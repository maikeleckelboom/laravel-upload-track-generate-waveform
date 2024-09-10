<?php

namespace App\Data;

use Illuminate\Http\Request;
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
        public ?int         $elapsedTime,
        public ?int         $remainingTime,
        public ?int         $uploadSpeed,
    )
    {
        $this->assignMetrics();
    }

    private function assignMetrics(): void
    {
        [$this->elapsedTime, $this->remainingTime, $this->uploadSpeed] = [
            $this->elapsedTime ?? (int)request()->header('X-Elapsed-Time', 0),
            $this->remainingTime ?? (int)request()->header('X-Remaining-Time', 0),
            $this->uploadSpeed ?? (int)request()->header('X-Upload-Speed', 0),
        ];
    }
}
