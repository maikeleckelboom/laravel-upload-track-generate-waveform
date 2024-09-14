<?php

namespace App\Actions;

class ProcessMetadata
{

    public function __construct(
        private readonly string $path
    )
    {
    }

    public function execute(): void
    {
        $file = storage_path('app/' . $this->path);

        $getID3 = new \getID3;
        $metadata = $getID3->analyze($file);

        $track = Track::create([
            'title' => $metadata['tags']['id3v2']['title'][0] ?? null,
            'artist' => $metadata['tags']['id3v2']['artist'][0] ?? null,
            'album' => $metadata['tags']['id3v2']['album'][0] ?? null,
            'year' => $metadata['tags']['id3v2']['year'][0] ?? null,
            'genre' => $metadata['tags']['id3v2']['genre'][0] ?? null,
            'duration' => $metadata['playtime_seconds'],
            'path' => $this->path,
        ]);

        AudioMetadata::create([
            'track_id' => $track->id,
            'bitrate' => $metadata['audio']['bitrate'] ?? null,
            'sample_rate' => $metadata['audio']['sample_rate'] ?? null,
            'channels' => $metadata['audio']['channels'] ?? null,
            'bits_per_sample' => $metadata['audio']['bits_per_sample'] ?? null,
        ]);
    }

}
