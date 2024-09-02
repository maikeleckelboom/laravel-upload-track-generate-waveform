<?php

use App\Http\Controllers\UploadController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TrackController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => [
    'message' => 'Welcome to the API',
    'time' => now()->toDateTimeString(),
]);

require __DIR__ . '/auth.php';

Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::get('/user', UserController::class)
        ->name('user');

    Route::resource('upload', UploadController::class)
        ->except(['create', 'edit'])
        ->parameters(['upload' => 'identifier'])
        ->names([
            'index' => 'upload.index',
            'store' => 'upload.store',
            'show' => 'upload.show',
            'destroy' => 'upload.destroy',
        ]);

    Route::resource('track', TrackController::class)
        ->except(['create', 'edit'])
        ->parameters(['track' => 'id'])
        ->names([
            'index' => 'track.index',
            'store' => 'track.store',
            'show' => 'track.show',
            'update' => 'track.update',
        ]);
});
