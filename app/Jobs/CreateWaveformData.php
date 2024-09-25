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

    private const int DEFAULT_BITS = 8;
    private readonly AudioWaveformBuilder $builder;
    private readonly string $format;

    public function __construct(private readonly Track $track)
    {
        $this->builder = new AudioWaveformBuilder();
        $this->format = config('audio_waveform.data_format', 'dat');
    }

    public function handle(): void
    {
        $playbackAudio = $this->track->getFirstMedia(
            'audio',
            fn($file) => $file->getCustomProperty('playback')
        );

        $inputFilename = $playbackAudio->getPath();
        $outputFilename = Str::replaceLast($playbackAudio->extension, $this->format, $inputFilename);

        $processResult = $this->builder
            ->setInputFilename(escapeshellarg($inputFilename))
            ->setOutputFilename(escapeshellarg($outputFilename))
            ->setEnd($this->track->duration)
            ->setBits(self::DEFAULT_BITS)
            ->setQuiet(true)
            ->generate();

        if ($processResult->successful()) {
            $this->track
                ->addMedia($outputFilename)
                ->withCustomProperties([
                    'format' => $this->format,
                    'type' => 'data'
                ])
                ->toMediaLibrary('waveform', 'waveform');
        }
    }
}
