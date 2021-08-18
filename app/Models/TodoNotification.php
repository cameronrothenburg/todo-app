<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 *  @OA\Schema(
 *      @OA\Xml(name="TodoNotification"),
 *          @OA\Property(property="id", type="string",readOnly=true,example="42189b19-5155-48a8-a81a-9433c9412b5d"),
 *          @OA\Property(property="datetime", type="string",readOnly=true, example="2021-09-15 09:47:59"),
 * )
 */
class TodoNotification extends Model
{
    use HasFactory, Uuid;

    public $incrementing = false;
    protected $keyType = 'uuid';

    protected $fillable = [
        'todo_item_id',
        'reminder_datetime'
    ];

    protected $attributes = [
        'sent' => false
    ];

    /**
     * Function to return the related todoitem
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function todoItem(): \Illuminate\Database\Eloquent\Relations\BelongsTo {
        return $this->belongsTo(TodoItem::class);
    }
}
