<?php

namespace App\Services;


use App\Models\Track;
use FFMpeg\Format\Audio\Flac;
use Illuminate\Support\Facades\Storage;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

class AudioProcessor
{
    public function process(Track $track): void
    {
        if (!$this->isValidFormat($track)) {
            $this->convertAudioFormat($track);
        }

        $track->duration = $this->getDurationInSeconds($track);
        $track->save();
    }

    public function getDurationInSeconds(Track $track): float
    {
        $audio = $track->getFirstMedia('audio');
        $opener = FFMpeg::fromDisk($audio->disk)->open($audio->getPathRelativeToRoot());
        return $opener->getAudioStream()->get('duration');
    }

    public function convertAudioFormat(Track $track): void
    {
        $original = $track->getFirstMedia('audio', fn($file) => $file->getCustomProperty('original'));


        $ffmpeg = FFMpeg::fromDisk($original->disk)->open($original->getPathRelativeToRoot());

        $outputFilename = $this->convertToFlacFormat($original->getPathRelativeToRoot());

        $ffmpeg->export()
            ->toDisk($original->disk)
            ->inFormat(new Flac)
            ->save($outputFilename);


        $track->addMediaFromDisk($outputFilename, $original->disk)
            ->withCustomProperties(['format' => 'flac', 'conversion' => true])
            ->toMediaLibrary('audio', 'conversions');
    }

    private function convertToFlacFormat(string $path): string
    {
        $dirname = pathinfo($path, PATHINFO_DIRNAME);
        $filename = pathinfo($path, PATHINFO_FILENAME);
        return "{$dirname}/{$filename}.flac";
    }

    private function isValidFormat(Track $track): bool
    {
        $format = $track->getFirstMedia('audio')->extension;
        $supportedFormats = explode(',', config('audiowaveform.supported_formats'));
        return in_array($format, $supportedFormats);
    }

}
