<?php

namespace App\Http\Controllers;

use App\Data\UploadData;
use App\Exceptions\ChunkCountMismatch;
use App\Services\UploadService;
use Illuminate\Http\File;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileCannotBeAdded;

class TrackController extends Controller
{
    public function __construct(
        private readonly UploadService $uploadService
    )
    {

    }

    /**
     * @throws ChunkCountMismatch|FileCannotBeAdded
     */
    public function store(Request $request)
    {
        $user = $request->user();
        $data = UploadData::validateAndCreate($request->all());

        $upload = $this->uploadService->store($user, $data);

        if ($upload->isCompleted()) {

            logger()->info("{$upload->name} has been completed");

            $track = $user->tracks()->create([
                'title' => 'Untitled',
                'artist' => 'Unknown',
            ]);

            $track->addMedia($upload->path)->toMediaCollection('tracks');

        }

        return $upload;
    }
}
