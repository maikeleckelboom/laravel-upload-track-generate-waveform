<?php

namespace App\Http\Controllers;

use App\Data\UploadData;
use App\Exceptions\AudioStreamNotFound;
use App\Exceptions\ChunkCannotBeStored;
use App\Exceptions\ChunkCountMismatch;
use App\Exceptions\ChunksCannotBeAssembled;
use App\Http\Resources\UploadResource;
use App\Jobs\CreateAudioWaveformData;
use App\Jobs\CreateAudioWaveformImage;
use App\Jobs\PreprocessAudioFile;
use App\Models\Track;
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

    public function index(Request $request)
    {
        $tracks = $request->user()->tracks()
            ->get()
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

            PreprocessAudioFile::dispatch($track);
            CreateAudioWaveformData::dispatch($track);

            defer(fn() => $upload->delete());
        }

        return response()->json(UploadResource::make($upload));
    }

    public function waveformData(Request $request, Track $track)
    {
        $inBinaryFormat = fn($file) => $file->getCustomProperty('format') === 'dat';
        $waveform = $track->getFirstMedia('waveform', $inBinaryFormat);

        return response()->stream(fn() => $waveform->stream(), 200, [
            'Content-Type' => $waveform->mime_type,
            'Content-Length' => $waveform->size,
        ]);
    }

    public function stream(Request $request, Track $track)
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
        $track->update($request->only(['title', 'description']));

        return response()->json($track);
    }

    public function destroy(Request $request, Track $track)
    {
        $track->delete();

        return response()->noContent();
    }
}
