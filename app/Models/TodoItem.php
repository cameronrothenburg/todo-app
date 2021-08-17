<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TodoItem extends Model
{
    use HasFactory, Uuid;

    public $incrementing = false;
    protected $keyType = 'uuid';

    protected $fillable = [
        'title',
        'body',
        'due_datetime',
        'completed',
    ];

    protected $attributes = [
      'completed' => false
    ];

    /**
     * Gets the todoAttachments for the todoItem
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function todoAttachments() {
        return $this->hasMany(TodoAttachment::class);
    }

    /**
     * Gets the todoNotifications for the todoItem
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function todoNotifications() {
        return $this->hasMany(TodoNotification::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }
}
