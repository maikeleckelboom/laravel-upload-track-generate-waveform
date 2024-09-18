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
            ->getFirstMedia('audio', fn($file) => $file->getCustomProperty('type') === 'playback');

        $outputFilename = Str::replaceLast(
            $playbackAudio->getCustomProperty('format'),
            'dat',
            $playbackAudio->getPath()
        );

        $processResult = $this->builder
            ->setInputFilename(escapeshellarg($playbackAudio->getPath()))
            ->setOutputFilename(escapeshellarg($outputFilename))
            ->setEndTime($this->track->duration)
            ->setWidth( 1280)
            ->setHeight(120)
            ->setBits(self::bitsByDuration($this->track->duration))
            ->generate();

        if ($processResult->successful()) {
            $this->track
                ->addMedia($outputFilename)
                ->withCustomProperties(['format' => 'dat'])
                ->toMediaLibrary('waveform', 'waveform');
        }
    }

    public static function bitsByDuration(float $duration): int
    {
        return $duration < 60 ? 16 : 8;
    }
}
