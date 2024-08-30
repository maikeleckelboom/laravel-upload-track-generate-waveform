<?php

use App\Http\Controllers\CurrentUserController;
use App\Http\Controllers\TrackController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return ['Laravel' => app()->version()];
});

require __DIR__ . '/auth.php';

Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::get('/user', CurrentUserController::class)->name('user');

    Route::post('/upload', [TrackController::class,'store'])->name('track.store');
});
