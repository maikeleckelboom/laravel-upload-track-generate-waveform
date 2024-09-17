<?php

namespace App\Http\Controllers;

use App\Data\UploadData;
use App\Exceptions\ChunksCannotBeAssembled;
use App\Exceptions\ChunkCannotBeStored;
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

    public function update(Request $request, string $identifier)
    {
        $upload = $request->user()->uploads()->where('identifier', $identifier)->firstOrFail();

        $validated = $request->validate([
            'transfer_speed' => 'numeric',
            'elapsed_time' => 'integer'
        ]);

        $upload->update($validated);

        return response()
            ->json(UploadResource::make($upload))
            ->setStatusCode(202);
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
        return response()->noContent();
    }

    /**
     * @throws FileIsTooBig
     * @throws FileDoesNotExist
     */
    public function addUploadToCollection(User $user, Upload $upload): \Spatie\MediaLibrary\MediaCollections\Models\Media
    {
        try {
            return $user->addMedia($upload->path)
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
