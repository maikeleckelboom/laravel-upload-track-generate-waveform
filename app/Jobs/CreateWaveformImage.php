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
    protected string $inputFormat;
    protected string $outputFormat;

    public function __construct(private readonly Track $track)
    {
        $this->builder = new AudioWaveformBuilder();
        $this->inputFormat = config('audio_waveform.waveform_data_format');
        $this->outputFormat = config('audio_waveform.waveform_image_format');
    }

    public function handle(): void
    {
        $inOutputFormat = fn($media) => $media->hasCustomProperty('format', $this->outputFormat);
        $media = $this->track->getFirstMedia('waveform', $inOutputFormat);

        $inputFilename = $media?->getPath();
        $outputFilename = Str::replaceLast($this->inputFormat, $this->outputFormat, $inputFilename);

        $processResult = $this->builder
            ->setInputFilename(escapeshellarg($inputFilename))
            ->setOutputFilename(escapeshellarg($outputFilename))
            ->setEnd($this->track->duration)
            ->setBarWidth(2)
            ->setBarGap(1)
            ->setWaveformStyle('bars')
            ->generateImage();

        if ($processResult->successful()) {
            $this->track
                ->addMedia($outputFilename)
                ->withCustomProperties(['image' => true, 'format' => $this->outputFormat])
                ->toMediaLibrary('waveform', 'waveform');
        }
    }
}
