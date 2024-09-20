<?php

namespace App\Services;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class AudioTempoService
{
    /**
     * Calculate BPM from audio using aubio.
     *
     * @param string $filePath
     * @return float|null
     */
    public function calculateBPM(string $filePath): ?float
    {
        // Run aubio beat command to get beat timestamps
        $process = new Process(['aubio', 'beat', $filePath]);
        $process->run();

        // Check if the process was successful
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        // Extract timestamps from aubio output
        $output = $process->getOutput();
        $timestamps = $this->extractTimestamps($output);

        if (count($timestamps) < 2) {
            return null; // Not enough data to calculate BPM
        }

        // Calculate average interval and BPM
        $intervals = $this->calculateIntervals($timestamps);
        $averageInterval = array_sum($intervals) / count($intervals);
        $bpm = 60 / $averageInterval;

        return round($bpm, 2);
    }

    /**
     * Extract timestamps from aubio output.
     *
     * @param string $output
     * @return array
     */
    private function extractTimestamps(string $output): array
    {
        // Each line in the aubio output contains a timestamp
        $lines = explode("\n", trim($output));
        $timestamps = array_filter($lines, function ($line) {
            return is_numeric(trim($line));
        });

        return array_map('floatval', $timestamps);
    }

    /**
     * Calculate the time intervals between beats.
     *
     * @param array $timestamps
     * @return array
     */
    private function calculateIntervals(array $timestamps): array
    {
        $intervals = [];

        for ($i = 1; $i < count($timestamps); $i++) {
            $intervals[] = $timestamps[$i] - $timestamps[$i - 1];
        }

        return $intervals;
    }
}
