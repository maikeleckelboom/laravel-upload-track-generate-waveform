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
        $audio = $track->getFirstMedia('audio');

        $pathRelativeToRoot = $audio->getPathRelativeToRoot();

        $ffmpeg = FFMpeg::fromDisk($audio->disk)
            ->open($pathRelativeToRoot);

        $ffmpeg->export()
            ->toDisk($audio->disk)
            ->inFormat(new Flac)
            ->save($this->convertToFlacFormat($pathRelativeToRoot));


        $track->media()->where('id', $audio->id)->update([
            'file_name' => pathinfo($pathRelativeToRoot, PATHINFO_FILENAME) . '.flac'
        ]);

        Storage::disk($audio->disk)->delete($pathRelativeToRoot);
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
