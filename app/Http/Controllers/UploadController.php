<?php

namespace App\Http\Controllers;

use App\Http\Resources\UploadResource;
use App\Models\Upload;
use Illuminate\Http\Request;

class UploadController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(UploadResource::collection($request->user()->uploads()->get()));
    }

    public function show(Request $request, Upload $upload)
    {
        return response()->json(UploadResource::make($upload));
    }

    public function destroy(Request $request, Upload $upload)
    {
        $upload->delete();
        return response()->json(null, 204);
    }
}
