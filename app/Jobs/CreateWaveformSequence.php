<?php

namespace App\Jobs;

use App\Models\Track;
use App\Services\AudioWaveformBuilder;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class CreateWaveformSequence implements ShouldQueue
{
    use Queueable;

    final const int DEFAULT_BITS = 8;
    final const string WAVEFORM_STYLE = 'bars';
    final const int DEFAULT_BAR_WIDTH = 4;
    final const int DEFAULT_BAR_GAP = 2;
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
        $this->createWaveformSequence();
    }

    private function zoomsFromDuration(int $duration): Collection
    {
        $bits = self::DEFAULT_BITS;
        $zooms = collect([1, 2, 4, 8, 16, 32, 64, 128, 256, 512]);

        return $zooms
            ->map(fn($zoom) => $zoom * $duration)
            ->filter(fn($zoom) => $zoom <= 3600)
            ->map(fn($zoom) => $zoom * $bits);
    }

    private function createWaveformSequence(): void
    {

        $duration = $this->track->duration;
        $zooms = $this->zoomsFromDuration($duration);

        logger()->info('Zooms from duration', $zooms->toArray());

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

    /*
     * Private Methods
     */
    private function createOutputFilename(string $inputFilename, Collection|array $params): string
    {
        $outputFilename = Str::replaceLast($this->dataFormat, $this->imageFormat, $inputFilename);

        $formattedParams = collect($params)
            ->map(fn($value, $key) => $this->formatParam($key, $value))
            ->implode('_');

        return Str::replaceLast(".{$this->imageFormat}",
            "_{$formattedParams}.{$this->imageFormat}",
            $outputFilename
        );
    }

    /**
     * Format the parameters into the desired string format.
     */
    private function formatParam(string $key, $value): string
    {
        return match ($key) {
            'waveformStyle' => "style-{$value}",
            'barWidth' => "bar-width-{$value}",
            'barGap' => "bar-gap-{$value}",
            'bits' => "bits-{$value}",
            'end' => $this->formatTimestamp($value),
            default => "{$key}_{$value}",
        };
    }

    /**
     * Format the timestamp for the 'end' parameter.
     */
    private function formatTimestamp(int $timestamp): string
    {
        return 'end-' . Carbon::createFromTimestamp($timestamp)->format('i_s');
    }
}
