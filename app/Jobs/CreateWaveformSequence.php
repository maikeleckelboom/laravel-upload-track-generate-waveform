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

    private readonly AudioWaveformBuilder $builder;
    final const int DEFAULT_BITS = 8;
    final const string WAVEFORM_STYLE = 'bars';
    final const int DEFAULT_BAR_WIDTH = 4;
    final const int DEFAULT_BAR_GAP = 2;

    private string $imageFormat = 'png';
    private readonly string $dataFormat;


    public function __construct(private readonly Track $track)
    {
        $this->builder = new AudioWaveformBuilder();
        $this->dataFormat = config('audio_waveform.data_format', 'dat');
    }

    public function handle(): void
    {
        $isData = fn($file) => $file->getCustomProperty('type') === 'image';

        $formatData = fn($file) => $file->getCustomProperty('format') === $this->dataFormat;

        $inputFilename = $this->track
            ->getFirstMedia(
                'waveform',
                fn($file) => $isData($file) && $formatData($file)
            )
            ?->getPath();

        $zoomLevels = collect([128, 256, 512, 1024, 2048, 4096]);

        $params = collect([
            'bits' => self::DEFAULT_BITS,
            'end' => $this->track->duration,
        ]);

        $outputFilename = $this->createOutputFilename($inputFilename, $params);

        // Calculate the count of snapshots needed for a specific zoom level, to cover whole duration.
        $count = $this->track->duration / $zoomLevels->first();

        // If the count is greater than 1, create many waveforms at the first zoom level.
        if ($count > 1) {
            $this->createManyWaveformsAtZoomForWholeTrack();
            return;
        }


        $processOverviewResult = $this->builder
            ->setInputFilename(escapeshellarg($inputFilename))
            ->setOutputFilename(escapeshellarg($outputFilename))
            ->setEnd($this->track->duration)
            ->setBits(self::DEFAULT_BITS)
            ->generate();

        if ($processOverviewResult->failed()) {
            return;
        }

        // Now create the data files with the zoom levels
        $zoomLevels->each(function ($zoom) use ($inputFilename) {
            $params = collect([
                'waveformStyle' => self::WAVEFORM_STYLE,
                'barWidth' => self::DEFAULT_BAR_WIDTH,
                'barGap' => self::DEFAULT_BAR_GAP,
                'bits' => self::DEFAULT_BITS,
                'zoom' => $zoom,
                'end' => $this->track->duration,
            ]);

            $outputFilename = $this->createOutputFilename($inputFilename, $params);

            $result = $this->builder
                ->setInputFilename(escapeshellarg($inputFilename))
                ->setOutputFilename(escapeshellarg($outputFilename))
                ->setZoom($zoom)
                ->setEnd($this->track->duration)
                ->generate();

            if ($result->successful()) {
                $this->track
                    ->addMedia($outputFilename)
                    ->withCustomProperties([
                        'format' => $this->dataFormat,
                        'type' => 'data',
                        'zoom' => $params['zoom']
                    ])
                    ->toMediaLibrary('waveform', 'waveform');
            }
        });


    }

    private function createManyWaveformsAtZoomForWholeTrack()
    {
        $trackDuration = $this->track->duration;
        $zoomLevels = collect([128, 256, 512, 1024, 2048, 4096]);

        $zoomLevels->each(function ($zoom) use ($trackDuration) {
            $count = $trackDuration / $zoom;
            $start = 0;
            $end = $zoom;

            for ($i = 0; $i < $count; $i++) {
                $params = collect([
//                    'waveformStyle' => self::WAVEFORM_STYLE,
//                    'barWidth' => self::DEFAULT_BAR_WIDTH,
//                    'barGap' => self::DEFAULT_BAR_GAP,
                    'bits' => self::DEFAULT_BITS,
                    'zoom' => $zoom,
                    'start' => $start,
                    'end' => $end,
                ]);

                $inputFilename = $this->track
                    ->getFirstMedia(
                        'waveform',
                        fn($file) => $file->getCustomProperty('waveform') && $file->getCustomProperty('type') === 'data'
                    )
                    ?->getPath();

                $outputFilename = $this->createOutputFilename($inputFilename, $params);

                $result = $this->builder
                    ->setInputFilename(escapeshellarg($inputFilename))
                    ->setOutputFilename(escapeshellarg($outputFilename))
                    ->setZoom($zoom)
                    ->setStart($start)
                    ->setEnd($end)
                    ->generate();

                if ($result->successful()) {
                    $this->track
                        ->addMedia($outputFilename)
                        ->withCustomProperties([
                            'waveform' => true,
                            'conversion' => true,
                            'format' => $this->dataFormat,
                            'type' => 'data',
                            'zoom' => $params['zoom']
                        ])
                        ->toMediaLibrary('waveform', 'waveform');
                }

                $start += $zoom;
                $end += $zoom;
            }
        });
    }

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
            'zoom' => "zoom-{$value}",
            'bits' => "bits-{$value}",
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
