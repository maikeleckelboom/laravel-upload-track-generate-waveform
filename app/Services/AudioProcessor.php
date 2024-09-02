<?php

namespace App\Services;


use App\Models\Upload;
use FFMpeg\FFProbe\DataMapping\Stream;
use ProtoneMedia\LaravelFFMpeg\MediaOpener;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

class AudioProcessor
{
    public function processMetadata(Upload $upload): array
    {
        $audio = FFMpeg::fromDisk($upload->disk)->open($upload->file_name);
        $stream = $this->getAudioStream($audio);

        if (!$stream) {
            logger()->error("No audio stream found in file '{$upload->file_name}' on disk '{$upload->disk}'");
            return [];
        }

        $metadata = $this->extractAudioMetadata($stream);

        logger(
            "Audio file '{$upload->file_name}' on disk '{$upload->disk}' processed successfully",
            $metadata
        );

        return $metadata;
    }

    private function getAudioStream(MediaOpener $audio): ?Stream
    {
        return collect($audio->getStreams())
            ->filter(fn($stream) => $stream->isAudio())
            ->first();
    }

    private function extractAudioMetadata(Stream $stream): array
    {
        logger(collect($stream->all())->toJson(
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        ));
        return [
            'codec_name' => $stream->get('codec_name'),
            'codec_tag_string' => $stream->get('codec_tag_string'),
            'channels' => $stream->get('channels'),
            'duration_ts' => $stream->get('duration_ts'),
            'sample_rate' => $stream->get('sample_rate'),
            'bit_rate' => $stream->get('bit_rate'),
            'bits_per_sample' => $stream->get('bits_per_sample') ?: null,
            'language' => $stream->get('tags')['language'] ?? null,
        ];
    }

}
