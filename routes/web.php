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

Route::group(['middleware' => 'auth:sanctum'], function () {

    Route::get('/user', UserController::class)->name('user');

    Route::resource('upload', UploadController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->names([
            'index' => 'upload.index',
            'store' => 'upload.store',
            'destroy' => 'upload.destroy',
        ]);

    Route::resource('track', TrackController::class)
        ->only(['index', 'store', 'show', 'update'])
        ->names([
            'index' => 'tracks.index',
            'store' => 'tracks.store',
            'show' => 'tracks.show',
            'update' => 'tracks.update',
        ]);

    Route::get('/track/{track}/waveform', [TrackController::class, 'waveformData'])->name('track.waveform-data');


    // Initialize all files at once at the beginning of the upload,
    // this way when an unexpected close happens, the track will still be visible but appear 'detached' in UI.
    // The user can then re-attach the files to the models to continue the upload.
    // Later on, when the upload is completed, we can attach the files to the model.
//    Route::post('/track/create', [TrackController::class, 'create'])->name('track.create');
//    Route::get('/track/{track}/waveform-data', [TrackController::class, 'waveformData'])->name('track.waveform-data');
//    Route::get('/track/{track}/waveform-image', [TrackController::class, 'waveformImage'])->name('track.waveform-image');


    Route::get('/media', fn() => response()->json(auth()->user()->media()->get()))->name('media');
});


Route::get('/storage/{disk}/{path}', StorageController::class)
    ->where('path', '.*')
    ->name('storage');

Route::get('/track/{track}/stream', [TrackController::class, 'stream'])->name('track.stream');

