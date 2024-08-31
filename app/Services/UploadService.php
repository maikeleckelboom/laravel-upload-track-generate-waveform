<?php

namespace App\Services;

use App\Data\UploadData;
use App\Enum\UploadStatus;
use App\Exceptions\ChunkCountMismatch;
use App\Models\Upload;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\Support\FileNamer\DefaultFileNamer;

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
                'name' => pathinfo($data->name, PATHINFO_FILENAME),
                'file_name' => $this->getFileName($data->name),
                'mime_type' => $data->type,
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
                'status' => UploadStatus::COMPLETED,
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
            ['disk' => $upload->disk]
        );
    }

    /**
     * @throws ChunkCountMismatch
     */
    private function assembleChunks(Upload $upload): string
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

    private function hasReceivedAllChunks(Upload $upload): bool
    {
        return $upload->received_chunks === $upload->total_chunks;
    }

    private function getFileName(string $name): string
    {
        $nameGenerator = new DefaultFileNamer();
        $fileName = $nameGenerator->originalFileName($name);
        $extension = pathinfo($name, PATHINFO_EXTENSION);
        return "{$fileName}.{$extension}";
    }
}
