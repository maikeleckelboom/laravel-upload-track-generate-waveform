<?php

namespace App\Jobs;

use App\Models\Track;
use App\Services\DetectTempoService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Symfony\Component\Process\Exception\ProcessFailedException;

class AnalyzeAudioTempo implements ShouldQueue
{
    use Queueable;

    private readonly DetectTempoService $audioTempoService;

    /**
     * Create a new job instance.
     */
    public function __construct(private readonly Track $track)
    {
        $this->audioTempoService = new DetectTempoService();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $path = $this->track->getFirstMedia('audio')->getPath();
        try {
            $bpm = $this->audioTempoService->calculateBPM($path);
            $this->track->bpm = $bpm;
            $this->track->save();
        } catch (ProcessFailedException $e) {
            logger()->error("Failed to calculate BPM", [
                'message' => $e->getMessage(),
                'output' => $e->getProcess()->getOutput(),
                'errorOutput' => $e->getProcess()->getErrorOutput()
            ]);
        }
    }
}
