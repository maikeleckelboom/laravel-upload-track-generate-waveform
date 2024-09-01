<?php

namespace App\Http\Resources;

use App\Enum\UploadStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
    public function toArray(Request $request): array
    {
        return [
            'identifier' => $this->identifier,
            'name' => $this->name,
            'size' => $this->size,
            'type' => $this->mime_type,
            'extension' => $this->extension,
            'status' => $this->status,
            'metadata' => [
                'totalBytes' => $this->size,
                'receivedBytes' => $this->received_bytes,
                'totalChunks' => $this->total_chunks,
                'receivedChunks' => $this->received_chunks,
                'progress' => $this->progress,
            ],
        ];
    }
}
