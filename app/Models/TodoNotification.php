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

    public function todoItem() {
        return $this->belongsTo(TodoItem::class);
    }
}
