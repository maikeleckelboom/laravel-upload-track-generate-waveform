<?php

return [
    'supported_formats' => env('AUDIOWAVEFORM_SUPPORTED_FORMATS', 'mp3,wav,flac,ogg,vorbis,opus'),
    'conversion_format' => env('AUDIOWAVEFORM_CONVERSION_FORMAT', 'opus'),
];
