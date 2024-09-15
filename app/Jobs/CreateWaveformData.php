<?php

namespace App\Jobs;

use App\Models\Track;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Process;

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
        $audio = $this->track->getFirstMedia('audio');
        $inputFilename = $audio->getPath();

        if (!file_exists($inputFilename)) {
            logger()->error('File not found.');
            return;
        }

        $duration = $this->track->duration;


        $outputFilename = $inputFilename . '.dat';
        $shellCommand = "audiowaveform -i $inputFilename -o '$outputFilename'";
        $shellCommand .= " --bits 8";
        $shellCommand .= " --end $duration";

        $processResult = Process::run($shellCommand);

        if ($processResult->successful()) {
            logger()->info('Waveform data created.');

            $this->createWaveformImage($inputFilename, $duration);
        } else {
            logger()->error($processResult->errorOutput());
        }
    }

    public function createWaveformImage(string $inputFilename, int $duration): void
    {

        $TRANSPARENT_WHITE = 'FFFFFF00';
        $LIGHT_YELLOW = 'FFD580FF';
        $ORANGE = 'FFB347FF';

        // Create waveform image in directory of the audio file
        $outputFilename = $inputFilename . '.png';
        $shellCommand = "audiowaveform -i $inputFilename -o '$outputFilename' --output-format png";
//        $shellCommand .= " --end $duration";
        $shellCommand .= " --bits 16";
        $shellCommand .= " --zoom 256";

        // Image Style
        $shellCommand .= " --width 3840 --height 500";
        $shellCommand .= " --background-color $TRANSPARENT_WHITE";
        $shellCommand .= " --waveform-color $ORANGE";
//        $shellCommand .= " --no-axis-labels";


        $shellCommand .= " --amplitude-scale 0.975";
        // Waveform Style
        $shellCommand .= " --waveform-style bars";
        $shellCommand .= " --bar-width 2";
        $shellCommand .= " --bar-gap 1";

        $processResult = Process::run($shellCommand);

        if ($processResult->failed()) {
            logger()->error($processResult->errorOutput());
            return;
        }

        if ($processResult->successful()) {
            logger()->info('Waveform image created.');
        } else {
            logger()->error('Waveform image not created.');
        }
    }
}
