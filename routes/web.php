<?php

use App\Http\Controllers\StorageController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TrackController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => [
    'now()' => now()->toDateTimeString(),
]);

require __DIR__ . '/auth.php';

Route::get('/storage/{disk}/{path}', StorageController::class)
    ->where('path', '.*')
    ->name('storage');

// Route::get('/track/{track}/playback', [TrackController::class, 'playback']);

Route::group(['middleware' => 'auth:sanctum'], function () {

    Route::get('/user', UserController::class)->name('user');

    Route::resource('upload', UploadController::class)
        ->only(['index', 'store', 'update', 'destroy']);

    Route::resource('track', TrackController::class)
        ->only(['index', 'store', 'show', 'update', 'destroy']);

    Route::get('/track/{track}/waveform', [TrackController::class, 'waveform']);




    // Initialize all files at once at the beginning of the upload,
    // this way when an unexpected close happens, the track will still be visible but appear 'detached' in UI.
    // The user can then re-attach the files to the models to continue the upload.
    // Later on, when the upload is completed, we can attach the files to the model.
//    Route::post('/track/create', [TrackController::class, 'create'])->name('track.create');


    Route::get('/media', fn() => response()->json(auth()->user()->media()->get()))->name('media');
});

