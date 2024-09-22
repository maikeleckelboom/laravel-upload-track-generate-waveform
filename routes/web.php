<?php

use App\Http\Controllers\StorageController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TrackController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => [
    'name' => 'Audio API',
    'version' => '0.0.0',
]);

require __DIR__ . '/auth.php';

Route::get('/storage/{disk}/{path}', StorageController::class)
    ->where('path', '.*')
    ->name('storage');

 Route::get('/track/{track}/playback', [TrackController::class, 'playback']);

Route::group(['middleware' => 'auth:sanctum'], function () {

    Route::get('/user', UserController::class)->name('user');

    Route::resource('upload', UploadController::class)
        ->only(['index', 'store', 'update', 'destroy']);

    Route::resource('track', TrackController::class)
        ->only(['index', 'store', 'show', 'update', 'destroy']);

    Route::get('/track/{track}/waveform/status', [TrackController::class, 'waveformStatus']);

    Route::get('/media', fn() => response()->json(auth()->user()->media()->get()))->name('media');
});

