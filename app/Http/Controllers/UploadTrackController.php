<?php

namespace App\Http\Controllers;

use App\Data\TemporaryUploadData;
use App\Enum\TemporaryUploadStatus;
use App\Models\TemporaryUpload;
use Illuminate\Http\Request;

class UploadTrackController extends Controller
{

    public function __invoke(Request $request)
    {
        $data = TemporaryUploadData::validateAndCreate($request->all());

        $temporaryUpload = TemporaryUpload::query()
            ->firstOrCreate(['identifier' => $data->identifier], [
                'chunk_size' => $data->chunkSize,
                'received_chunks' => $data->chunkNumber - 1,
                'status' => TemporaryUploadStatus::PENDING,
            ]);

        $temporaryUpload->update([
            'received_chunks' => $data->chunkNumber,
        ]);

        if ($data->chunkNumber === $data->totalChunks) {
            $temporaryUpload->update([
                'status' => 'completed',
            ]);
        }

        return $temporaryUpload;
    }
}
