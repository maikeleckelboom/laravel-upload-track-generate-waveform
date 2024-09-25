<?php

namespace App\Services;

use App\Format\Audio\Opus;
use App\Models\Track;
use Illuminate\Support\Str;
use ProtoneMedia\LaravelFFMpeg\Exporters\HLSExporter;
use ProtoneMedia\LaravelFFMpeg\FFMpeg\ImageFormat;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

class AudioProcessor
{
    private array $supportedFormats;
    private string $playbackFormat;
    private string $artworkFormat;

    public function __construct()
    {
        $this->playbackFormat = config('audio_waveform.playback_format');
        $this->artworkFormat = config('audio_waveform.artwork_format');
        $this->supportedFormats = explode(',', config('audio_waveform.formats'));
    }

    public function process(Track $track): void
    {
        $this->createAudioForPlayback($track);

        $track->duration = $this->getDurationInSeconds($track);
        $track->save();

        $this->getArtworkFromAudio($track);
    }

    private function alwaysProcess(Track $track): void
    {
        $audio = $track->getFirstMedia('audio', fn($file) => $file->getCustomProperty('original'));

        $opener = FFMpeg::fromDisk($audio->disk)->open($audio->getPathRelativeToRoot());

        $outputFilename = Str::replaceLast($audio->extension, $this->playbackFormat, $audio->getPathRelativeToRoot());




    }

    private function createAudioForPlayback(Track $track): void
    {
        $this->isSupportedFormat($track)
            ? $this->addOriginalAudio($track)
            : $this->addConvertedAudio($track);
    }

    private function addOriginalAudio(Track $track): void
    {
        $original = $track->getFirstMedia(
            'audio',
            fn($file) => $file->getCustomProperty('original')
        );
        $track->addMedia($original->getPath())
            ->preservingOriginal()
            ->withCustomProperties([
                'playback' => true,
                'type' => 'audio'
            ])
            ->toMediaLibrary('audio', 'playback');
    }

    public function getDurationInSeconds(Track $track): float
    {
        $audio = $track->getFirstMedia('audio');
        $opener = FFMpeg::fromDisk($audio->disk)->open($audio->getPathRelativeToRoot());
        $audioStream = $opener->getAudioStream();
        return $audioStream->get('duration');
    }

    public function addConvertedAudio(Track $track): void
    {
        $audio = $track->getFirstMedia(
            'audio',
            fn($file) => $file->getCustomProperty('original')
        );

        $opener = FFMpeg::fromDisk($audio->disk)
            ->open($audio->getPathRelativeToRoot());

        $outputFilename = Str::replaceLast(
            $audio->extension,
            $this->playbackFormat,
            $audio->getPathRelativeToRoot()
        );

        $opener->export()
            ->toDisk($audio->disk)
            ->inFormat(new Opus)
            ->addFilter('-strict')
            ->addFilter('-2')
            ->addFilter('-loglevel')
            ->addFilter('quiet')
            ->save($outputFilename);

        $track
            ->addMediaFromDisk($outputFilename, $audio->disk)
            ->withCustomProperties([
                'playback' => true,
                'type' => 'audio'
            ])
            ->toMediaLibrary('audio', 'playback');
    }

    public function getArtworkFromAudio(Track $track): void
    {
        $audio = $track->getFirstMedia(
            'audio',
            fn($file) => $file->getCustomProperty('original')
        );

        $outputFilename = "{$audio->uuid}.{$this->artworkFormat}";

        $ffmpeg = FFMpeg::fromDisk($audio->disk)
            ->open($audio->getPathRelativeToRoot());

        if ($ffmpeg->getAudioStream()->isAudio()) {
            $success = $ffmpeg
                ->exportFramesByAmount(1)
                ->inFormat(new ImageFormat)
                ->toDisk('temporary')
                ->save($outputFilename);
            if ($success) {
                $track
                    ->addMediaFromDisk($outputFilename, 'temporary')
                    ->withResponsiveImages()
                    ->onQueue()
                    ->toMediaLibrary('artwork', 'artwork');
            }
        }
    }

    private function isSupportedFormat(Track $track): bool
    {
        $format = $track->getFirstMedia('audio')->extension;
        return in_array($format, $this->supportedFormats);
    }

    private function isPlaybackFormat(Track $track): bool
    {
        $original = $track->getFirstMedia('audio');
        return $original->extension === $this->playbackFormat;
    }
}
