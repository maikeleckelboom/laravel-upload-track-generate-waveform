<?php

use App\Http\Controllers\UploadController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TrackController;
use App\Http\Resources\UploadResource;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return ['Laravel' => app()->version()];
});

require __DIR__ . '/auth.php';

Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::get('/user', UserController::class)->name('user');

    Route::resource('upload', UploadController::class)->only(['index', 'show', 'destroy'])->parameters(['upload' => 'identifier']);
    Route::post('/upload', [TrackController::class, 'store'])->name('upload.store');


    Route::resource('track', TrackController::class)->only(['index', 'store', 'show', 'update']);
});
