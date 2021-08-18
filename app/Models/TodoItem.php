<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 *  @OA\Schema(
 *      required={"title","body"},
 *      @OA\Xml(name="TodoItem"),
 *      @OA\Property(property="id", type="string",readOnly=true, example="7fed716f-4653-4e11-873d-f341aa8d911d"),
 *      @OA\Property(property="title", type="string",readOnly=false, example="Assumenda fuga recusandae voluptatumimpedit."),
 *      @OA\Property(property="body", type="string",readOnly=false, example="Assumenda fuga recusandae"),
 *      @OA\Property(property="completed", type="boolean",readOnly=false, example=0),
 *      @OA\Property(property="due_datetime", type="string",readOnly=false, example="2021-11-26 09:47:59"),
 * )
 */
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

    protected $hidden = [
        'updated_at',
        'created_at',
        'user_id'
    ];

    protected $attributes = [
      'completed' => false
    ];

    /**
     * Gets the todoAttachments for the todoItem
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function todoAttachments(): \Illuminate\Database\Eloquent\Relations\HasMany {
        return $this->hasMany(TodoAttachment::class);
    }

    /**
     * Gets the todoNotifications for the todoItem
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function todoNotifications(): \Illuminate\Database\Eloquent\Relations\HasMany {
        return $this->hasMany(TodoNotification::class);
    }

    /**
     * Function to get the user of the todoitem
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo {
        return $this->belongsTo(User::class);
    }
}
