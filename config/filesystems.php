<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application for file storage.
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Below you may configure as many filesystem disks as necessary, and you
    | may even configure multiple disks for the same driver. Examples for
    | most supported storage drivers are configured here for reference.
    |
    | Supported drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'original' => [
            'driver' => 'local',
            'root' => storage_path('app/private/original'),
            'url' => env('APP_URL') . '/storage/private/original',
            'visibility' => 'private',
            'throw' => false,
        ],

        'conversion' => [
            'driver' => 'local',
            'root' => storage_path('app/private/conversion'),
            'url' => env('APP_URL') . '/storage/private/conversion',
            'visibility' => 'private',
            'throw' => false,
        ],

        'waveform' => [
            'driver' => 'local',
            'root' => storage_path('app/private/waveform'),
            'url' => env('APP_URL') . '/storage/private/waveform',
            'visibility' => 'private',
            'throw' => false,
        ],

        'waveform_data' => [
            'driver' => 'local',
            'root' => storage_path('app/private/waveform_data'),
            'url' => env('APP_URL') . '/storage/private/waveform_data',
            'visibility' => 'private',
            'throw' => false,
        ],

        'waveform_image' => [
            'driver' => 'local',
            'root' => storage_path('app/private/waveform_image'),
            'url' => env('APP_URL') . '/storage/private/waveform_image',
            'visibility' => 'private',
            'throw' => false,
        ],


        'tracks' => [
            'driver' => 'local',
            'root' => storage_path('app/tracks'),
            'url' => env('APP_URL') . '/storage/tracks',
            'visibility' => 'private',
            'throw' => false,
        ],

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
            'url' => env('APP_URL') . '/storage',
            'visibility' => 'private',
            'throw' => false,
        ],

        'local-temporary' => [
            'driver' => 'local',
            'root' => storage_path('app/temporary'),
            'url' => env('APP_URL') . '/storage/temporary',
            'visibility' => 'private',
            'throw' => false,
        ],

        'local-test-cases' => [
            'driver' => 'local',
            'root' => storage_path('test_cases/app'),
            'url' => env('APP_URL') . '/storage/test_cases/app',
            'visibility' => 'private',
            'throw' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL') . '/storage',
            'visibility' => 'public',
            'throw' => false,
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
