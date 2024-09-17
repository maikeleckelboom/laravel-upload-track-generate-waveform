<?php

namespace App\Jobs;

use App\Models\Track;
use App\Services\AudioWaveformBuilder;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Str;

class CreateAudioWaveformData implements ShouldQueue
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
            ->getMedia('audio', fn($file) => $file->getCustomProperty('type') === 'playback')
            ->first();

        logger()->info("Playback audio path: {$playbackAudio->getPath()}");

        $outputFilename = Str::replaceLast(
            $playbackAudio->getCustomProperty('format'),
            'json',
            $playbackAudio->getPath()
        );

        $processResult = $this->builder
            ->setInputFilename(escapeshellarg($playbackAudio->getPath()))
            ->setOutputFilename(escapeshellarg($outputFilename))
            ->setEndTime($this->track->duration)
            ->generate();

        if ($processResult->successful()) {
            $this->track
                ->addMedia($outputFilename)
                ->withCustomProperties([
                    'format' => 'json',
                    'type' => 'waveform'
                ])
                ->toMediaLibrary('waveform', 'waveform');
        }
    }
}
