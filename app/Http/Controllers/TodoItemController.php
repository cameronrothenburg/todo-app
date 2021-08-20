<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidMIMETypeException;
use App\Exceptions\ModelNotFoundException;
use App\Models\TodoAttachment;
use App\Models\TodoItem;
use App\Models\TodoNotification;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Cache;

class TodoItemController extends Controller {

    /**
     * @var int Seconds that cache will expire after
     */
    private $ttl = 200;

    /**
     * @var string[] Validation rules for model attributes.
     */
    private $validationRules = [
        'title' => 'string|required',
        'body' => 'string|required',
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
     *          @OA\Schema(type="int")
     *      ),
     *     @OA\Parameter(
     *          description="Page",
     *          in="query",
     *          name="page",
     *          required=false,
     *          @OA\Schema(type="int")
     *      ),
     *     @OA\Response(
     *       response=200,
     *       description="Success",
     *          @OA\JsonContent(
     *                  @OA\Property(property="current_page", type="int", example=1),
     *                  @OA\Property(property="data", type="array",
     *                      @OA\Items(type="object", ref="#/components/schemas/TodoItem"),
     *                      @OA\Items(type="object", ref="#/components/schemas/TodoItem"),
     *                      @OA\Items(type="object", ref="#/components/schemas/TodoItem"),
     *                  ),
     *                   @OA\Property(property="first_page_url", type="string", example="http://localhost/api/v1/todoitems?page=1"),
     *                   @OA\Property(property="from", type="int", example=1),
     *                  @OA\Property(property="next_page_url", type="string",example="http://localhost/api/v1/todoitems?page=2"),
     *                   @OA\Property(property="per_paage", type="int", example=100),
     *                  @OA\Property(property="prev_page", type="string", example=null),
     *                   @OA\Property(property="to", type="integer", example=100),
     *              ),
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
        $query = [];

        if ($request->has('completed')) {
            $query[] = ["completed", $request->completed];
        }
        $todoItems = $this->itemCache($request->page ?? 1, $query);

        return response()->json($todoItems);
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
     *      @OA\Parameter(
     *          description="Completed",
     *          in="query",
     *          name="completed",
     *          required=false,
     *          example=0,
     *          @OA\Schema(type="int")
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
     *          description="Not Found"
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *       @OA\Response(
     *       response=500,
     *       description="Server Error",
     *          @OA\JsonContent(
     *              @OA\Property( property="errors", type="string", example="TodoItem could not be saved"),
     *          ),
     *      ),
     * )
     */
    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function store(Request $request): \Illuminate\Http\JsonResponse {

        $request->validate($this->validationRules);

        $todoItem = new TodoItem([
            "title" => strip_tags($request->title),
            "body" => strip_tags($request->body),
            "due_datetime" => $request->due_datetime,
            "completed" => $request->completed ?? 0,
        ]);

        $saved = auth()->user()->todoItems()->save($todoItem);

        if ($request->has('attachments')) {
            try {
                $todoItem->createAttachments($request->attachments);
            } catch (InvalidMIMETypeException $e) {
                return $e->render($request);
            }
        }

        if ($request->has('notifications')) {
            $todoItem->createNotifications($request->notifications);
        }

        if ($saved) {
            return response()->json([
                'data' => [
                    "content" => [$todoItem],
                    "attachments" => $todoItem->getFormattedAttachments(),
                    "notifications" => $todoItem->getFormattedNotifications()

                ]
            ]);
        }

        return response()->json([
            'error' => 'TodoItem could not be saved'
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
    public function show(Request $request, string $id): \Illuminate\Http\JsonResponse {
        $todoItem = TodoItem::where('id', $id)->first();

        if (!$todoItem) {
            $exception = new ModelNotFoundException();
            return $exception->render($request);
        }

        return response()->json([
            'data' => [
                "content" => [$todoItem],
                "attachments" => $todoItem->getFormattedAttachments(),
                "notifications" => $todoItem->getFormattedNotifications(),
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
     *      @OA\Parameter(
     *          description="Completed",
     *          in="query",
     *          name="completed",
     *          required=false,
     *          example=0,
     *          @OA\Schema(type="int")
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
        $validation = [
            "title" => 'string|nullable',
            "body" => 'string|nullable'
        ];

        $validation[] = array_splice($this->validationRules, 2);
        $request->validate($validation);

        $todoItem = TodoItem::where('id', $id)->first();

        if (!$todoItem) {
            $error = new ModelNotFoundException();
            $error->render($request);
        }

        if ($request->has('attachments')) {
            try {
                $todoItem->createAttachments($request->attachments);
            } catch (InvalidMIMETypeException $e) {
                return $e->render($request);
            }
        }

        $updatedItem = $todoItem->fill([
            "title" => strip_tags($request->title) ?? $todoItem->title,
            "body" => strip_tags($request->body) ?? $todoItem->body,
            "due_datetime" => $request->due_datetime ?? $todoItem->due_datetime,
            "completed" => $request->completed ?? $todoItem->completed,
        ])->save();

        if ($request->has('notifications')) {
            $todoItem->createNotifications($request->notifications);
        }

        if (!$updatedItem) {
            return response()->json([
                'errors' => 'Item could not be updated!'
            ], 500);
        }

        if ($request->deleteAttachments) {
            $this->deleteAttachments($request->deleteAttachments);
        }

        if ($request->deleteNotifications) {
            $this->deleteNotifications($request->deleteNotifications);
        }

        $notifications = $todoItem->getFormattedNotifications();

        if ($notifications) {
            $todoItem->removeInvalidNotifications();
        }

        return response()->json([
            'data' => [
                "content" => [$todoItem],
                "attachments" => $todoItem->getFormattedAttachments(),
                "notifications" => $todoItem->getFormattedNotifications()
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
     * @param Request $request
     * @param String $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, string $id): \Illuminate\Http\JsonResponse {
        $todoItem = TodoItem::where('id', $id)->first();

        if (!$todoItem) {
            $error = new ModelNotFoundException();
            $error->render($request);
        }

        if ($todoItem->delete()) {
            $response = response()->json([
                'message' => 'Item deleted',
            ]);
        } else {
            $response = response()->json([
                'errors' => 'Item could not be deleted!'
            ], 500);
        }
        return $response;
    }

    /**
     * Helper function to call TodoNotifications::delete on multiple TodoNotifications
     * @param string[] $todoNotificationIds
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
     * Helper function to call TodoAttachments::remove on multiple TodoAttachments
     * @param string[] $todoAttachmentIds
     * @return void
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
     * Helper function to interact with TodoItem cache
     * @param int $page
     * @param array[] $query
     * @return Paginator
     */
    private function itemCache(int $page, $query): Paginator {
        $userId = \Auth::user()->id;
        $storeName = "todoItems-{$userId}-{$page}}";
        if (!empty($query)) {
            foreach ($query as $item) {
                $storeName .= "?{$item[0]}={$item[1]}";
            }
        }

        return Cache::tags("todoItems-{$userId}")->remember($storeName, $this->ttl, function () use ($query) {
            return \Auth::user()
                ->todoItems()
                ->where($query)
                ->orderByDesc('due_datetime')
                ->simplePaginate(100);
        });
    }
}
