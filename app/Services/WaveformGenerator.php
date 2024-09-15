<?php

namespace App\Services;

use App\Models\Track;
use FFMpeg\FFProbe;
use Illuminate\Support\Facades\Storage;

class WaveformGenerator
{
    public function generateWaveforms(Track $track)
    {
        $audioPath = $track->getFirstMediaPath('audio');
        $outputDir = storage_path('app/public/waveforms/' . $track->id);
        $segmentDuration = 20;
        $imageWidth = 1000;
        $imageHeight = 200;

        if (!file_exists($outputDir)) {
            mkdir($outputDir, 0777, true);
        }

        $audioDuration = $track->duration;
        $startTime = 0;

        while ($startTime < $audioDuration) {
            $endTime = min($startTime + $segmentDuration, $audioDuration);
            $outputFile = $outputDir . "/waveform_{$startTime}_to_{$endTime}.png";

            $this->generateWaveformSegment($audioPath, $outputFile, $startTime, $endTime, $imageWidth, $imageHeight);

            $startTime = $endTime;
        }
    }

    private function generateWaveformSegment($inputFile, $outputFile, $startTime, $endTime, $width, $height): void
    {
        $command = "audiowaveform -i $inputFile -o $outputFile -s $startTime -e $endTime -w $width -h $height";
        shell_exec($command);
    }
}
