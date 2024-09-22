<?php

namespace App\Http\Controllers;

use App\Data\UploadData;
use App\Exceptions\ChunkCannotBeStored;
use App\Exceptions\ChunksCannotBeAssembled;
use App\Http\Resources\UploadResource;
use App\Models\Media;
use App\Models\Upload;
use App\Models\User;
use App\Services\UploadService;
use Exception;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;
use Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection;
use Storage;

class UploadController extends Controller
{
    public function __construct(
        private readonly UploadService $uploadService
    )
    {
    }

    public function index(Request $request)
    {
        $tracks = $request->user()->tracks();
        $uploads = $tracks->get()->pluck('uploads')->flatten();
        return response()->json(UploadResource::collection($uploads));
    }

    /**
     * @throws FileIsTooBig
     * @throws FileDoesNotExist
     * @throws ChunkCannotBeStored
     * @throws ChunksCannotBeAssembled
     */
    public function store(Request $request)
    {
        $user = $request->user();
        $data = UploadData::validateAndCreate($request->all());

        $upload = $this->uploadService->store($user, $data);
        $upload->updateMetrics($data);

        if ($upload->isCompleted()) {
            $this->addUploadToCollection($user, $upload);
        }

        return response()->json(UploadResource::make($upload));
    }

    public function show(Request $request, string $identifier)
    {
        $upload = $request->user()->uploads()->where('identifier', $identifier)->firstOrFail();
        return response()->json(UploadResource::make($upload));
    }

    public function destroy(Request $request, string $identifier)
    {
        $upload = $request->user()
            ->tracks()
            ->get()
            ->pluck('uploads')
            ->flatten()
            ->where('identifier', $identifier)
            ->firstOrFail();

        $upload->delete();

        return response()->noContent();
    }

    /**
     * @throws FileIsTooBig
     * @throws FileDoesNotExist
     */
    public function addUploadToCollection(User $user, Upload $upload): \Spatie\MediaLibrary\MediaCollections\Models\Media
    {
        try {
            $path = Storage::disk($upload->disk)->path($upload->file_name);
            return $user->addMedia($path)
                ->withCustomProperties(['upload_id' => $upload->id])
                ->toMediaCollection('media');

        } catch (Exception $e) {
            if ($e instanceof FileDoesNotExist) {
                $resource = $this->tryFindMediaResource($user, $upload);

                if ($resource->isEmpty()) {
                    throw $e;
                }

                return $resource->first();
            }

            throw $e;
        }
    }

    private function tryFindMediaResource(User $user, Upload $upload): MediaCollection
    {
        return Media::where('custom_properties->upload_id', $upload->id)
            ->where('disk', $upload->disk)
            ->orWhere('file_name', $upload->file_name)
            ->get();
    }
}
