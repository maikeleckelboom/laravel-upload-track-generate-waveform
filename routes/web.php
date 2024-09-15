<?php

use App\Http\Controllers\UploadController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TrackController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => [
    'time' => now()->toDateTimeString(),
]);

require __DIR__ . '/auth.php';

Route::group(['middleware' => 'auth:sanctum'], function () {

    Route::get('/user', UserController::class)->name('user');

    Route::resource('upload', UploadController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->names([
            'index' => 'upload.index',
            'store' => 'upload.store',
            'update' => 'upload.update',
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

    // storage
    Route::get('/storage/{path}', function () {

        $path = request()->path;
        $path = storage_path("app/{$path}");
        if (!file_exists($path)) {
            return response()->json(['error' => 'File not found'], 404);
        }
        return response()->file($path);

    })->name('storage');


    Route::get('/track/{track}/waveform', [TrackController::class, 'waveform'])->name('track.waveform');

    Route::post('/track', [TrackController::class, 'store'])->name('track.store');

    Route::get('/media', fn() => response()->json(auth()->user()->media()->get()))->name('media');
});
