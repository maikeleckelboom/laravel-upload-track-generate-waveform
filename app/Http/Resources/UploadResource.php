<?php

namespace App\Http\Resources;

use App\Enum\UploadStatus;
use Carbon\Carbon;
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
 * @property float $upload_speed
 * @property int $eta
 * @property string $updated_at
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
                'speed' => $this->upload_speed,
                'remaining' => $this->getRemainingTime(),
                'eta' => $this->getEta(),
            ],
        ];
    }

    private function getRemainingTime(): int
    {
        if (intval($this->upload_speed) === 0) return 0;
        return ($this->size - $this->received_bytes) / $this->upload_speed * 1000;
    }

    private function getEta(): int
    {
        return Carbon::parse($this->updated_at)->timestamp + $this->getRemainingTime();
    }
}
