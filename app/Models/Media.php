<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Spatie\MediaLibrary\MediaCollections\Models\Media as BaseMedia;
use Spatie\MediaLibrary\Support\File;

class Media extends BaseMedia
{
    protected $appends = [];

    public function getHighestOrderNumber(): int
    {
        return (int)static::where('model_type', $this->model_type)
            ->where('model_id', $this->model_id)
            ->where('collection_name', $this->collection_name)
            ->max($this->determineOrderColumnName());
    }



}
