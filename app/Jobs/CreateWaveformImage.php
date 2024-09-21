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

    private readonly AudioWaveformBuilder $builder;


    public function __construct(private readonly Track $track)
    {
        $this->builder = new AudioWaveformBuilder();
    }

    public function handle(): void
    {
        $media = $this->track->getFirstMedia('waveform');

        $inputFilename = $media->getPath();
        $outputFilename = Str::replaceLast('dat', 'png', $inputFilename);
        logger('outputFilename: ' . $outputFilename);

        $processResult = $this->builder
            ->setInputFilename(escapeshellarg($inputFilename))
            ->setOutputFilename(escapeshellarg($outputFilename))
            ->setEnd($this->track->duration)
            ->setBarWidth(4)
            ->setBarGap(2)
            ->setWaveformStyle('bars')
            ->generateImage();

        if ($processResult->successful()) {
            $this->track
                ->addMedia($outputFilename)
                ->withCustomProperties(['image' => true, 'format' => 'png'])
                ->toMediaLibrary('waveform', 'waveform');
        }
    }
}
