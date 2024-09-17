<?php

namespace App\Jobs;

use App\Models\Track;
use App\Services\AudioWaveformBuilder;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Str;

class CreateAudioWaveformImage implements ShouldQueue
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
        $binaryConversion = $this->track
            ->getMedia('waveform', fn($file) => $file->getCustomProperty('format') === 'dat')
            ->first();

        $inputFilename = $binaryConversion->getPath();
        $outputFilename = Str::replaceLast('dat', 'png', $inputFilename);

        $processResult = $this->builder
            ->setInputFilename(escapeshellarg($inputFilename))
            ->setOutputFilename(escapeshellarg($outputFilename))
            ->setWaveformStyle('normal')
            ->setWaveformColor('D2D1D9')
            ->setBarWidth(1)
            ->setBarGap(0)
            ->setWidth(1200)
            ->setHeight(100)
            ->setEndTime($this->track->duration)
            ->generate();

        if ($processResult->successful()) {
            $this->track
                ->addMedia($outputFilename)
                ->withCustomProperties([
                    'format' => 'png',
                    'type' => 'image'
                ])
                ->toMediaLibrary('waveform', 'waveform');
        }
    }
}
