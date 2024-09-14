<?php

namespace App\Data;

use Illuminate\Http\UploadedFile;
use Spatie\LaravelData\Attributes\Validation\File;
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
        #[File]
        public UploadedFile $chunkData,
        public ?float       $uploadSpeed,
        public ?int         $elapsedTime
    )
    {
        foreach ($this->extractMetrics() as $key => $value) {
            $this->$key ??= $value;
        }
    }

    private function extractMetrics(): array
    {
        return [
            'uploadSpeed' => (float)request()->header('X-Upload-Speed', 0),
            'elapsedTime' => (int)request()->header('X-Elapsed-Time', 0)
        ];
    }
}
