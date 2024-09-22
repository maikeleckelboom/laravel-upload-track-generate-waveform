<?php

namespace App\Jobs;

use App\Models\Track;
use App\Services\AudioWaveformBuilder;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Str;

class CreateWaveformData implements ShouldQueue
{
    use Queueable;

    private readonly AudioWaveformBuilder $builder;
    private readonly string $dataFormat;


    public function __construct(private readonly Track $track)
    {
        $this->builder = new AudioWaveformBuilder();
        $this->dataFormat = config('audio_waveform.data_format', 'dat');
    }

    public function handle(): void
    {
        $playbackAudio = $this->track->getFirstMedia(
            'audio',
            fn($file) => $file->getCustomProperty('playback')
        );

        $inputFilename = $playbackAudio->getPath();
        $outputFilename = Str::replaceLast($playbackAudio->extension, $this->dataFormat, $inputFilename);

        $processResult = $this->builder
            ->setInputFilename(escapeshellarg($inputFilename))
            ->setOutputFilename(escapeshellarg($outputFilename))
            ->setEnd($this->track->duration)
            ->setBits(8)
            ->setQuiet(true)
            ->generate();

        if ($processResult->successful()) {
            $this->track
                ->addMedia($outputFilename)
                ->withCustomProperties([
                    'waveform' => true,
                    'format' => $this->dataFormat,
                    'type' => 'data'
                ])
                ->toMediaLibrary('waveform', 'waveform');
        }
    }

    private function createWaveforms()
    {

        $sampleRate = 48000;
        $duration = $this->track->duration;

        $zooms = [
            3840,
            1280,
            800,
            400,
            200,
            100,
            50,
        ];


        $inputFilename = $this->track->getFirstMedia('waveform')->getPath();

        foreach ($zooms as $zoomLevel) {
            $outputFilename = Str::replaceLast('dat', "{$zoomLevel}.dat", $inputFilename);

            $processResult = $this->builder
                ->setInputFilename(escapeshellarg($inputFilename))
                ->setOutputFilename(escapeshellarg($outputFilename))
                ->setZoom($zoomLevel)
                ->generate();

            if ($processResult->successful()) {
                $this->track
                    ->addMedia($outputFilename)
                    ->withCustomProperties([
                        'waveform' => true,
                        'format' => 'dat',
                        'type' => 'data',
                        'zoom' => $zoomLevel
                    ])
                    ->toMediaLibrary('waveform', 'waveform');
            }
        }

    }
}
