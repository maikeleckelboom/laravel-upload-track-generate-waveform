<?php

namespace App\Http\Controllers;

use App\Data\TemporaryUploadData;
use App\Exceptions\ChunkCountMismatch;
use App\Services\UploadService;
use Illuminate\Http\Request;

class TrackController extends Controller
{
    public function __construct(
        private readonly UploadService $uploadService
    )
    {

    }

    /**
     * @throws ChunkCountMismatch
     */
    public function store(Request $request)
    {
        $data = TemporaryUploadData::validateAndCreate($request->all());

        $temporaryUpload = $this->uploadService->store($request->user(), $data);

        if($temporaryUpload->isCompleted()){
            return response()->json([
                'message' => 'Upload completed',
            ]);
        }

        return $temporaryUpload;
    }
}
