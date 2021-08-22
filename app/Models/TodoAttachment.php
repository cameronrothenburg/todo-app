<?php

namespace App\Models;

use App\Exceptions\InvalidMIMETypeException;
use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\File\File;

/**
 *  @OA\Schema(
 *      @OA\Xml(name="TodoAttachment"),
 *          @OA\Property(property="todo_item_id", type="string",readOnly=true,example="7fed716f-4653-4e11-873d-f341aa8d911d"),
 *          @OA\Property(property="url", type="string|null",readOnly=true,example="/storage/cd63320c-918e-4eee-acbb-de530ccc691c/ab15b527-9161-4ba5-b3e7-752c78c7a532.png"),
 * )
 */
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

    /**
     * @var string[] The accepted attachment MimeTypes
     */
    private $acceptedMimeTypes = [
        'png',
        'jpeg',
        'jpg',
    ];

    /**
     * Gets the todoItem the todoAttachment belongs too
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function todoItem(): \Illuminate\Database\Eloquent\Relations\BelongsTo {
        return $this->belongsTo(TodoItem::class);
    }

    /** Helper function to create filename
     * @return string filename The filename of the attachment
     */
    public function fileName(): string {
        return "{$this->id}.{$this->file_type}";
    }

    /**
     * Helper function to return the URI of an attachment
     * @return string uri The URI of the attachment
     */
    public function uri(): string {
        return "{$this->todo_item_id}/{$this->fileName()}";
    }

    /**
     * Function to return the URL of the attachment if it exists
     * @return string|null URL string or null
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function getUrl(): ?string {

        if (Storage::disk($this->storage_type)->exists($this->uri())) {
            return Storage::disk($this->storage_type)->url($this->uri());
        }

        return null;
    }

    /**
     * Function to store the attachment
     * @param String $base64File Base64 encoded file
     * @return Bool If operation was successful
     * @throws InvalidMIMETypeException MIMEType of the file is not in accepted list
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
            throw new InvalidMimeTypeException();
        }
        Storage::disk($this->storage_type)->putFileAs($this->todo_item_id, $file, $this->fileName());
        return true;
    }

    /**
     * Function to remove model from database and file from storage
     * @return void
     */
    public function remove(): void {

        Storage::delete($this->uri());
        $this->delete();
    }

    /**
     * Return a formatted response for api
     * @return string[] Return the ID and URl TodoAttachments file
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function formattedResponse(): array {
        return [
            'id' => $this->id,
            'url' => $this->getUrl(),
        ];
    }

    /**
     * Helper function to return the accepted MIME Types
     * @return string[] Array of MIME types accepted
     */
    public function getAcceptedMimeTypes(): array {
        return $this->acceptedMimeTypes;
    }
}

