<?php

namespace App\Enum;

enum PlaybackStatus: string
{
    case PLAYING = 'playing';
    case PAUSED = 'paused';
    case STOPPED = 'stopped';
    case BUFFERING = 'buffering';
    case ERROR = 'error';
    case IDLE = 'idle';

    public static function toArray(): array
    {
        return array_column(PlaybackStatus::cases(), 'value');
    }
}
