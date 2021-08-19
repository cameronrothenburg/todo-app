<?php

namespace App\Http\Controllers;

use App\Models\TodoAttachment;
use App\Models\TodoItem;
use App\Models\TodoNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class TodoItemController extends Controller {

    /**
     * @var string[] Validation rules for model attributes.
     */
    private $validation = [
        'title' => 'string',
        'body' => 'string',
        'completed' => 'boolean|nullable',
        'due_datetime' => 'date|nullable',
        'attachments' => 'array|nullable',
        'notifications' => 'array|nullable',
        'notifications.*' => 'date|before:due_datetime|nullable',
    ];

    /**
     * @OA\Get(
     *     path="/api/v1/todoitems",
     *     operationId="getTodoItemsList",
     *     tags={"TodoItems"},
     *     summary="Get list of TodoItems",
     *     description="Returns list of TodoItems",
     *        security={
     *              {"passport": {}},
     *   },
     *     @OA\Parameter(
     *          description="Completed",
     *          in="query",
     *          name="completed",
     *          required=false,
     *          @OA\Schema(type="boolean")
     *      ),
     *     @OA\Response(
     *       response=200,
     *       description="Success",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean",readOnly=true,example=true),
     *              @OA\Property( property="data", type="object",
     *                  @OA\Property(property="content", type="array",
     *                      @OA\Items(type="object", ref="#/components/schemas/TodoItem"),
     *                      @OA\Items(type="object", ref="#/components/schemas/TodoItem"),
     *                      @OA\Items(type="object", ref="#/components/schemas/TodoItem"),
     *                  ),
     *              ),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated"
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="not found"
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     * )
     */
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request): \Illuminate\Http\JsonResponse {
        $todoItems = auth()->user()->todoItems()->orderByDesc('due_datetime')->get();

        if ($request->has('completed')) {

            $todoItems = $todoItems->where('completed', $request->boolean('completed'));
        }

        return response()->json([
            'success' => true,
            'data' => [
                'content' => $todoItems
            ]
        ]);
    }
    /**
     * @OA\Post(
     *     path="/api/v1/todoitems",
     *     operationId="createTodoItem",
     *     tags={"TodoItems"},
     *     summary="Create TodoItem",
     *     description="Create TodoItem",
     *        security={
     *              {"passport": {}},
     *          },
     *     @OA\Parameter(
     *          description="Title",
     *          in="query",
     *          name="title",
     *          required=true,
     *          @OA\Schema(type="string")
     *      ),
     *     @OA\Parameter(
     *          description="Body",
     *          in="query",
     *          name="body",
     *          required=true,
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\Parameter(
     *          description="Time and Date due",
     *          in="query",
     *          name="due_datetime",
     *          required=false,
     *          example="2021-11-26 09:47:59",
     *          @OA\Schema(type="string")
     *      ),
     *     @OA\Parameter(
     *          description="Base64 encoded attachments",
     *          in="query",
     *          name="attachments[]",
     *          required=false,
     *          @OA\Schema(type="array", @OA\Items(type="string"))
     *      ),
     *      @OA\Parameter(
     *          description="Notification date and time",
     *          in="query",
     *          name="notifications[]",
     *          required=false,
     *          @OA\Schema(type="array", @OA\Items(type="string", example="2021-11-26 09:47:59"))
     *      ),
     *
     *
     *
     *     @OA\Response(
     *       response=200,
     *       description="Success",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean",readOnly=true,example=true),
     *              @OA\Property( property="data", type="object",
     *                  @OA\Property(property="content", type="array",
     *                      @OA\Items(type="object", ref="#/components/schemas/TodoItem"),
     *                  ),
     *                  @OA\Property(property="attachments", type="array",
     *                      @OA\Items(type="object", ref="#/components/schemas/TodoAttachment")
     *                  ),
     *                  @OA\Property(property="notifications", type="array",
     *                      @OA\Items(type="object", ref="#/components/schemas/TodoNotification")
     *                  ),
     *              ),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated"
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="not found"
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     * )
     */
    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request): \Illuminate\Http\JsonResponse {

        $request->validate($this->validation);

        $todoItem = new TodoItem($request->all());
        $saved = auth()->user()->todoItems()->save($todoItem);

        if ($request->has('attachments')) {
            $savedAttachments = $this->createAttachments($request->attachments, $todoItem->id);
            $saved = $saved && $savedAttachments;
        }

        if ($request->has('notifications')) {
            $this->createNotifications($request->notifications, $todoItem->id);
        }
        if ($saved) {
            return response()->json([
                'success' => true,
                'data' => [
                    "content" => [$todoItem],
                    "attachments" => $this->getAttachments($todoItem->id),
                    "notifications" => $this->getNotifications($todoItem->id)

                ]
            ]);
        }
        return response()->json([
            'success' => false,
            'message' => 'Item could not be saved'
        ], 500);

    }

    /**
     * @OA\Get(
     *     path="/api/v1/todoitems/{id}",
     *     operationId="getTodoItemDetail",
     *     tags={"TodoItems"},
     *     summary="Get detail of TodoItem",
     *     description="Returns detail of TodoItem",
     *        security={
     *              {"passport": {}},
     *          },
     *     @OA\Parameter(
     *          description="ID of TodoItem",
     *          in="path",
     *          name="id",
     *          required=true,
     *          example="7fed716f-4653-4e11-873d-f341aa8d911d",
     *          @OA\Schema(type="string")
     *      ),
     *     @OA\Response(
     *       response=200,
     *       description="Success",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean",readOnly=true,example=true),
     *              @OA\Property( property="data", type="object",
     *                  @OA\Property(property="content", type="array",
     *                      @OA\Items(type="object", ref="#/components/schemas/TodoItem"),
     *                  ),
     *                  @OA\Property(property="attachments", type="array",
     *                      @OA\Items(type="object", ref="#/components/schemas/TodoAttachment")
     *                  ),
     *                  @OA\Property(property="notifications", type="array",
     *                      @OA\Items(type="object", ref="#/components/schemas/TodoNotification")
     *                  ),
     *              ),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated"
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *     @OA\Response(
     *          response=404,
     *          description="not found",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean",readOnly=true,example=false),
     *              @OA\Property(property="message", type="string",readOnly=true,example="Item could not be found!"),
     *              ),
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     * )
     */
    /**
     * Display the specified resource.
     *
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(string $id): \Illuminate\Http\JsonResponse {
        $todoItem = auth()->user()->todoItems()->find($id);

        if (!$todoItem) {
            return response()->json([
                'success' => false,
                'message' => 'Item is not available!'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                "content" => $todoItem,
                "attachments" => $this->getAttachments($id),
                "notifications" => $this->getNotifications($id),
            ],
        ]);
    }

    /**
     * @OA\Patch(
     *     path="/api/v1/todoitems/{id}",
     *     operationId="updateTodoItem",
     *     tags={"TodoItems"},
     *     summary="Update TodoItem",
     *     description="Update TodoItem",
     *        security={
     *              {"passport": {}},
     *          },
     *      @OA\Parameter(
     *          description="ID of TodoItem",
     *          in="path",
     *          name="id",
     *          required=false,
     *          example="7fed716f-4653-4e11-873d-f341aa8d911d",
     *          @OA\Schema(type="string")
     *      ),
     *     @OA\Parameter(
     *          description="Title",
     *          in="query",
     *          name="title",
     *          required=false,
     *          @OA\Schema(type="string")
     *      ),
     *     @OA\Parameter(
     *          description="Body",
     *          in="query",
     *          name="body",
     *          required=true,
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\Parameter(
     *          description="Time and Date due",
     *          in="query",
     *          name="due_datetime",
     *          required=false,
     *          example="2021-11-26 09:47:59",
     *          @OA\Schema(type="string")
     *      ),
     *     @OA\Parameter(
     *          description="Base64 encoded attachments",
     *          in="query",
     *          name="attachments[]",
     *          required=false,
     *          @OA\Schema(type="array", @OA\Items(type="string"))
     *      ),
     *      @OA\Parameter(
     *          description="Notification date and time",
     *          in="query",
     *          name="notifications[]",
     *          required=false,
     *          @OA\Schema(type="array", @OA\Items(type="string", example="2021-11-26 09:47:59"))
     *      ),
     *     @OA\Parameter(
     *          description="ID of Notifications to delete",
     *          in="query",
     *          name="deleteNotifications[]",
     *          required=false,
     *          @OA\Schema(type="array", @OA\Items(type="string", example="7fed716f-4653-4e11-873d-f341aa8d911d"))
     *      ),
     *      @OA\Parameter(
     *          description="ID of attachments to delete",
     *          in="query",
     *          name="deleteAttachments[]",
     *          required=false,
     *          @OA\Schema(type="array", @OA\Items(type="string", example="7fed716f-4653-4e11-873d-f341aa8d911d"))
     *      ),
     *
     *
     *
     *     @OA\Response(
     *       response=200,
     *       description="Success",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean",readOnly=true,example=true),
     *              @OA\Property( property="data", type="object",
     *                  @OA\Property(property="content", type="array",
     *                      @OA\Items(type="object", ref="#/components/schemas/TodoItem"),
     *                  ),
     *                  @OA\Property(property="attachments", type="array",
     *                      @OA\Items(type="object", ref="#/components/schemas/TodoAttachment")
     *                  ),
     *                  @OA\Property(property="notifications", type="array",
     *                      @OA\Items(type="object", ref="#/components/schemas/TodoNotification")
     *                  ),
     *              ),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated"
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="not found",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean",readOnly=true,example=false),
     *              @OA\Property(property="message", type="string",readOnly=true,example="Item is not available!"),
     *              ),
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Server Error",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean",readOnly=true,example=false),
     *              @OA\Property(property="message", type="string",readOnly=true,example="Item could not be updated!"),
     *              ),
     *      ),
     * )
     */
    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param String $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, string $id): \Illuminate\Http\JsonResponse {

        $request->validate($this->validation);

        $todoItem = auth()->user()->todoItems()->find($id);

        if (!$todoItem) {
            return response()->json([
                'success' => false,
                'message' => 'Item could not be found!'
            ], 404);
        }

        $updatedItem = $todoItem->fill([
            "title" => $request->title ?? $todoItem->title,
            "body" => $request->body ?? $todoItem->body,
            "due_datetime" => $request->due_datetime ?? $todoItem->due_datetime,
            "completed" => $request->completed ?? $todoItem->completed,
        ])->save();

        if ($request->has('attachments')) {
            $updatedAttachments = $this->createAttachments($request->attachments, $todoItem->id);
            $updatedItem = $updatedItem && $updatedAttachments;
        }

        if (!$updatedItem) {
            return response()->json([
                'success' => false,
                'message' => 'Item could not be updated!'
            ], 500);
        }

        if ($request->has('notifications')) {
            $this->createNotifications($request->notifications, $todoItem->id);
        }

        if ($request->deleteAttachments) {
            $this->deleteAttachments($request->deleteAttachments);
        }

        if ($request->deleteNotifications) {
            $this->deleteNotifications($request->deleteNotifications);
        }

        $notifications = $this->getNotifications($todoItem->id);

        if ($notifications) {
            $this->validateNotifications($todoItem);
        }

        return response()->json([
            'success' => true,
            'data' => [
                "content" => [$todoItem],
                "attachments" => $this->getAttachments($todoItem->id),
                "notifications" => $this->getNotifications($todoItem->id)
            ],
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/todoitems/{id}",
     *     operationId="removeTodoItem",
     *     tags={"TodoItems"},
     *     summary="Delete TodoItem",
     *     description="Delete TodoItem",
     *        security={
     *              {"passport": {}},
     *          },
     *     @OA\Parameter(
     *          description="ID of TodoItem",
     *          in="path",
     *          name="id",
     *          required=true,
     *          example="7fed716f-4653-4e11-873d-f341aa8d911d",
     *          @OA\Schema(type="string")
     *      ),
     *     @OA\Response(
     *       response=200,
     *       description="Success",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean",readOnly=true,example=true),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated"
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="not found",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean",readOnly=true,example=false),
     *              @OA\Property(property="message", type="string",readOnly=true,example="Item could not be found!"),
     *              ),
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Server Error",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean",readOnly=true,example=false),
     *              @OA\Property(property="message", type="string",readOnly=true,example="Item could not be deleted!"),
     *              ),
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     * )
     */
    /**
     * Remove the specified resource from storage.
     *
     * @param String $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(string $id): \Illuminate\Http\JsonResponse {
        $todoItem = auth()->user()->todoItems()->find($id);

        if (!$todoItem) {
            return response()->json([
                'success' => false,
                'message' => 'Item could not be found!'
            ], 404);
        }

        if ($todoItem->delete()) {
            $response = response()->json([
                'success' => true,
            ]);
        } else {
            $response = response()->json([
                'success' => false,
                'message' => 'Item could not be deleted!'
            ], 500);
        }
        return $response;
    }

    /**
     * Return all relevant fields from attachments related to a TodoItem
     * @param $id
     * @return TodoAttachment[]
     */
    private function getAttachments($id) {
        $query = TodoAttachment::all()->where('todo_item_id', $id);
        $result = [];
        foreach ($query as $attachment){
            $result[] = $attachment->formattedResponse();
        }
        return $result;
    }

    /**
     * Helper function to create TodoAttachments
     * @param $todoAttachments
     * @param $todo_item_id
     * @return bool
     */
    private function createAttachments($todoAttachments, $todo_item_id): bool {
        foreach ($todoAttachments as $attachment) {

            $todoAttachment = TodoAttachment::create([
                'todo_item_id' => $todo_item_id,
            ]);
            if (!$todoAttachment->store($attachment)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Helper function to get TodoNotifications
     * @param $todo_item_id
     * @return TodoNotification[]
     */
    private function getNotifications($todo_item_id) {
        $query = TodoNotification::all()->where('todo_item_id', $todo_item_id)->where('sent', false);
        $result = [];
        foreach ($query as $notification){
            $result[] = $notification->formattedResponse();
        }
        return $result;
    }

    /**
     * Helper function to create TodoNotifications
     * @param $todoNotifications
     * @param $todo_item_id
     * @return void
     */
    private function createNotifications($todoNotifications, $todo_item_id): void {
        foreach ($todoNotifications as $notification) {
            TodoNotification::create([
                'todo_item_id' => $todo_item_id,
                'reminder_datetime' => $notification
            ]);
        }
    }

    /**
     * Helper function to delete TodoNotifications
     * @param array $todoNotificationIds
     * @return void
     */
    private function deleteNotifications(array $todoNotificationIds): void {
        foreach ($todoNotificationIds as $deleteId) {
            $todoNotification = TodoNotification::all()->where('id', $deleteId)->first();
            if (!is_null($todoNotification)) {
                $todoNotification->delete();
            }
        }
    }

    /**
     * Helper function to remove TodoAttachments
     * @param array $todoAttachmentIds
     */
    private function deleteAttachments(array $todoAttachmentIds): void {
        foreach ($todoAttachmentIds as $deleteId) {
            $todoAttachment = TodoAttachment::all()->where('id', $deleteId)->first();
            if (!is_null($todoAttachment)) {
                $todoAttachment->remove();
            }
        }
    }

    /**
     * Helper function to validate notifications
     * @param TodoItem $todoItem
     */
    private function validateNotifications(TodoItem $todoItem) {
        $notifications = TodoNotification::all()->where('todo_item_id', $todoItem->id);
        $notifications->map(function ($notification) use ($todoItem) {
            $notification->validateSelf($todoItem);
        });
    }

}
