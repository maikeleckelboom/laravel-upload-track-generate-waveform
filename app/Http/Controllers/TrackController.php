<?php

namespace App\Http\Controllers;

use App\Data\TemporaryUploadData;
use App\Exceptions\ChunkCountMismatch;
use App\Services\UploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileCannotBeAdded;

class TrackController extends Controller
{
    public function __construct(
        private readonly UploadService $uploadService
    )
    {

    }

    /**
     * @throws ChunkCountMismatch|FileCannotBeAdded
     */
    public function store(Request $request)
    {
        $data = TemporaryUploadData::validateAndCreate($request->all());

        $temporaryUpload = $this->uploadService->store($request->user(), $data);

        if ($temporaryUpload->isCompleted()) {

            logger()->info("{$temporaryUpload->name} has been uploaded with {$temporaryUpload->size} bytes");

            $path =  Storage::disk($temporaryUpload->disk)->path($temporaryUpload->file_name);

            $media = $request->user()
                ->addMedia($path)
                ->toMediaCollection('media');
        }

        return $temporaryUpload;
    }
}
