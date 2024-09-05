<?php

namespace App\Http\Controllers;

use App\Data\UploadData;
use App\Exceptions\ChunkCountMismatch;
use App\Exceptions\ChunkStorageFailed;
use App\Http\Resources\UploadResource;
use App\Models\Track;
use App\Models\Upload;
use App\Services\AudioProcessor;
use App\Services\UploadService;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileCannotBeAdded;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;

class TrackController extends Controller
{
    public function __construct(
        private readonly UploadService  $uploadService,
        private readonly AudioProcessor $audioProcessor
    )
    {
    }

    public function index(Request $request)
    {
        $tracks = $request->user()->tracks()->get();
        return response()->json($tracks->load('audioMetadata'));
    }

    /**
     * @throws FileCannotBeAdded
     * @throws FileIsTooBig
     * @throws ChunkCountMismatch
     * @throws ChunkStorageFailed
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $upload = $this->uploadService->store(
            $user,
            UploadData::validateAndCreate($request->all())
        );

        if ($upload->isCompleted()) {
            $track = $user->tracks()->create(['title' => $upload->name]);
            $metadata = $this->audioProcessor->processMetadata($upload);
            $track->audioMetadata()->create($metadata);
        }

        return response()->json(UploadResource::make($upload));
    }

    public function show(Request $request, Track $track)
    {
        return response()->json($track->load('audioMetadata'));
    }

    public function update(Request $request, Track $track)
    {
        $track->update($request->only(['title', 'description']));
        return response()->json($track);
    }

    public function destroy(Request $request, Track $track)
    {
        $track->delete();
        return response()->json(['message' => 'Track deleted']);
    }
}
