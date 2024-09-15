<?php

namespace App\Jobs;

use App\Models\Track;
use App\Services\WaveformBuilder;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CreateWaveformData implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private readonly Track $track
    )
    {

    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $inputFilename = $this->track->getFirstMedia('audio')->getPath();
        $outputFilename = $inputFilename . '.dat';

        $duration = $this->track->duration;

        logger([
            'duration' => $duration,
            'duration_int' => intval($duration)
        ]);

        $waveformBuilder = new WaveformBuilder();
        $successful = $waveformBuilder
            ->setInputFilename($inputFilename)
            ->setOutputFilename($outputFilename)
            ->setBits(8)
            ->setWidth(3840)
            ->setHeight(500)
            ->setEndTime($this->track->duration)
            ->generateWaveform();

        if ($successful) {
            $this->createWaveformImage($outputFilename, $duration);
        }
    }

    private function createWaveformImage(string $inputFilename, float $endTime): void
    {
        $outputFilename = $inputFilename . '.png';

        $waveformBuilder = new WaveformBuilder();
        $success = $waveformBuilder
            ->setInputFilename($inputFilename)
            ->setOutputFilename($outputFilename)
            ->setEndTime($endTime)
            ->setBackgroundColor('FFFFFF00')
            ->setWaveformColor('FFDE87FF')
            ->generateWaveform();

        if (!$success) {
            logger()->error('Failed to generate waveform image.');
        }
    }

//    public function createWaveformImage(string $inputFilename, float $duration): void
//    {
//
//        $backgroundColor = 'FFFFFF00';
//        $waveformColor = 'FFDE87FF';
//
//        $outputFilename = $inputFilename . '.png';
//        $shellCommand = "audiowaveform -i $inputFilename -o $outputFilename --output-format png";
//        $shellCommand .= " --bits 8";
//        $shellCommand .= " --end $duration";
////        $shellCommand .= " --zoom 256";
////        $shellCommand .= " --pixels-per-second 100";
//        $shellCommand .= " --width 3840 --height 500";
//        $shellCommand .= " --background-color $backgroundColor";
//        $shellCommand .= " --waveform-color $waveformColor";
//        $shellCommand .= " --waveform-style bars";
//        $shellCommand .= " --bar-width 2";
//        $shellCommand .= " --bar-gap 1";
//        $shellCommand .= " --amplitude-scale 0.975";
////        $shellCommand .= " --no-axis-labels";
//
//        $processResult = Process::run($shellCommand);
//
//        if ($processResult->failed()) {
//            logger()->error($processResult->errorOutput());
//        }
//    }
}
