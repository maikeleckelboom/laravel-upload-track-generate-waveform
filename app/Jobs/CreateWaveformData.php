<?php

namespace App\Jobs;

use App\Models\Track;
use App\Services\AudioWaveformBuilder;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Str;

class CreateWaveformData implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Track                $track,
        private readonly AudioWaveformBuilder $builder = new AudioWaveformBuilder()
    )
    {
    }

    public function handle(): void
    {
        $playbackAudio = $this->track
            ->getFirstMedia('audio', fn($file) => !!$file->getCustomProperty('playback'));

        $playbackAudioPath = $playbackAudio->getPath();

        $playbackFormat = $playbackAudio->extension;

        $outputFilename = Str::replaceLast($playbackFormat, 'dat', $playbackAudioPath);

        logger('outputFilename: ' . $outputFilename);

        $processResult = $this->builder
            ->setInputFilename(escapeshellarg($playbackAudioPath))
            ->setOutputFilename(escapeshellarg($outputFilename))
            ->setEnd($this->track->duration)
            ->setBits(8)
            ->generate();

        if ($processResult->successful()) {
            $this->track
                ->addMedia($outputFilename)
                ->withCustomProperties(['waveform' => true, 'format' => 'dat'])
                ->toMediaLibrary('waveform', 'waveform');
        }
    }
}
