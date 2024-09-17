<?php

namespace App\Models;

use Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Filesystem\FilesystemAdapter;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Track extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    protected $guarded = [];

    protected $appends = ['stream_path', 'waveform_data_url', 'stream_url'];

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
        $frontendUrl = config('app.frontend_url');
        return "{$frontendUrl}/track/{$this->id}/stream";
    }

    public function getWaveformDataUrlAttribute(): string
    {
        return $this->getFirstMedia('audio')->getUrl() . '.dat';
    }
}
