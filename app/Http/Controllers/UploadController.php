<?php

namespace App\Http\Controllers;

use App\Data\UploadData;
use App\Exceptions\ChunkCannotBeStored;
use App\Exceptions\ChunksCannotBeAssembled;
use App\Http\Resources\UploadResource;
use App\Models\Upload;
use App\Models\User;
use App\Services\UploadService;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;
use Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

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

    public function update(Request $request, string $identifier)
    {
        $upload = $request->user()
            ->tracks()
            ->get()
            ->pluck('uploads')
            ->flatten()
            ->where('identifier', $identifier)
            ->firstOrFail();

        $upload->update([
            'elapsed_time' => $request->get('elapsed'),
            'transfer_speed' => $request->get('speed'),
        ]);

        defer(fn() => $upload->delete());

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
    public function addUploadToCollection(User $user, Upload $upload): Media
    {
        $path = Storage::disk($upload->disk)->path($upload->file_name);
        return $user->addMedia($path)
            ->withCustomProperties(['upload_id' => $upload->id])
            ->toMediaCollection('media');
    }
}
