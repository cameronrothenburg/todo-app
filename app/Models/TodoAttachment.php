<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\File\File;

class TodoAttachment extends Model {
    use HasFactory, Uuid;

    public $incrementing = false;
    protected $keyType = 'uuid';

    protected $fillable = [
        'todo_item_id'
    ];

    protected $hidden = [
        'storage_type',
        'uri'
    ];

    protected $attributes = [
        'storage_type' => 'local',
        'file_type' => 'attachment',
    ];

    private $acceptedMimeTypes = [
        'png',
        'jpeg',
        'jpg',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function todoItem() {
        return $this->belongsTo(TodoItem::class);
    }

    /** Returns the filename
     * @return string filename
     */
    public function fileName() {
        return "{$this->id}.{$this->file_type}";
    }

    public function uri(): string {
        return "{$this->todo_item_id}/{$this->fileName()}";
    }

    /**
     * @return string|null
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function getUrl() {

        if (Storage::disk($this->storage_type)->exists($this->uri())) {
            return Storage::disk($this->storage_type)->url($this->uri());
        }

        return null;
    }

    /**
     * Function to store the attachment
     * @param String $base64File
     * @return Bool If store was successful
     */
    public function store(string $base64File): bool {
        $fileData = base64_decode($base64File);
        $tmpFilePath = sys_get_temp_dir() . '/' . Str::uuid()->toString();
        file_put_contents($tmpFilePath, $fileData);

        $tmpFile = new File($tmpFilePath);
        $file = new UploadedFile(
            $tmpFile->getPathname(),
            $tmpFile->getFilename(),
            $tmpFile->getMimeType(),
            0,
            true
        );
        $this->file_type = $file->guessExtension();
        $this->save();

        if (!in_array($this->file_type, $this->acceptedMimeTypes, true)) {
            $this->delete();
            return false;
        }
        Storage::disk($this->storage_type)->putFileAs($this->todo_item_id, $file, $this->fileName());
        return true;
    }

    public function remove() {

        Storage::delete($this->uri());
        $this->delete();
    }
}

