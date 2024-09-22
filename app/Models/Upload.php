<?php

namespace App\Models;

use App\Data\UploadData;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Upload extends Model
{
    protected $guarded = [];

    protected $casts = [
        'size' => 'int',
        'chunk_size' => 'int',
        'received_chunks' => 'int',
        'elapsed_time' => 'int',
        'transfer_speed' => 'float'
    ];

    protected $appends = [
        'total_chunks',
        'received_bytes',
        'progress',
        'extension',
        'remaining_time',
        'eta',
    ];

    public function getExtensionAttribute(): string
    {
        return pathinfo($this->name, PATHINFO_EXTENSION);
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

    public function getRemainingTimeAttribute(): int
    {
        if (intval($this->transfer_speed) === 0) return 0;
        return ($this->size - $this->received_bytes) / $this->transfer_speed * 1000;
    }

    public function getEtaAttribute(): int
    {
        return $this->isCompleted()
            ? Carbon::parse($this->updated_at)->timestamp
            : Carbon::now()->timestamp + $this->remaining_time;
    }

    public function isCompleted(): bool
    {
        return $this->size === $this->received_bytes
            || $this->received_chunks === $this->total_chunks
            || $this->progress === 100
            || $this->status === 'completed';
    }

    public function updateMetrics(UploadData $data): void
    {
        $this->update([
            'elapsed_time' => $data->elapsedTime,
            'transfer_speed' => $data->transferSpeed
        ]);
    }

    public function uploadable(): MorphTo
    {
        return $this->morphTo();
    }
}
