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
        $binaryConversion = $this->track->getFirstMedia('waveform');

        $inputFilename = $binaryConversion->getPath();
        $outputFilename = Str::replaceLast('dat', 'png', $inputFilename);

        $processResult = $this->builder
            ->setInputFilename(escapeshellarg($inputFilename))
            ->setOutputFilename(escapeshellarg($outputFilename))
            ->setWaveformColor('D2D1D9')
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

    private function bitsByDuration(): int
    {
        return $this->track->duration < 60 ? 16 : 8;
    }
}
