<?php

namespace App\Services;

use Illuminate\Process\ProcessResult;
use Illuminate\Support\Facades\Process;
use InvalidArgumentException;

class AudioWaveformBuilder
{
    public function __construct(
        private readonly CommandBuilder $builder = new CommandBuilder()
    )
    {
    }

    protected string $inputFilename;
    protected string $outputFilename;
    protected bool $quiet = false; // (default: false)
    protected bool $help = false; // (default: false)
    protected bool $version = false; // (default: false)
    protected bool $splitChannels = false; // (default: false)
    protected int|null $pixelsPerSecond = null; // (default: 100)
    protected int|string|null $zoom = null; // (default: 256)
    protected int $bits = 8; // (default: 16)
    protected float $amplitudeScale = 0.95; // (default: 1)
    protected bool $axisLabels = false; // (default: true)
    protected string $axisLabelColor = 'D16D00FF';
    protected string $backgroundColor = 'FFFFFF00';
    protected string $waveformColor = 'F9F9F9FF';
    protected string $waveformStyle = 'normal';
    protected int $width = 1280; // (default: 800)
    protected int $height = 120; // (default: 250)
    protected float $start = 0; // (default: 0)
    protected float $end = 0; // (default: 0)
    protected int $barWidth = 2; // (default: 8)
    protected int $barGap = 1; // (default: 4)
    protected string|false $borderColor = false;


    public function setHelp(bool $help): static
    {
        $this->help = $help;
        return $this;
    }

    public function setVersion(bool $version): static
    {
        $this->version = $version;
        return $this;
    }

    public function setQuiet(bool $quiet): static
    {
        $this->quiet = $quiet;
        return $this;
    }
    public function setInputFilename(string $inputFilename): static
    {
        $this->inputFilename = $inputFilename;
        return $this;
    }

    public function setOutputFilename(string $outputFilename): static
    {
        $this->outputFilename = $outputFilename;
        return $this;
    }

    public function setZoom(int|string $zoom): static
    {
        $this->zoom = $zoom;
        return $this;
    }

    public function setAxisLabels(bool $axisLabels): static
    {
        $this->axisLabels = $axisLabels;
        return $this;
    }

    public function setAxisLabelColor(string $axisLabelColor): static
    {
        $this->axisLabelColor = $this->validateAndNormalizeColor($axisLabelColor);
        return $this;
    }

    public function setSplitChannels(bool $splitChannels): static
    {
        $this->splitChannels = $splitChannels;
        return $this;
    }

    public function setPixelsPerSecond(int $pixelsPerSecond): static
    {
        $this->pixelsPerSecond = $pixelsPerSecond;
        return $this;
    }
    public function setBits(int $bits): static
    {
        $this->bits = $bits;
        return $this;
    }

    public function setWidth(int $width): static
    {
        $this->width = $width;
        return $this;
    }

    public function setHeight(int $height): static
    {
        $this->height = $height;
        return $this;
    }


    public function setStart(float $start): static
    {
        $this->start = $start;
        return $this;
    }

    public function setEnd(float $end): static
    {
        $this->end = $end;
        return $this;
    }

    public function setBackgroundColor(string $backgroundColor): static
    {
        $this->backgroundColor = $this->validateAndNormalizeColor($backgroundColor);
        return $this;
    }

    public function setWaveformColor(string $waveformColor): static
    {
        $this->waveformColor = $this->validateAndNormalizeColor($waveformColor);
        return $this;
    }

    public function setBorderColor(string $borderColor): static
    {
        $this->borderColor = $this->validateAndNormalizeColor($borderColor);
        return $this;
    }

    public function setWaveformStyle(string $waveformStyle): static
    {
        $this->waveformStyle = $waveformStyle;
        return $this;
    }

    public function setBarWidth(int $barWidth): static
    {
        $this->barWidth = $barWidth;
        return $this;
    }

    public function setBarGap(int $barGap): static
    {
        $this->barGap = $barGap;
        return $this;
    }
    public function setAmplitudeScale(float $amplitudeScale): static
    {
        $this->amplitudeScale = $amplitudeScale;
        return $this;
    }

    private function validateAndNormalizeColor(string $color): string
    {
        $color = ltrim($color, '#');
        return match (strlen($color)) {
            3 => strtoupper($color . $color . 'FF'),
            6 => strtoupper($color . 'FF'),
            8 => strtoupper($color),
            default => throw new InvalidArgumentException("Invalid color code: $color"),
        };
    }

    private function buildBaseCommand(): CommandBuilder
    {
        return $this->builder
            ->setCommand("audiowaveform")
            ->addOption('--input-filename', $this->inputFilename)
            ->addOption('--output-filename', $this->outputFilename)
            ->addOption('--bits', $this->bits)
            ->addOption('--amplitude-scale', $this->amplitudeScale)
            ->addOption('--width', $this->width)
            ->addOption('--height', $this->height)
            ->addOption('--start', $this->start)
            ->addConditionalOption('--end', $this->end, $this->end > 0)
            ->addConditionalOption('--zoom', $this->zoom, !!$this->zoom && !($this->end > 0))
            ->addConditionalOption('--pixels-per-second', $this->pixelsPerSecond, !!$this->pixelsPerSecond && !($this->end > 0) && !$this->zoom);

    }

    public function generate(): ProcessResult
    {
        $command = $this->buildBaseCommand()->getCommand();

        $processResult = Process::run($command);

        if ($processResult->failed()) {
            logger()->error('Failed to generate waveform data', [
                'output' => $processResult->output(),
                'error' => $processResult->errorOutput(),
            ]);
        }

        return $processResult;
    }

    public function generateImage(): ProcessResult
    {
        $commandBuilder = $this->buildBaseCommand()
            ->addOption('--background-color', $this->backgroundColor)
            ->addOption('--waveform-color', $this->waveformColor)
            ->addOption('--waveform-style', $this->waveformStyle)
            ->addOption('--bar-width', $this->barWidth)
            ->addOption('--bar-gap', $this->barGap)
            ->addConditionalOption('--border-color', $this->borderColor, $this->borderColor !== false)
            ->addConditionalArgument('--with-axis-labels', '--no-axis', $this->axisLabels)
            ->addConditionalOption('--axis-label-color', $this->axisLabelColor, $this->axisLabels);

        $command = $commandBuilder->getCommand();

        $processResult = Process::run($command);

        if ($processResult->failed()) {
            logger()->error('Failed to generate waveform image', [
                'output' => $processResult->output(),
                'error' => $processResult->errorOutput(),
            ]);
        }

        return $processResult;
    }

}
