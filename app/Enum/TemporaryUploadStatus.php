<?php

namespace App\Enum;

enum TemporaryUploadStatus: string
{
    case QUEUED = 'queued';
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case COMPLETED = 'completed';
    case FAILED = 'failed';

    public static function toArray(): array
    {
        return array_column(TemporaryUploadStatus::cases(), 'value');
    }
}
