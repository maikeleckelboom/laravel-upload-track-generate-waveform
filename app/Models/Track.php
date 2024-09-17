<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Track extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    protected $guarded = [];

    protected $appends = ['waveform_image_url', 'waveform_data_url', 'stream_url'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cue(): HasMany
    {
        return $this->hasMany(Cue::class);
    }

    public function genre(): BelongsTo
    {
        return $this->belongsTo(Genre::class);
    }

    public function playlists(): BelongsToMany
    {
        return $this->belongsToMany(Playlist::class);
    }

    public function getStreamPathAttribute(): string
    {
        return "track/{$this->id}/stream";
    }

    public function getStreamUrlAttribute(): string
    {
        return $this->getMedia('audio', fn($file) => $file->getCustomProperty('format') === 'flac')
            ->first()
            ?->getUrl();
    }

    public function getWaveformDataUrlAttribute(): string|null
    {
        return $this->getFirstMedia('waveform', fn($file) => $file->getCustomProperty('type') === 'binary')?->getUrl();
    }

    public function getWaveformImageUrlAttribute(): string|null
    {
        return $this->getFirstMedia('waveform', fn($file) => $file->getCustomProperty('type') === 'image')?->getUrl();
    }
}
