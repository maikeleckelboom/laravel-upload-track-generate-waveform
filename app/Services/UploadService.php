<?php

namespace App\Services;

use Spatie\MediaLibrary\Support\FileNamer\DefaultFileNamer;
use App\Exceptions\AssembleChunksFailed;
use App\Exceptions\ChunkStorageFailed;
use App\Models\Upload;
use App\Models\User;
use App\Data\UploadData;
use App\Enum\UploadStatus;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Exception;

class UploadService
{
    /**
     * @throws ChunkStorageFailed
     * @throws AssembleChunksFailed
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

        $upload->updateMetrics($data);

        return $upload;
    }

    /**
     * @throws ChunkStorageFailed
     */
    private function addChunk(Upload $upload, UploadedFile $uploadedFile): void
    {
        if (!$this->storeChunk($upload, $uploadedFile)) {
            throw new ChunkStorageFailed();
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
     * @throws AssembleChunksFailed
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
            throw new AssembleChunksFailed($e->getMessage(), $e->getCode());
        } finally {
            fclose($destinationStream);
        }

        $disk->deleteDirectory("chunks/{$upload->identifier}");

        return $destinationPath;
    }

    private function hasReceivedAllChunks(Upload $upload): bool
    {
        return $upload->received_chunks === $upload->total_chunks;
    }
}
