<?php

namespace App\Http\Controllers;

use App\Data\UploadData;
use App\Exceptions\ChunkCountMismatch;
use App\Services\UploadService;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileCannotBeAdded;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;

class TrackController extends Controller
{
    public function __construct(
        private readonly UploadService $uploadService
    )
    {
    }

    /**
     * @throws ChunkCountMismatch|FileCannotBeAdded|FileIsTooBig
     */
    public function store(Request $request)
    {
        $upload = $this->uploadService->store(
            $request->user(),
            UploadData::validateAndCreate($request->all())
        );

        if ($upload->isCompleted()) {
            $request->user()
                ->addMedia($upload->path)
                ->usingName($upload->name)
                ->usingFileName($upload->file_name)
                ->toMediaCollection('tracks');
        }

        return $upload;
    }
}
