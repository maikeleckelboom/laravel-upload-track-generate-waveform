<?php

namespace App\Models;

use App\Data\UploadData;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Upload extends Model
{
    protected $guarded = [];

    protected $hidden = [
        'path',
    ];
    protected $casts = [
        'size' => 'int',
        'chunk_size' => 'int',
        'received_chunks' => 'int',
        'elapsed_active_time' => 'int',
    ];

    protected $appends = [
        'total_chunks',
        'received_bytes',
        'progress',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getExtensionAttribute(): string
    {
        return pathinfo($this->file_name, PATHINFO_EXTENSION);
    }

    public function getReceivedBytesAttribute(): int
    {
        return min($this->size, $this->received_chunks * $this->chunk_size);
    }

    public function getTotalChunksAttribute(): int
    {
        return ceil($this->size / $this->chunk_size);
    }

    public function getProgressAttribute(): float
    {
        return min(100, max(0, $this->received_chunks / $this->total_chunks * 100));
    }

    public function isCompleted(): bool
    {

        return $this->size === $this->received_bytes
            || $this->received_chunks === $this->total_chunks
            || $this->progress === 100
            || $this->status === 'completed';
    }

    public function updateMetrics(Upload $upload, UploadData $data): void
    {
        $upload->update([
            'elapsed_time' => $data->elapsedTime,
            'remaining_time' => $data->remainingTime,
            'upload_speed' => $data->uploadSpeed,
            'eta' => $data->eta,
        ]);
    }
}
