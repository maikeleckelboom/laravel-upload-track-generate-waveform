<?php

namespace App\Services\MediaLibrary;

use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\DefaultPathGenerator;
use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;

class CustomPathGenerator extends DefaultPathGenerator implements PathGenerator
{
    /*
     * Get a unique base path for the given media.
     */
    protected function getBasePath(Media $media): string
    {
        return auth()->user()->id . DIRECTORY_SEPARATOR . $media->getKey();
    }

    /*
     * Get a unique path for the given media.
     */
    public function getPath(Media $media): string
    {
        return $this->getBasePath($media) . DIRECTORY_SEPARATOR . $media->file_name;
    }




}
