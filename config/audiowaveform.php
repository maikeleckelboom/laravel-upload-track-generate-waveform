<?php

return [
    /**
     * Supported audio formats as input filename for the audiowaveform library.
     */
    'supported_formats' => env('AUDIOWAVEFORM_SUPPORTED_FORMATS', 'mp3,wav,flac,ogg,vorbis,opus'),
    /**
     * The format of the audio file to be used for playback.
     */
    'conversion_format' => env('AUDIOWAVEFORM_CONVERSION_FORMAT', 'opus'),
    /**
     * The dimensions of the waveform image for different devices.
     */
    'waveform_dimensions' => [
        '4K' => [
            'device' => 'Desktop / TV',
            'width' => env('AUDIOWAVEFORM_4K_WIDTH', 3840),
            'height' => env('AUDIOWAVEFORM_4K_HEIGHT', 400),
        ],
        '2K' => [
            'device' => 'Desktop / High-Res Laptop',
            'width' => env('AUDIOWAVEFORM_2K_WIDTH', 2560),
            'height' => env('AUDIOWAVEFORM_2K_HEIGHT', 350),
        ],
        'FullHD' => [
            'device' => '1080p Desktop / Laptop',
            'width' => env('AUDIOWAVEFORM_FULL_HD_WIDTH', 1920),
            'height' => env('AUDIOWAVEFORM_FUL_LHD_HEIGHT', 300),
        ],
        'HDReady' => [
            'device' => '720p Desktop / Laptop / Tablets',
            'width' => env('AUDIOWAVEFORM_HD_READY_WIDTH', 1280),
            'height' => env('AUDIOWAVEFORM_HD_READY_HEIGHT', 250),
        ],
        'LargeTablet' => [
            'device' => 'Landscape Mode',
            'width' => env('AUDIOWAVEFORM_LARGE_TABLET_WIDTH', 1024),
            'height' => env('AUDIOWAVEFORM_LARGE_TABLET_HEIGHT', 220),
        ],
        'MediumTablet' => [
            'device' => 'Portrait Mode',
            'width' => env('AUDIOWAVEFORM_MEDIUM_TABLET_WIDTH', 768),
            'height' => env('AUDIOWAVEFORM_MEDIUM_TABLET_HEIGHT', 200),
        ],
        'LargeMobile' => [
            'device' => 'Landscape Mode',
            'width' => env('AUDIOWAVEFORM_LARGE_MOBILE_WIDTH', 640),
            'height' => env('AUDIOWAVEFORM_LARGE_MOBILE_HEIGHT', 180),
        ],
        'SmallMobile' => [
            'device' => 'Portrait Mode',
            'width' => env('AUDIOWAVEFORM_SMALL_MOBILE_WIDTH', 360),
            'height' => env('AUDIOWAVEFORM_SMALL_MOBILE_HEIGHT', 150),
        ],
    ],
];
