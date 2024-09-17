<?php

namespace App\Services;


use App\Format\Audio\Opus;
use App\Models\Track;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

class AudioProcessor
{
    private const string PLAYBACK_FORMAT = 'opus';

    public function process(Track $track): void
    {
        if ($this->isValidAsPlayback($track)) {
            $this->addOriginalFileAsPlayback($track);
        } else {
            $this->addConvertedFileAsPlayback($track);
        }

        $track->duration = $this->getDurationInSeconds($track);
        $track->save();
    }

    private function isValidAsPlayback(Track $track): bool
    {
        $format = $track->getFirstMedia('audio')->extension;
        return $format === self::PLAYBACK_FORMAT;
    }

    private function addOriginalFileAsPlayback(Track $track): void
    {
        $original = $track->getFirstMedia('audio', fn($file) => $file->getCustomProperty('original'));
        $track->addMedia($original->getPath())
            ->preservingOriginal()
            ->withCustomProperties([
                'type' => 'playback',
                'format' => $original->extension
            ])
            ->toMediaLibrary('audio', 'playback');
    }

    public function getDurationInSeconds(Track $track): float
    {
        $audio = $track->getFirstMedia('audio');
        $opener = FFMpeg::fromDisk($audio->disk)->open($audio->getPathRelativeToRoot());
        return $opener->getAudioStream()->get('duration');
    }

    public function addConvertedFileAsPlayback(Track $track): void
    {
        $original = $track->getFirstMedia('audio', fn($file) => $file->getCustomProperty('original'));

        $ffmpeg = FFMpeg::fromDisk($original->disk)->open($original->getPathRelativeToRoot());

        $outputFilename = $this->convertToPlaybackFormat($original->getPathRelativeToRoot());

        $ffmpeg->export()
            ->toDisk($original->disk)
            ->inFormat(new Opus)
            ->addFilter('-strict')
            ->addFilter('-2')
            ->save($outputFilename);

        $track->addMediaFromDisk($outputFilename, $original->disk)
            ->withCustomProperties([
                'type' => 'playback',
                'format' => self::PLAYBACK_FORMAT
            ])
            ->toMediaLibrary('audio', 'playback');
    }

    private function convertToPlaybackFormat(string $path): string
    {
        $dirname = pathinfo($path, PATHINFO_DIRNAME);
        $filename = pathinfo($path, PATHINFO_FILENAME);
        return "{$dirname}/{$filename}." . self::PLAYBACK_FORMAT;
    }

    private function isSupportedFormat(Track $track): bool
    {
        $format = $track->getFirstMedia('audio')->extension;
        $supportedFormats = explode(',', config('audiowaveform.supported_formats'));
        return in_array($format, $supportedFormats);
    }

}
