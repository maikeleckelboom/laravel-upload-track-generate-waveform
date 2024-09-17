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
 * @property int $elapsed_time
 * @property int $remaining_time
 * @property float $transfer_speed
 * @property int $eta
 * @property string $updated_at
 * @property string $created_at
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
            'progress' => [
                'percentage' => $this->progress,
                'receivedChunks' => $this->received_chunks,
                'receivedBytes' => $this->received_bytes,
                'totalChunks' => $this->total_chunks,
            ],
            'metrics' => [
                'elapsed' => $this->elapsed_time,
                'speed' => $this->transfer_speed,
                'remaining' => $this->remaining_time,
                'eta' => $this->eta,
            ],
        ];
    }
}
