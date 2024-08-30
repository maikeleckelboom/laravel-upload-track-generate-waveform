<?php

namespace App\Services;

use App\Data\TemporaryUploadData;
use App\Enum\TemporaryUploadStatus;
use App\Exceptions\ChunkCountMismatch;
use App\Models\TemporaryUpload;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileCannotBeAdded;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;

class UploadService
{

    /**
     * @throws ChunkCountMismatch
     */
    public function store(User $user, TemporaryUploadData $data): TemporaryUpload
    {
        $upload = $user
            ->temporaryUploads()
            ->firstOrCreate(['identifier' => $data->identifier], [
                'name' => $data->name,
                'file_name' => md5($data->name) . '.' . pathinfo($data->name, PATHINFO_EXTENSION),
                'mime_type' => $data->type,
                'size' => $data->size,
                'chunk_size' => $data->chunkSize,
                'received_chunks' => $data->chunkNumber - 1,
                'status' => TemporaryUploadStatus::PENDING,
            ])
            ->refresh();

        $this->addChunk($upload, $data->chunkData);

        if ($this->hasReceivedAllChunks($upload)) {

            $path = $this->assembleChunks($upload);

            $upload->status = TemporaryUploadStatus::COMPLETED;
            $upload->save();
        }

        return $upload;
    }

    private function addChunk(TemporaryUpload $upload, UploadedFile $uploadedFile): void
    {
        if ($this->storeChunk($upload, $uploadedFile)) {
            $upload->increment('received_chunks');
            $upload->save();
        }
    }

    private function storeChunk(TemporaryUpload $upload, UploadedFile $uploadedFile): bool
    {
        return $uploadedFile->storeAs(
            $upload->identifier,
            $upload->received_chunks,
            ['disk' => $upload->disk]
        );
    }

    /**
     * @throws ChunkCountMismatch
     */
    private function assembleChunks(TemporaryUpload $upload): string
    {
        $disk = Storage::disk($upload->disk);

        $chunks = $disk->files($upload->identifier);

        if (count($chunks) !== $upload->total_chunks) {
            throw new ChunkCountMismatch();
        }

        $destinationPath = $disk->path($upload->file_name);
        $destinationStream = fopen($destinationPath, 'a');

        foreach ($chunks as $chunk) {
            $chunkStream = $disk->readStream($chunk);
            stream_copy_to_stream($chunkStream, $destinationStream);
            fclose($chunkStream);
            $disk->delete($chunk);
        }

        fclose($destinationStream);
        $disk->deleteDirectory($upload->identifier);

        return $destinationPath;
    }

    private function hasReceivedAllChunks(TemporaryUpload $upload): bool
    {
        return $upload->received_chunks === $upload->total_chunks;
    }
}
