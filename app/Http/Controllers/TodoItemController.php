<?php

namespace App\Http\Controllers;

use App\Models\TodoAttachment;
use App\Models\TodoItem;
use App\Models\TodoNotification;
use Illuminate\Http\Request;

class TodoItemController extends Controller {

    /**
     * @var string[] Validation rules for model attributes.
     */
    private $validation = [
        'title' => 'string|nullable',
        'body' => 'string|nullable',
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
     *     @OA\Response(
     *       response=200,
     *       description="Success",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean",readOnly=true,example=true),
     *              @OA\Property( property="data", type="object",
     *                  @OA\Property(property="content", type="array",
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
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): \Illuminate\Http\JsonResponse {
        $todoItems = auth()->user()->todoItems()->orderByDesc('due_datetime')->get();

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
     *          required=false,
     *          @OA\Schema(type="string")
     *      ),
     *     @OA\Parameter(
     *          description="Body",
     *          in="query",
     *          name="body",
     *          required=false,
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
     *                  @OA\Property(property="notifications", type="object",
     *                      @OA\Property(type="object", ref="#/components/schemas/TodoNotification")
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
            $this->createAttachments($request->attachments, $todoItem->id);
        }

        if ($request->has('notifications')) {
            $this->createNotifications($request->notifications, $todoItem->id);
        }
        if ($saved) {
            $response = response()->json([
                'success' => true,
                'data' => [
                    "content" => $todoItem,
                    "attachments" => $this->getAttachments($todoItem->id),
                    "notifications" => $this->getNotifications($todoItem->id),

                ]
            ]);
        } else {
            $response = response()->json([
                'success' => false,
                'message' => 'Item could not be saved'
            ], 500);
        }
        return $response;

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
     *                  @OA\Property(property="notifications", type="object",
     *                      @OA\Property(type="object", ref="#/components/schemas/TodoNotification")
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
     *          required=true,
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
     *          required=false,
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
     *                  @OA\Property(property="notifications", type="object",
     *                      @OA\Property(type="object", ref="#/components/schemas/TodoNotification")
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

        if (!$updatedItem) {
            return response()->json([
                'success' => false,
                'message' => 'Item could not be updated!'
            ], 500);
        }

        if ($request->has('attachments')) {
            $this->createAttachments($request->attachments, $todoItem->id);
        }

        if ($request->has('notifications')) {
            $this->createNotifications($request->notifications, $todoItem->id);
        }

        if ($request->deleteAttachments) {
            foreach ($request->deleteAttachments as $deleteId) {
                $todoAttachment = TodoAttachment::all()->where('id', $deleteId)->first();
                if (!is_null($todoAttachment)) {
                    $todoAttachment->remove();
                }
            }
        }

        if ($request->deleteNotifications) {
            foreach ($request->deleteNotifications as $deleteId) {
                $todoNotification = TodoNotification::all()->where('id', $deleteId)->first();
                if (!is_null($todoNotification)) {
                    $todoNotification->delete();
                }
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                "content" => $todoItem,
                "attachments" => $this->getAttachments($todoItem->id),
                "notifications" => $this->getNotifications($todoItem->id),
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
     * @return TodoAttachment[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection
     */
    private function getAttachments($id) {
        $query = TodoAttachment::all()->where('todo_item_id', $id);
        return $query->map(function (TodoAttachment $attachment) {
            return [
                'id' => $attachment->id,
                'url' => $attachment->getUrl()

            ];
        });
    }

    /**
     * Helper function to create TodoAttachments
     * @param $todoAttachments
     * @param $todo_item_id
     * @return void
     */
    private function createAttachments($todoAttachments, $todo_item_id): void {
        foreach ($todoAttachments as $attachment) {

            $todoAttachment = TodoAttachment::create([
                'todo_item_id' => $todo_item_id,
            ]);
            $todoAttachment->store($attachment);
        }
    }

    /**
     * Helper funtion to get TodoNotifications
     * @param $todo_item_id
     * @return TodoNotification[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection
     */
    private function getNotifications($todo_item_id) {
        $query = TodoNotification::all()->where('todo_item_id', $todo_item_id)->where('sent', false);
        return $query->map(function (TodoNotification $notification) {
            return [
                'id' => $notification->id,
                'datetime' => $notification->reminder_datetime

            ];
        });
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
}
