<?php

namespace App\Http\Controllers;

use App\Data\UploadData;
use App\Exceptions\AssembleChunksFailed;
use App\Exceptions\ChunkStorageFailed;
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
     * @throws FileIsTooBig
     * @throws FileDoesNotExist
     * @throws ChunkStorageFailed
     * @throws AssembleChunksFailed
     */
    public function store(Request $request)
    {
        $user = $request->user();
        $data = UploadData::validateAndCreate($request->all());

        $upload = $this->uploadService->store($user, $data);

        $upload->updateMetrics($upload, $data);

        if ($upload->isCompleted()) {
            $this->addUploadToCollection($user, $upload);
            $upload->update([
                'remaining_time' => 0,
            ]);
//            $upload->delete();
        }

        return response()
            ->json(UploadResource::make($upload))
            ->setStatusCode($upload->isCompleted() ? 201 : 202);
    }

    public function show(Request $request, string $identifier)
    {
        $upload = $request->user()->uploads()->where('identifier', $identifier)->firstOrFail();
        return response()->json(UploadResource::make($upload));
    }

    public function destroy(Request $request, string $identifier)
    {
        $upload = $request->user()->uploads()->where('identifier', $identifier)->firstOrFail();
        $upload->delete();
        return response()->json(null, 204);
    }

    /**
     * @throws FileIsTooBig
     * @throws FileDoesNotExist
     */
    public function addUploadToCollection(User $user, Upload $upload): \Spatie\MediaLibrary\MediaCollections\Models\Media
    {
        try {
            return $user->addMedia($upload->path)
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
