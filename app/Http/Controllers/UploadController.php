<?php

namespace App\Http\Controllers;

use App\Data\UploadData;
use App\Exceptions\ChunkCountMismatch;
use App\Http\Resources\UploadResource;
use App\Models\Media;
use App\Models\Upload;
use App\Models\User;
use App\Services\UploadService;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;

class UploadController extends Controller
{
    public function __construct(
        private readonly UploadService $uploadService
    )
    {
    }

    public function index(Request $request)
    {
        return response()->json(UploadResource::collection($request->user()->uploads()->get()));
    }

    /**
     * @throws ChunkCountMismatch|FileDoesNotExist|FileIsTooBig
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $upload = $this->uploadService->store(
            $user,
            UploadData::validateAndCreate($request->all())
        );

        $media = $upload->isCompleted() ?: $this->saveUploadAsMedia($user, $upload);

        return response()->json(UploadResource::make($upload, $media));
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

    /**
     * @throws FileDoesNotExist|FileIsTooBig
     */
    public function saveUploadAsMedia(User $user, Upload $upload): \Spatie\MediaLibrary\MediaCollections\Models\Media
    {
        return $user->addMedia($upload->path)->toMediaCollection('uploads');
    }
}
