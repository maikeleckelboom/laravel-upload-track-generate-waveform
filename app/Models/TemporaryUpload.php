<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class TemporaryUpload extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'identifier',
        'chunk_size',
        'received_chunks',
        'status',
        'meta',
        'disk',
    ];

    protected $casts = [
        'meta' => 'array',
        'chunk_size' => 'int',
    ];

    protected $appends = [
        'total_chunks',
        'received_bytes',
        'progress',
    ];

    public function getReceivedBytesAttribute(): int
    {
        return min($this->size, $this->received_chunks * $this->chunk_size);
    }

    public function getTotalChunksAttribute(): int
    {
        if (!$media = $this->getFirstMedia()) return 0;
        return ceil($media->size / $this->chunk_size);
    }

    public function getProgressAttribute(): float
    {
        return $this->received_chunks / $this->total_chunks * 100;
    }

}
