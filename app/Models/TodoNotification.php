<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TodoNotification extends Model
{
    use HasFactory;

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
