<?php

namespace App\Services;

use Illuminate\Process\ProcessResult;
use Illuminate\Support\Facades\Log;
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
    protected int $bits = 8;
    protected int $width = 1280;
    protected int $height = 220;
    protected float $endTime = 0;
    protected string $backgroundColor = 'FFFFFF00';
    protected string $waveformColor = 'FFDE87FF';
    protected string $waveformStyle = 'bars'; // 'normal';
    protected int $barWidth = 2;
    protected int $barGap = 1;
    protected float $amplitudeScale = 0.995;
    protected string $borderColor = '';

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

    public function setEndTime(float $endTime): static
    {
        $this->endTime = $endTime;
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

    public function generate(): ProcessResult
    {
        $shellCommand = $this->builder
            ->setCommand("audiowaveform")
            ->addOption('--input-filename', $this->inputFilename)
            ->addOption('--output-filename', $this->outputFilename)
            ->addOption('--amplitude-scale', $this->amplitudeScale)
            ->addOption('--background-color', $this->backgroundColor)
            ->addOption('--waveform-color', $this->waveformColor)
            ->addOption('--waveform-style', $this->waveformStyle)
            ->addOption('--bar-width', $this->barWidth)
            ->addOption('--bar-gap', $this->barGap)
            ->addOption('--bits', $this->bits)
            ->addOption('--width', $this->width)
            ->addOption('--height', $this->height)
            ->addOption('--end', $this->endTime)
            ->addOption('--no-axis')
            ->addOption($this->borderColor ? $this->builder->addOption('--border-color', $this->borderColor) : '')
            ->getCommand();

        $processResult = Process::run($shellCommand);

        if ($processResult->failed()) {
            Log::error("Failed to generate waveform", [
                'inputFilename' => $this->inputFilename,
                'outputFilename' => $this->outputFilename,
                'error' => $processResult->errorOutput()
            ]);
        }

        return $processResult;
    }
}
