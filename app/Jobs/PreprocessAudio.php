<?php

namespace App\Jobs;

use App\Models\Track;
use App\Services\AudioProcessor;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class PreprocessAudio implements ShouldQueue
{
    use Queueable;

    private readonly AudioProcessor $audioProcessor;

    /**
     * Create a new job instance.
     */
    public function __construct(private readonly Track $track)
    {
        $this->audioProcessor = new AudioProcessor();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->audioProcessor->process($this->track);
    }
}
