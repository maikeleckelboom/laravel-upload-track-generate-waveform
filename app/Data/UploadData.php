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
        public string       $fileName,
        public string       $mimeType,
        #[GreaterThan(0)]
        public int          $fileSize,
        #[GreaterThan(0)]
        public int          $totalChunks,
        #[GreaterThan(0), LessThanOrEqualTo('totalChunks')]
        public int          $chunkNumber,
        #[GreaterThanOrEqualTo(1024 * 1024)]
        public int          $chunkSize,
        public UploadedFile $chunkData,
    )
    {
    }
}
