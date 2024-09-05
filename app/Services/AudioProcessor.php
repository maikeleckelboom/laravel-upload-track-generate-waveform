<?php

namespace App\Services;


use App\Exceptions\AudioStreamNotFound;
use App\Models\Upload;
use FFMpeg\FFProbe\DataMapping\Stream;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

class AudioProcessor
{
    /**
     * @throws AudioStreamNotFound
     */
    public function processMetadata(Upload $upload)
    {
        $probable = FFMpeg::fromDisk($upload->disk)->getFFProbe()->isValid($upload->file_name);

        if (!$probable) {
            throw new AudioStreamNotFound($upload->file_name, $upload->disk);
        }

        logger('probable', [$probable]);

//        $stream = FFMpeg::fromDisk($upload->disk)->open($upload->file_name)->getAudioStream();
//
//        if (!$stream) {
//            throw new AudioStreamNotFound($upload->file_name, $upload->disk);
//        }
//
//        return $this->extractAudioMetadata($stream);
    }

    private function extractAudioMetadata(Stream $stream): array
    {
        return [
            'codec_name' => $stream->get('codec_name'),
            'codec_tag_string' => $stream->get('codec_tag_string'),
            'duration' => $stream->get('duration'),
            'sample_rate' => $stream->get('sample_rate'),
            'bit_rate' => $stream->get('bit_rate'),
            'bits_per_sample' => $stream->get('bits_per_sample') ?: $stream->get('bits_per_raw_sample'),
            'channels' => $stream->get('channels'),
        ];
    }

}
