<?php

namespace App\Jobs;

use App\Models\Track;
use App\Services\AudioWaveformBuilder;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Str;

class CreateAudioWaveform implements ShouldQueue
{
    use Queueable;

    private AudioWaveformBuilder $builder;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private readonly Track $track
    )
    {
        $this->builder = new AudioWaveformBuilder();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        $flacConversion = $this->track
            ->getMedia('audio', fn($file) => $file->getCustomProperty('format') === 'flac')
            ->first();

        $inputFilename = $flacConversion->getPath();
        $outputFilename = Str::replaceLast('flac', 'dat', $inputFilename);

        $successful = $this->builder
            ->setInputFilename(escapeshellarg($inputFilename))
            ->setOutputFilename(escapeshellarg($outputFilename))
            ->setEndTime($this->track->duration)
            ->generate();

        if ($successful) {
            $this->track->addMedia($outputFilename)
                ->withCustomProperties(['source_format' => 'flac'])
                ->toMediaLibrary('waveforms', 'waveforms');
        } else {
            logger()->error('Failed to generate waveform.', ['track_id' => $this->track->id]);
        }
    }

    /**
     * Create a waveform image from the generated data file.
     */
    private function createWaveformImage(string $inputFilename, float $endTime): bool
    {
        return $this->builder
            ->setInputFilename($inputFilename)
            ->setOutputFilename($inputFilename . '.png')
            ->setEndTime($endTime)
            ->setWidth(3840)
            ->setHeight(500)
            ->setBits(8)
            ->setBackgroundColor('FFFFFF00')
            ->setWaveformColor('FFDE87FF')
            ->setWaveformStyle('bars')
            ->setBarWidth(2)
            ->setBarGap(1)
            ->setAmplitudeScale(0.975)
            ->generate();
    }
}
