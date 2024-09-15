<?php

namespace App\Services;


use App\Models\Track;
use FFMpeg\FFProbe\DataMapping\Stream;
use FFMpeg\Format\Audio\Mp3;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

class AudioProcessor
{
    public function process(Track $track): Track
    {
        $mp3Stream = $this->convertToMp3($track);
        $track->duration = $mp3Stream->get('duration');
        $track->save();
        return $track;
    }

    public function getDurationInSeconds(Track $track): int
    {
        $audio = $track->getFirstMedia('audio');
        $opener = FFMpeg::fromDisk($audio->disk)->open($audio->getPathRelativeToRoot());
        return $opener->getAudioStream()->get('duration');
    }

    private function convertToMp3(Track $track): Stream
    {
        $audio = $track->getFirstMedia('audio');
        $opener = FFMpeg::fromDisk($audio->disk)->open($audio->getPathRelativeToRoot());

        return $opener->export()
            ->toDisk($audio->disk)
            ->inFormat(new Mp3)
            ->save($audio->getPath())->getAudioStream();
    }

//    private function processAudioStream(Stream $stream): array
//    {
//        return [
//            'codec_name' => $stream->get('codec_name'),
//            'duration' => $stream->get('duration'),
//            'duration_ts' => $stream->get('duration_ts'),
//            'sample_rate' => $stream->get('sample_rate'),
//            'bit_rate' => $stream->get('bit_rate'),
//            'bits_per_sample' => $stream->get('bits_per_sample') ?: $stream->get('bits_per_raw_sample'),
//            'start_time' => $stream->get('start_time'),
//            'channels' => $stream->get('channels'),
//        ];
//    }
}
