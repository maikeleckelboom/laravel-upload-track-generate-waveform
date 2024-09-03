<?php

namespace App\Http\Resources;

use App\Enum\UploadStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * @property int $identifier
 * @property int $progress
 * @property int $received_chunks
 * @property int $total_chunks
 * @property int $received_bytes
 * @property int $size
 * @property UploadStatus $status
 * @property string $extension
 * @property string $mime_type
 * @property string $name
 */
class UploadResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request, Model|null $uploaded = null): array
    {
        return [
            'identifier' => $this->identifier,
            'name' => $this->name,
            'size' => $this->size,
            'type' => $this->mime_type,
            'extension' => $this->extension,
            'status' => $this->status,
            'progress' => [
                'value' => $this->progress,
                'receivedChunks' => $this->received_chunks,
                'receivedBytes' => $this->received_bytes,
                'totalChunks' => $this->total_chunks,
                'totalBytes' => $this->size,
            ],
            'meta' => [
                'elapsedMilliseconds' => $this->elapsed_milliseconds,
            ]
        ];
    }
}
