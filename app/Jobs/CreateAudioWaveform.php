<?php

namespace App\Jobs;

use App\Models\Track;
use App\Services\AudioWaveformBuilder;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CreateAudioWaveform implements ShouldQueue
{
    use Queueable;

    private AudioWaveformBuilder $builder;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private readonly Track $track
    )
    {
        $this->builder = new AudioWaveformBuilder();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $inputFilename = $this->track->getFirstMedia('audio')->getPath();

        $this->builder
            ->setInputFilename($inputFilename)
            ->setOutputFilename($inputFilename . '.dat')
            ->setEndTime($this->track->duration)
            ->setBits(8)
            ->generate();
    }
    /**
     * Create a waveform image from the generated data file.
     */
    private function createWaveformImage(string $inputFilename, float $endTime): bool
    {
        return $this->builder
            ->setInputFilename($inputFilename)
            ->setOutputFilename($inputFilename . '.png')
            ->setEndTime($endTime)
            ->setWidth(3840)
            ->setHeight(500)
            ->setBits(8)
            ->setBackgroundColor('FFFFFF00')
            ->setWaveformColor('FFDE87FF')
            ->setWaveformStyle('bars')
            ->setBarWidth(2)
            ->setBarGap(1)
            ->setAmplitudeScale(0.975)
            ->generate();
    }
}
