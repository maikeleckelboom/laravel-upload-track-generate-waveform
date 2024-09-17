<?php

namespace App\Services;

use App\Data\UploadData;
use App\Enum\UploadStatus;
use App\Exceptions\ChunkCannotBeStored;
use App\Exceptions\ChunksCannotBeAssembled;
use App\Models\Upload;
use App\Models\User;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class UploadService
{
    /**
     * @throws ChunkCannotBeStored
     * @throws ChunksCannotBeAssembled
     */
    public function store(User $user, UploadData $data): Upload
    {
        $upload = $user->uploads()->firstOrCreate(['identifier' => $data->identifier], [
            'file_name' => $data->name,
            'name' => pathinfo($data->name, PATHINFO_FILENAME),
            'mime_type' => $data->type,
            'size' => $data->size,
            'chunk_size' => $data->chunkSize,
            'received_chunks' => $data->chunkNumber - 1,
            'status' => UploadStatus::PENDING,
        ]);

        if ($upload->wasRecentlyCreated) {
            $upload->refresh();
        }

        $this->addChunk($upload, $data->chunkData);

        if ($this->hasReceivedAllChunks($upload)) {
            $upload->path = $this->assembleChunks($upload);
            $upload->status = UploadStatus::COMPLETED;
            $upload->save();
        }

        defer(fn() => $upload->updateMetrics($data));

        return $upload;
    }

    /**
     * @throws ChunkCannotBeStored
     */
    private function addChunk(Upload $upload, UploadedFile $uploadedFile): void
    {
        if (!$this->storeChunk($upload, $uploadedFile)) {
            throw new ChunkCannotBeStored();
        }

        if ($upload->received_chunks < $upload->total_chunks) {
            $upload->increment('received_chunks');
            $upload->save();
        }
    }

    private function storeChunk(Upload $upload, UploadedFile $uploadedFile): bool
    {
        return $uploadedFile->storeAs(
            "chunks/{$upload->identifier}",
            $upload->received_chunks,
            ['disk' => $upload->disk]
        );
    }

    /**
     * @throws ChunksCannotBeAssembled
     */
    private function assembleChunks(Upload $upload): string
    {
        $disk = Storage::disk($upload->disk);
        $chunks = $disk->files("chunks/{$upload->identifier}");

        $destinationPath = $disk->path($upload->file_name);
        $destinationStream = fopen($destinationPath, 'w');

        try {
            foreach ($chunks as $chunk) {
                $chunkStream = $disk->readStream($chunk);
                stream_copy_to_stream($chunkStream, $destinationStream);
                fclose($chunkStream);
                $disk->delete($chunk);
            }
        } catch (Exception $e) {
            throw new ChunksCannotBeAssembled($e->getMessage(), $e->getCode());
        } finally {
            fclose($destinationStream);
        }

        $disk->deleteDirectory("chunks/{$upload->identifier}");

        if (config('app.env') === 'local' && count($disk->files("chunks")) === 0) {
            $disk->deleteDirectory("chunks");
        }

        return $destinationPath;
    }

    private function hasReceivedAllChunks(Upload $upload): bool
    {
        return $upload->received_chunks === $upload->total_chunks;
    }
}
