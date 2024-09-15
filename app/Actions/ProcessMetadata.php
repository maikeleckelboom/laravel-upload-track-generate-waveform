<?php

namespace App\Actions;

class ProcessMetadata
{

    public function __construct(
        private readonly string $path
    )
    {
    }

    public function execute(): void
    {
        logger('Processing metadata for ' . $this->path);
    }

}
