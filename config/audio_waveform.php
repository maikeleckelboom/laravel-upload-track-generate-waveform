<?php

return [
    /**
     * Formats that are supported by audiowaveform.
     */
    'formats' => env('AUDIO_WAVEFORM_FORMATS', 'mp3,wav,flac,ogg,vorbis,opus'),
    /**
     * The format to convert the audio file to for playback.
     */
    'playback_format' => env('AUDIO_PLAYBACK_FORMAT', 'opus'),
    /**
     * The format to convert the audio file to for waveform generation.
     * Supported formats are '.dat' (default) and '.json'.
     */
    'waveform_data_format' => env('AUDIO_WAVEFORM_DATA_FORMAT', 'dat'),
    /**
     * The format to convert the waveform data to for image generation.
     */
    'waveform_image_format' => env('AUDIO_WAVEFORM_IMAGE_FORMAT', 'png'),
    /**
     * The dimensions for each waveform image conversion.
     */
    'waveform_dimensions' => [
        '4K' => [
            'device' => 'Desktop / TV',
            'width' => env('AUDIO_WAVEFORM_4K_WIDTH', 3840),
            'height' => env('AUDIO_WAVEFORM_4K_HEIGHT', 250),
        ],
        '2K' => [
            'device' => 'Desktop / High-Res Laptop',
            'width' => env('AUDIO_WAVEFORM_2K_WIDTH', 2560),
            'height' => env('AUDIO_WAVEFORM_2K_HEIGHT', 200),
        ],
        'Desktop' => [
            'device' => '1080p Desktop / Laptop',
            'width' => env('AUDIO_WAVEFORM_DESKTOP_WIDTH', 1920),
            'height' => env('AUDIO_WAVEFORM_DESKTOP_HEIGHT', 200),
        ],
        'Laptop' => [
            'device' => '720p Desktop / Laptop / Tablets',
            'width' => env('AUDIO_WAVEFORM_LAPTOP_WIDTH', 1280),
            'height' => env('AUDIO_WAVEFORM_LAPTOP_HEIGHT', 200),
        ],
        'LargeTablet' => [
            'device' => 'Landscape Mode',
            'width' => env('AUDIO_WAVEFORM_LARGE_TABLET_WIDTH', 1024),
            'height' => env('AUDIO_WAVEFORM_LARGE_TABLET_HEIGHT', 150),
        ],
        'MediumTablet' => [
            'device' => 'Portrait Mode',
            'width' => env('AUDIO_WAVEFORM_MEDIUM_TABLET_WIDTH', 768),
            'height' => env('AUDIO_WAVEFORM_MEDIUM_TABLET_HEIGHT', 150),
        ],
        'LargeMobile' => [
            'device' => 'Landscape Mode',
            'width' => env('AUDIO_WAVEFORM_LARGE_MOBILE_WIDTH', 640),
            'height' => env('AUDIO_WAVEFORM_LARGE_MOBILE_HEIGHT', 150),
        ],
        'SmallMobile' => [
            'device' => 'Portrait Mode',
            'width' => env('AUDIO_WAVEFORM_SMALL_MOBILE_WIDTH', 360),
            'height' => env('AUDIO_WAVEFORM_SMALL_MOBILE_HEIGHT', 100),
        ],
    ],
];
