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
        'waveform_image_url',
        'waveform_data_url',
        'playback_url',
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

    public function getPlaybackStreamPathAttribute(): string
    {
        return "track/{$this->id}/stream";
    }

    public function getPlaybackUrlAttribute(): ?string
    {
        return $this->getMedia('audio', fn($file) => $file->getCustomProperty('type') === 'playback')
            ->first()
            ?->getUrl();
    }

    public function getWaveformDataUrlAttribute(): ?string
    {
        return $this->getMedia('waveform', fn($file) => $file->getCustomProperty('type') === 'waveform' && $file->getCustomProperty('format') === 'dat')
            ->first()
            ?->getUrl();
    }

    public function getWaveformImageUrlAttribute(): ?string
    {
        return $this->getFirstMedia('waveform', fn($file) => $file->getCustomProperty('type') === 'image')?->getUrl();
    }

    public function getIsWaveformReadyAttribute(): bool
    {
        $callback = fn($file) => $file->getCustomProperty('type') === 'waveform' && $file->getCustomProperty('format') === 'dat';
        return $this->getMedia('waveform', $callback)->isNotEmpty();
    }

}
