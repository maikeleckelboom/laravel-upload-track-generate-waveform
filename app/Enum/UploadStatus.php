<?php

namespace App\Enum;

enum UploadStatus: string
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case COMPLETED = 'completed';
    case FAILED = 'failed';

    public static function toArray(): array
    {
        return array_column(UploadStatus::cases(), 'value');
    }
}
