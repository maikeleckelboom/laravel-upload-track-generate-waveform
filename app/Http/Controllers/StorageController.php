<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StorageController extends Controller
{
    public function __invoke(Request $request, string $disk, string $path)
    {
        $storagePath = storage_path("app/{$disk}/{$path}");

        if (!file_exists($storagePath)) {
            abort(404, 'The requested file was not found.');
        }

        return response()->file($storagePath);
    }
}
