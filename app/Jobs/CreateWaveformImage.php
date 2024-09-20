<?php

namespace App\Jobs;

use App\Models\Track;
use App\Services\AudioWaveformBuilder;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Str;

class CreateWaveformImage implements ShouldQueue
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
        $inputFilename = $this->track->getFirstMedia('waveform')->getPath();
        $outputFilename = Str::replaceLast('dat', 'png', $inputFilename);

        $processResult = $this->builder
            ->setInputFilename(escapeshellarg($inputFilename))
            ->setOutputFilename(escapeshellarg($outputFilename))
            ->setAxisLabels(true)
            ->setAxisLabelColor('D16D00FF')
            ->setEndTime($this->track->duration)
            ->generate();

        if ($processResult->successful()) {
            $this->track
                ->addMedia($outputFilename)
                ->withCustomProperties(['format' => 'png'])
                ->toMediaLibrary('waveform', 'waveform');
        }
    }

    private function bitsByDuration(): int
    {
        return $this->track->duration < 60 ? 16 : 8;
    }
}
