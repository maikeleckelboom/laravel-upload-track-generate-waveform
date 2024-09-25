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
     * The format for the artwork image.
     */
    'artwork_format' => env('AUDIO_ARTWORK_FORMAT', 'png'),
];
