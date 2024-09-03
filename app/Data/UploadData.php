<?php

namespace App\Data;

use Illuminate\Http\UploadedFile;
use Spatie\LaravelData\Attributes\Validation\GreaterThan;
use Spatie\LaravelData\Attributes\Validation\GreaterThanOrEqualTo;
use Spatie\LaravelData\Attributes\Validation\LessThanOrEqualTo;
use Spatie\LaravelData\Data;

class UploadData extends Data
{
    public function __construct(
        public string       $identifier,
        public string       $name,
        public string       $type,
        #[GreaterThan(1)]
        public int          $size,
        #[GreaterThanOrEqualTo(1)]
        public int          $totalChunks,
        #[GreaterThanOrEqualTo(1), LessThanOrEqualTo('totalChunks')]
        public int          $chunkNumber,
        #[GreaterThanOrEqualTo(1024 * 1024)]
        public int          $chunkSize,
        public UploadedFile $chunkData,
        public ?int         $elapsedMilliseconds = 0,
    )
    {
    }
}
