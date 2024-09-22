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

        // if length is below 5 seconds, 16bit
        // if length is above 5 seconds, 8bit
        $bits = ceil($this->track->duration) < 5 ? 16 : 8;

        $processResult = $this->builder
            ->setInputFilename(escapeshellarg($inputFilename))
            ->setOutputFilename(escapeshellarg($outputFilename))
            ->setBits($bits)
            ->setEnd($this->track->duration)
            ->setWaveformStyle('bars')
            ->setBarWidth(4)
            ->setBarGap(2)
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
