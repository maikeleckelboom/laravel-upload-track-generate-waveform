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
    public function process(Upload $upload): array
    {
        $probable = FFMpeg::fromDisk($upload->disk)->getFFProbe()->isValid($upload->file_name);

        if (!$probable) {
            throw new AudioStreamNotFound($upload->file_name, $upload->disk);
        }

        $audio = FFMpeg::fromDisk($upload->disk)->open($upload->file_name);

        $audio->export()
            ->toDisk($upload->disk)
            ->inFormat(new \FFMpeg\Format\Audio\Wav)
            ->save($upload->file_name . '.wav');

        $audio = FFMpeg::fromDisk($upload->disk)->open($upload->file_name . '.wav');

        return $this->extractAudioMetadata($audio->getAudioStream());
    }

    private function extractAudioMetadata(Stream $stream): array
    {
        return [
            'codec_name' => $stream->get('codec_name'),
            'duration' => $stream->get('duration'),
            'sample_rate' => $stream->get('sample_rate'),
            'bit_rate' => $stream->get('bit_rate'),
            'bits_per_sample' => $stream->get('bits_per_sample') ?: $stream->get('bits_per_raw_sample')
        ];
    }

}
