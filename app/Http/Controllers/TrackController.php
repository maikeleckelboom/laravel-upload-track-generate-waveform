<?php

namespace App\Http\Controllers;

use App\Data\UploadData;
use App\Exceptions\AssembleChunksFailed;
use App\Exceptions\AudioStreamNotFound;
use App\Exceptions\ChunkCountMismatch;
use App\Exceptions\ChunkStorageFailed;
use App\Http\Resources\UploadResource;
use App\Jobs\CreateWaveformData;
use App\Models\Track;
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
        return response()->json($tracks);
    }

    /**
     * @throws FileCannotBeAdded
     * @throws FileIsTooBig
     * @throws ChunkCountMismatch
     * @throws ChunkStorageFailed
     * @throws AssembleChunksFailed
     * @throws AudioStreamNotFound
     */
    public function store(Request $request)
    {
        $data = UploadData::validateAndCreate($request->all());

        $upload = $this->uploadService->store($user = $request->user(), $data);

        if ($upload->isCompleted()) {

            $track = $user->tracks()->create(['title' => $upload->name]);

            $track->addMediaFromDisk($upload->file_name, $upload->disk)->toMediaLibrary('audio') ;

            $track = $this->audioProcessor->process($track);
 
//            $track->duration = $this->audioProcessor->getDurationInSeconds($track);
//            $track->save();

            CreateWaveformData::dispatch($track);
        }

        return response()->json(UploadResource::make($upload));
    }

    public function show(Request $request, Track $track)
    {
        return response()->json($track);
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
