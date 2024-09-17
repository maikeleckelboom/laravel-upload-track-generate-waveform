<?php

namespace App\Jobs;

use App\Models\Track;
use App\Services\AudioWaveformBuilder;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Str;

class CreateAudioWaveform implements ShouldQueue
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
        $flacConversion = $this->track
            ->getMedia('audio', fn($file) => $file->getCustomProperty('format') === 'flac')
            ->first();

        $inputFilename = $flacConversion->getPath();
        $outputFilename = Str::replaceLast('flac', 'dat', $inputFilename);

        $processResult = $this->builder
            ->setInputFilename(escapeshellarg($inputFilename))
            ->setOutputFilename(escapeshellarg($outputFilename))
            ->setEndTime($this->track->duration)
            ->generate();

        if ($processResult->successful()) {
            $this->track
                ->addMedia($outputFilename)
                ->withCustomProperties([
                    'format' => 'dat',
                    'type' => 'binary'
                ])
                ->toMediaLibrary('waveform', 'waveform');
        }
    }
}
