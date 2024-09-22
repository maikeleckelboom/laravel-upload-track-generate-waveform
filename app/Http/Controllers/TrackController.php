<?php

namespace App\Http\Controllers;

use App\Data\UploadData;
use App\Exceptions\AudioStreamNotFound;
use App\Exceptions\ChunkCannotBeStored;
use App\Exceptions\ChunkCountMismatch;
use App\Exceptions\ChunksCannotBeAssembled;
use App\Http\Resources\UploadResource;
use App\Jobs\AnalyzeAudioTempo;
use App\Jobs\CreateWaveformData;
use App\Jobs\CreateWaveformImage;
use App\Jobs\CreateWaveformSequence;
use App\Jobs\PreprocessAudio;
use App\Models\Track;
use App\Services\UploadService;
use Exception;
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

    public function index(Request $request)
    {
        $tracks = $request->user()->tracks()
            ->paginate(10)
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

            $track = $user->tracks()->create(['name' => $upload->name]);

            $track->addMediaFromDisk($upload->file_name, $upload->disk)
                ->withCustomProperties(['original' => true])
                ->toMediaLibrary('audio');

            PreprocessAudio::withChain([
                new CreateWaveformData($track),
                new CreateWaveformImage($track),
                new AnalyzeAudioTempo($track),
                new CreateWaveformSequence($track),
            ])->dispatch($track);

            defer(fn() => $upload->delete());
        }

        return response()->json(UploadResource::make($upload));
    }

    public function playback(Request $request, Track $track)
    {
        $audio = $track->getFirstMedia('audio');
        return response()->stream(fn() => $audio->stream(), 200, [
            'Content-Type' => $audio->mime_type,
            'Content-Length' => $audio->size,
        ]);
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




//    public function waveform(Request $request, Track $track)
//    {
//        $format = $request->query('format', config('audio_waveform.waveform_data_format'));
//
//        $matchDataFormat = fn($file) => $file->getCustomProperty('format') === $format;
//        $waveform = $track->getFirstMedia('waveform', $matchDataFormat);
//
//        return response()->stream(fn() => $waveform->stream(), 200, [
//            'Content-Type' => $waveform->mime_type,
//            'Content-Length' => $waveform->size,
//        ]);
//    }

    /**
     * @throws Exception
     */
    public function waveformStatus(Request $request, Track $track)
    {
        $types = ['image', 'data'];
        $type = $request->query('type', 'data');

        // TODO: Improve all of below here.

        if (!in_array($type, $types)) {
            return response()->json(['error' => 'Invalid type'], 400);
        }


        $isTypeReady = fn($file) => $file->getCustomProperty('type') === $type;
        $isWaveform = fn($file) => $file->getCustomProperty('waveform');
        $waveform = $track->getFirstMedia('waveform', fn($file) => $isWaveform($file) && $isTypeReady($file));

        if (!$waveform?->exists()) {
            throw new Exception('No waveform found');
        }

        return response()->file($waveform->getPath());
    }
}
