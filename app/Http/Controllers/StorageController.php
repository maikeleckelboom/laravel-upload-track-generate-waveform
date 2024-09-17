<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StorageController extends Controller
{
    public function __invoke(Request $request, string $disk, string $path)
    {
        $path = "{$disk}/{$path}";

        if (!file_exists(storage_path("app/{$path}"))) {
            abort(404);
        }

        logger()->info("Serving file: {$path}");

        return response()->file(storage_path("app/{$path}"));
    }
}
