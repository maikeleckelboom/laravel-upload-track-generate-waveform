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

    protected $appends = [
        'playback_url',
        'waveform_image_url',
        'waveform_data_url',
        'is_waveform_ready'
    ];

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


    public function getPlaybackUrlAttribute(): ?string
    {
        $isTypePlayback = fn($file) => $file->getCustomProperty('type') === 'playback';
        return $this->getFirstMedia('audio', $isTypePlayback)?->getUrl();
    }

    public function getWaveformDataUrlAttribute(): ?string
    {
        $inBinaryFormat = fn($file) => $file->getCustomProperty('format') === 'dat';
        return $this->getFirstMedia('waveform', $inBinaryFormat)?->getUrl();
    }

    public function getWaveformImageUrlAttribute(): ?string
    {
        $isTypeImage = fn($file) => $file->getCustomProperty('type') === 'image';
        return $this->getFirstMedia('waveform', $isTypeImage)?->getUrl();
    }

    public function getIsWaveformReadyAttribute(): bool
    {
        $callback = fn($file) => $file->getCustomProperty('format') === 'dat';
        return $this->getMedia('waveform', $callback)->isNotEmpty();
    }

}
