<?php

namespace App\Models;

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
        'elapsed_milliseconds' => 'int',
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
        return $this->received_chunks / $this->total_chunks * 100;
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function setElapsedMilliseconds(int $milliseconds): void
    {
        $this->update([
            'elapsed_milliseconds' => $milliseconds,
        ]);
    }

    //    public function isPending(): bool
    //    {
    //        return $this->status === 'pending';
    //    }
    //
    //    public function isProcessing(): bool
    //    {
    //        return $this->status === 'processing';
    //    }
}
