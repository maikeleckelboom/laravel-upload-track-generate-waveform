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

    private readonly AudioWaveformBuilder $builder;
    private const int DEFAULT_BITS = 8;
    private const string WAVEFORM_STYLE = 'bars';
    private const int DEFAULT_BAR_WIDTH = 1;
    private const int DEFAULT_BAR_GAP = 1;
    private const int DEFAULT_WIDTH = 1280;
    private const int DEFAULT_HEIGHT = 128;
    private string $imageFormat = 'png';
    private readonly string $dataFormat;

    public function __construct(private readonly Track $track)
    {
        $this->builder = new AudioWaveformBuilder();
        $this->dataFormat = config('audio_waveform.data_format', 'dat');
    }

    public function handle(): void
    {
        $isData = fn($file) => $file->getCustomProperty('type') === 'data';
        $formatData = fn($file) => $file->getCustomProperty('format') === $this->dataFormat;

        $inputFilename = $this->track
            ->getFirstMedia(
                'waveform',
                fn($file) => $isData($file) && $formatData($file)
            )
            ?->getPath();

        $params = collect([
            'waveformStyle' => self::WAVEFORM_STYLE,
            'barWidth' => self::DEFAULT_BAR_WIDTH,
            'barGap' => self::DEFAULT_BAR_GAP,
            'bits' => self::DEFAULT_BITS,
            'width' => self::DEFAULT_WIDTH,
            'height' => self::DEFAULT_HEIGHT,
            'end' => $this->track->duration,
        ]);

        $outputFilename = $this->replaceExtensionAddParams($inputFilename, $params);

        $processResult = $this->builder
            ->setInputFilename(escapeshellarg($inputFilename))
            ->setOutputFilename(escapeshellarg($outputFilename))
            ->setWaveformStyle($params->get('waveformStyle'))
            ->setBarWidth($params->get('barWidth'))
            ->setBarGap($params->get('barGap'))
            ->setBits($params->get('bits'))
            ->setEnd($params->get('end'))
            ->setWidth($params->get('width'))
            ->setHeight($params->get('height'))
            ->setQuiet(true)
            ->generateImage();

        if ($processResult->successful()) {
            $this->track
                ->addMedia($outputFilename)
                ->withCustomProperties([
                    'format' => $this->imageFormat,
                    'type' => 'image'
                ])
                ->toMediaLibrary('waveform', 'waveform');
        }
    }

    private function replaceExtensionAddParams(string $inputFilename, Collection|array $params): string
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

    private function formatParam(string $key, $value): string
    {
        return match ($key) {
            'waveformStyle' => "style-{$value}",
            'barWidth' => "bar-width-{$value}",
            'barGap' => "bar-gap-{$value}",
            'bits' => "bits-{$value}",
            'end' => $this->formatTimestamp($value),
            'width' => "width-{$value}",
            'height' => "height-{$value}",
            default => "{$key}_{$value}",
        };
    }

    private function formatTimestamp(int $timestamp): string
    {
        return 'end-' . Carbon::createFromTimestamp($timestamp)->format('i_s');
    }
}
