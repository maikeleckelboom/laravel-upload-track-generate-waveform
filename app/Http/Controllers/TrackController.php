<?php

namespace App\Http\Controllers;

use App\Data\UploadData;
use App\Exceptions\AudioStreamNotFound;
use App\Exceptions\ChunkCannotBeStored;
use App\Exceptions\ChunkCountMismatch;
use App\Exceptions\ChunksCannotBeAssembled;
use App\Http\Resources\UploadResource;
use App\Jobs\CreateWaveformData;
use App\Jobs\PreprocessAudio;
use App\Models\Track;
use App\Services\UploadService;
use Exception;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileCannotBeAdded;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;
use Symfony\Component\HttpFoundation\Response;

class TrackController extends Controller
{
    public function __construct(
        private readonly UploadService $uploadService
    )
    {
    }

    public function index(Request $request)
    {
        $tracks = $request
            ->user()
            ->tracks()
            ->paginate(12)
            ->sortByDesc('created_at')
            ->values();

        return response()->json($tracks);
    }

    /**
     * @throws FileCannotBeAdded|FileIsTooBig
     * @throws ChunkCannotBeStored|ChunkCountMismatch|ChunksCannotBeAssembled
     * @throws AudioStreamNotFound
     */
    public function store(Request $request)
    {
        $data = UploadData::validateAndCreate($request->all());

        $upload = $this->uploadService->store($user = $request->user(), $data);


        if ($upload->isCompleted()) {

            $track = $user->tracks()->firstOrCreate(['name' => $upload->name]);

            $upload->uploadable()->associate($track);
            $upload->save();

            $track->addMediaFromDisk($upload->file_name, $upload->disk)
                ->withCustomProperties(['original' => true])
                ->toMediaLibrary('audio');

            PreprocessAudio::withChain([
                new CreateWaveformData($track),
            ])->dispatch($track);

        }

        return response()->json(UploadResource::make($upload));
    }

    public function storeMany(Request $request)
    {
        $tracks = $request->user()->tracks()->createMany($request->only(['name']));
        return response()->json($tracks);
    }

    public function playback(Request $request, Track $track)
    {
        $audio = $track->getFirstMedia('audio');
        return $audio->toResponse($request);
    }

    public function create(Request $request)
    {
        $tracks = $request->user()->tracks()->createMany($request->only(['name']));
        return response()->json($tracks);
    }

    public function show(Request $request, Track $track)
    {
        return response()->json($track);
    }

    public function update(Request $request, Track $track)
    {
        $track->update($request->only(['name', 'duration', 'bpm']));

        return response()->json($track);
    }

    public function destroy(Request $request, Track $track)
    {
        try {
            $track->delete();
            return response()->json(['message' => 'Track deleted']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * @throws Exception
     */
    public function waveformStatus(Request $request, Track $track)
    {
        $type = $request->query('type', 'data');

        if (!in_array($type, ['image', 'data'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Type must be either "image" or "data"'
            ], Response::HTTP_BAD_REQUEST);
        }

        $isTypeReady = fn($file) => $file->getCustomProperty('type') === $type;
        $waveform = $track->getFirstMedia('waveform', fn($file) => $isTypeReady($file));

        if (!$waveform?->exists()) {
            return response()->json([
                'status' => 'pending',
                'message' => 'Waveform is being generated'
            ], Response::HTTP_ACCEPTED);
        }

        return response()->json([
            'status' => 'ready',
            'url' => $waveform->getUrl()
        ], Response::HTTP_OK);
    }
}
