<?php

namespace App\Services;


use App\Models\Track;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

class AudioProcessor
{
    public function process(Track $track): void
    {

    }

    public function getDurationInSeconds(Track $track): float
    {
        $audio = $track->getFirstMedia('audio');
        $opener = FFMpeg::fromDisk($audio->disk)->open($audio->getPathRelativeToRoot());
        return $opener->getAudioStream()->get('duration');
    }
}
