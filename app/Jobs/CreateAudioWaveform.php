<?php

namespace App\Jobs;

use App\Models\Track;
use App\Services\AudioWaveformBuilder;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CreateAudioWaveform implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private readonly Track                $track,
        private readonly AudioWaveformBuilder $builder = new AudioWaveformBuilder()
    )
    {

    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $inputFilename = $this->track->getFirstMedia('audio')->getPath();

        $successful = $this->builder
            ->setInputFilename($inputFilename)
            ->setOutputFilename($outputFilename = $inputFilename . '.dat')
            ->setEndTime($endTime = $this->track->duration)
            ->generate();

        if ($successful) {
            $this->createWaveformImage($outputFilename, $endTime);
        }
    }

    private function createWaveformImage(string $inputFilename, float $endTime): bool
    {
        return $this->builder
            ->setInputFilename($inputFilename)
            ->setOutputFilename($inputFilename . '.png')
            ->setEndTime($endTime)
            ->setWaveformColor('FFDE87FF')
            ->setBackgroundColor('FFFFFF00')
            ->generate();
    }
}
