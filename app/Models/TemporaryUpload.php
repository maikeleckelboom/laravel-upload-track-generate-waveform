<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class TemporaryUpload extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'user_id',
        'genre_id',
        'identifier',
        'chunk_size',
        'size',
        'name',
        'type',
        'received_chunks',
        'status',
        'meta'
    ];

    protected $casts = [
        'chunk_size' => 'int',
        'meta' => 'array',
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
        return $this->received_chunks === $this->total_chunks || $this->status === 'completed';
    }

}
