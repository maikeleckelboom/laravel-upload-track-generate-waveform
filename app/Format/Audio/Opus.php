<?php

namespace App\Format\Audio;

use FFMpeg\Format\Audio\DefaultAudio;

class Opus extends DefaultAudio
{
    public function __construct()
    {
        $this->audioCodec = 'opus';
    }

    /**
     * {@inheritDoc}
     */
    public function getAvailableAudioCodecs(): array
    {
        return ['opus'];
    }
}
