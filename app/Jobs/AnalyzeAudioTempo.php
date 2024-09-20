<?php

namespace App\Jobs;

use App\Models\Track;
use App\Services\AudioTempoService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Symfony\Component\Process\Exception\ProcessFailedException;

class AnalyzeAudioTempo implements ShouldQueue
{
    use Queueable;

    private readonly AudioTempoService $audioTempoService;

    /**
     * Create a new job instance.
     */
    public function __construct(private readonly Track $track)
    {
        $this->audioTempoService = new AudioTempoService();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $path = $this->track->getFirstMedia('audio')->getPath();

        try {
            $bpm = $this->audioTempoService->calculateBPM($path);
            logger()->info("Calculated BPM", ['bpm' => $bpm]);
        } catch (ProcessFailedException $e) {
            logger()->error("Failed to calculate BPM", [
                'message' => $e->getMessage(),
                'output' => $e->getProcess()->getOutput(),
                'errorOutput' => $e->getProcess()->getErrorOutput()
            ]);
        }
    }
}
