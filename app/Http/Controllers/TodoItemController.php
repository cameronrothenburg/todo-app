<?php

namespace App\Http\Controllers;

use App\Models\TodoAttachment;
use App\Models\TodoItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TodoItemController extends Controller {
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index() {
        $todoItems = auth()->user()->todoItems()->select([
            'id', 'title', 'completed'
        ])->orderByDesc('due_datetime')->get();

        return response()->json([
            'success' => true,
            'data' => [
                'content' => $todoItems
            ]
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request) {

        $request->validate([
            'title' => 'required|string',
            'body' => 'required|string',
            'completed' => 'boolean|nullable',
            'due_datetime' => 'date_equals:date|nullable',
            'attachments' => 'array|nullable',
            'attachments.*' => 'string'
        ]);

        $todoItem = new TodoItem($request->all());
        $saved = auth()->user()->todoItems()->save($todoItem);

        if ($request->has('attachments')) {
            $this->createAttachments($request->attachments, $todoItem->id);
        }

        if ($saved) {
            $response = response()->json([
                'success' => true,
                'data' => [
                    "content" => $todoItem,
                    "attachments" => $this->getAttachments($todoItem->id)

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
     * Display the specified resource.
     *
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(string $id) {
        $todoItem = auth()->user()->todoItems()->select([
            'id', 'title', 'body', 'completed', 'due_datetime'
        ])->find($id);

        if (!$todoItem) {
            return response()->json([
                'success' => false,
                'message' => 'Item is not available!'
            ], 404);
        }

        $todoAttachments = $this->getAttachments($id);

        return response()->json([
            'success' => true,
            'data' => [
                "content" => $todoItem,
                "attachments" => $todoAttachments
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param String $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, string $id) {

        $request->validate([
            'title' => 'string|nullable',
            'body' => 'string|nullable',
            'completed' => 'boolean|nullable',
            'due_datetime' => 'date_equals:date|nullable',
            'attachments' => 'array|nullable',
        ]);

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

        if ($request->deleteAttachment) {
            foreach ($request->deleteAttachment as $deleteId) {
                $todoAttachment = TodoAttachment::all()->where('id', $deleteId)->first();
                if (!is_null($todoAttachment)) {
                    $todoAttachment->remove();
                }
            }
        }

        if ($request->has('attachments')) {
            $this->createAttachments($request->attachments, $id);

        }

        $todoAttachments = $this->getAttachments($id);

        return response()->json([
            'success' => true,
            'data' => [
                "content" => $todoItem,
                "attachments" => $todoAttachments
            ],
        ]);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param String $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(string $id) {
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
                'file' => $attachment->getUrl()

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
}
