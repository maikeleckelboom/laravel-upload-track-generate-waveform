<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;

class WaveformBuilder
{
    protected string $inputFilename;
    protected string $outputFilename;
    protected int $bits = 8;
    protected int $width = 3840;
    protected int $height = 500;
    protected float $endTime = 0;
    protected string $backgroundColor = 'FFFFFF00';
    protected string $waveformColor = 'FFDE87FF';
    protected string $waveformStyle = 'bars';
    protected int $barWidth = 2;
    protected int $barGap = 1;
    protected float $amplitudeScale = 0.975;

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
        $this->backgroundColor = $backgroundColor;
        return $this;
    }

    public function setWaveformColor(string $waveformColor): static
    {
        $this->waveformColor = $waveformColor;
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

    public function generateWaveform(): bool
    {
        $builder = new ShellCommandBuilder();
        $shellCommand = $builder
            ->setCommand("audiowaveform")
            ->addOption('-i', $this->inputFilename)
            ->addOption('-o', $this->outputFilename)
            ->addOption('--bits', $this->bits)
            ->addOption('--width', $this->width)
            ->addOption('--height', $this->height)
            ->addOption('--background-color', $this->backgroundColor)
            ->addOption('--waveform-color', $this->waveformColor)
            ->addOption('--waveform-style', $this->waveformStyle)
            ->addOption('--bar-width', $this->barWidth)
            ->addOption('--bar-gap', $this->barGap)
            ->addOption('--amplitude-scale', $this->amplitudeScale)
            ->addOption('--end', $this->endTime)
            ->getCommand();

        $processResult = Process::run($shellCommand);

        if ($processResult->failed()) {
            Log::error('Failed to generate waveform image.');
        }

        return $processResult->successful();
    }
}
