<?php

namespace App\Services;

use App\Data\TemporaryUploadData;
use App\Enum\TemporaryUploadStatus;
use App\Exceptions\ChunkCountMismatch;
use App\Models\TemporaryUpload;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

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
                'type' => $data->type,
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

            logger('Upload completed', [
                'user_id' => $user->id,
                'file_path' => $path,
            ]);

            $upload->refresh();
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
        $chunksDisk = Storage::disk($upload->chunks_disk);

        $chunks = $chunksDisk->files($upload->identifier);

        if (count($chunks) !== $upload->total_chunks) {
            throw new ChunkCountMismatch();
        }

        $destinationPath = $disk->path($upload->name);
        $destinationStream = fopen($destinationPath, 'a');

        foreach ($chunks as $chunk) {
            $chunkStream = $chunksDisk->readStream($chunk);
            stream_copy_to_stream($chunkStream, $destinationStream);
            fclose($chunkStream);
            $chunksDisk->delete($chunk);
        }

        fclose($destinationStream);
        $chunksDisk->deleteDirectory($upload->identifier);

        return $destinationPath;
    }

    private function hasReceivedAllChunks(TemporaryUpload $upload): bool
    {
        return $upload->received_chunks === $upload->total_chunks;
    }
}
