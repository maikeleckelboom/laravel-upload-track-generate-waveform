<?php

namespace App;

use App\Models\Track;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Pluralizer;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\DefaultPathGenerator;
use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator as PathGeneratorInterface;

class PathGenerator extends DefaultPathGenerator implements PathGeneratorInterface
{
    public function getPath(Media $media): string
    {
        return $this->pluralizeModelName($media->model) . '/'
            . $media->model->getKey() . '/'
            . $media->collection_name . '/'
            . $media->getKey() . '/';
    }

    public function getPathForConversions(Media $media): string
    {
        return $this->getPath($media) . 'conversions/';
    }

    public function getPathForResponsiveImages(Media $media): string
    {
        return $this->getPath($media) . 'responsive/';
    }

    private static function pluralizeModelName(Model $model): string
    {
        return Pluralizer::plural(strtolower(class_basename($model)));
    }
}
