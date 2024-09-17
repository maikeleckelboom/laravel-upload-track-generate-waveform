<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StorageController extends Controller
{
    public function __invoke(Request $request, string $disk, string $path)
    {
        return response()->file(storage_path("app/{$disk}/{$path}"));
    }
}
