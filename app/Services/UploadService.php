<?php

namespace App\Services;

use App\Exceptions\ChunkCountMismatch;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class UploadService
{

    /**
     * @throws ChunkCountMismatch
     */
    public function store(User $user, UploadData $data): Upload
    {
        $upload = $user
            ->uploads()
            ->firstOrCreate(['identifier' => $data->identifier], [
                'file_name' => $data->fileName,
                'mime_type' => $data->mimeType,
                'size' => $data->size,
                'chunk_size' => $data->chunkSize,
                'received_chunks' => $data->chunkNumber - 1,
                'status' => UploadStatus::PENDING,
            ])
            ->refresh();

        $this->addChunk($upload, $data->chunkData);

        if ($this->hasReceivedAllChunks($upload)) {

            $upload->update([
                'path' => $this->assembleChunks($upload),
                'status' => UploadStatus::COMPLETED
            ]);

            $upload->refresh();
        }

        return $upload;
    }

    private function addChunk(Upload $upload, UploadedFile $uploadedFile): void
    {
        if ($this->storeChunk($upload, $uploadedFile)) {
            $upload->increment('received_chunks');
            $upload->save();
        }
    }

    private function storeChunk(Upload $upload, UploadedFile $uploadedFile): bool
    {
        return $uploadedFile->storeAs(
            $upload->identifier,
            $upload->received_chunks,
            ['disk' => $upload->chunks_disk]
        );
    }

    /**
     * @throws ChunkCountMismatch
     */
    private function assembleChunks(Upload $upload): string
    {
        $disk = Storage::disk($upload->disk);
        $chunksDisk = Storage::disk($upload->chunks_disk);

        $chunks = $chunksDisk->files($upload->identifier);

        if (count($chunks) !== $upload->total_chunks) {
            throw new ChunkCountMismatch();
        }

        $destinationPath = $disk->path($upload->file_name);
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

    private function hasReceivedAllChunks(Upload $upload): bool
    {
        return $upload->received_chunks === $upload->total_chunks;
    }
}
