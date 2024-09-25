<?php

namespace App\Services;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class AubioBeatService
{
    /**
     * Calculate BPM from audio using aubio.
     *
     * @param string $filePath
     * @return float|null
     */
    public function calculateBpm(string $filePath): ?float
    {
        $process = new Process(['aubio', 'beat', $filePath]);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $output = $process->getOutput();
        $timestamps = $this->extractTimestamps($output);

        if (count($timestamps) < 2) {
            return null;
        }

        $bpm = $this->calculateAverageBpm($timestamps);

        return round($bpm, 2);
    }
    /**
     * @param array $timestamps
     * @return float|int
     */
    public function calculateAverageBpm(array $timestamps): int|float
    {
        $intervals = $this->calculateIntervals($timestamps);
        $averageInterval = array_sum($intervals) / count($intervals);
        return 60 / $averageInterval;
    }

    /**
     * Extract timestamps from aubio output.
     *
     * @param string $output
     * @return array
     */
    private function extractTimestamps(string $output): array
    {
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
