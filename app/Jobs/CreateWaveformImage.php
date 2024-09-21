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
    private readonly string $imageFormat;
    private readonly string $dataFormat;


    public function __construct(private readonly Track $track)
    {
        $this->builder = new AudioWaveformBuilder();
        $this->imageFormat = config('audio_waveform.image_format', 'png');
        $this->dataFormat = config('audio_waveform.data_format', 'dat');
    }

    public function handle(): void
    {
        $isWaveform = fn($file) => $file->getCustomProperty('waveform');
        $isData = fn($file) => $file->getCustomProperty('type') === 'data';
        $formatData = fn($file) => $file->getCustomProperty('format') === $this->dataFormat;

        $media = $this->track->getFirstMedia(
            'waveform',
            fn($file) => $isWaveform($file) && $isData($file) && $formatData($file)
        );

        $inputFilename = $media->getPath();
        $outputFilename = Str::replaceLast($this->dataFormat, $this->imageFormat, $inputFilename);

        $processResult = $this->builder
            ->setInputFilename(escapeshellarg($inputFilename))
            ->setOutputFilename(escapeshellarg($outputFilename))
            ->setBarWidth(4)
            ->setBarGap(2)
            ->setWaveformStyle('bars')
            ->setEnd($this->track->duration)
            ->generateImage();

        if ($processResult->successful()) {
            $this->track
                ->addMedia($outputFilename)
                ->withCustomProperties([
                    'waveform' => true,
                    'format' => $this->imageFormat,
                    'type' => 'image'
                ])
                ->toMediaLibrary('waveform', 'waveform');
        }
    }
}
