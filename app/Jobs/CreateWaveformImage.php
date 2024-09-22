<?php

namespace App\Jobs;

use App\Models\Track;
use App\Services\AudioWaveformBuilder;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class CreateWaveformImage implements ShouldQueue
{
    use Queueable;

    private const string WAVEFORM_STYLE = 'bars';
    private const int DEFAULT_BAR_WIDTH = 4;
    private const int DEFAULT_BAR_GAP = 2;
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

        $inputFilename = $this->track
            ->getFirstMedia(
                'waveform',
                fn($file) => $isWaveform($file) && $isData($file) && $formatData($file)
            )
            ?->getPath();

        $params = collect([
            'waveformStyle' => self::WAVEFORM_STYLE,
            'barWidth' => self::DEFAULT_BAR_WIDTH,
            'barGap' => self::DEFAULT_BAR_GAP,
            'bits' => $this->getBits($this->track->duration),
            'end' => $this->track->duration,
        ]);

        $outputFilename = $this->createOutputFilename($inputFilename, $params);

        $processResult = $this->builder
            ->setInputFilename(escapeshellarg($inputFilename))
            ->setOutputFilename(escapeshellarg($outputFilename))
            ->setWaveformStyle($params->get('waveformStyle'))
            ->setBarWidth($params->get('barWidth'))
            ->setBarGap($params->get('barGap'))
            ->setBits($params->get('bits'))
            ->setEnd($params->get('end'))
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
     * Determine the bits based on track duration.
     */
    private function getBits(int $duration): int
    {
        return ceil($duration) < 5 ? 16 : 8;
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
