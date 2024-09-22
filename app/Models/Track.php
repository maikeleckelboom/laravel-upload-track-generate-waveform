<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Track extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    protected $guarded = [];

    protected $appends = [
        'playback_url',
        'waveform_data_url',
        'waveform_image_url',
    ];

    protected $hidden = [
        'media',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function uploads(): MorphMany
    {
        return $this->morphMany(Upload::class, 'uploadable');
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
        $isTypePlayback = fn($file) => $file->getCustomProperty('playback');
        return $this->getFirstMedia('audio', $isTypePlayback)?->getUrl();
    }

    public function getWaveformDataUrlAttribute(): ?string
    {
        $typeData = fn($file) => $file->getCustomProperty('type') === 'data';
        $dataFormat = fn($file) => $file->getCustomProperty('format') === config('audio_waveform.waveform_data_format', 'dat');
        return $this->getFirstMedia('waveform', fn($file) => $typeData($file) && $dataFormat($file))?->getUrl();
    }

    public function getWaveformImageUrlAttribute(): ?string
    {
        $imageFormat = fn($file) => $file->getCustomProperty('type') === 'image';
        $imageType = fn($file) => $file->getCustomProperty('format') === config('audio_waveform.waveform_image_format', 'png');
        return $this->getFirstMedia('waveform', fn($file) => $imageFormat($file) && $imageType($file))?->getUrl();
    }

    public function registerMediaConversions(?Media $media = null): void
    {

    }
}
