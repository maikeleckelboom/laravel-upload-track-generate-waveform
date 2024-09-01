<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AudioMetadata extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function track(): BelongsTo
    {
        return $this->belongsTo(Track::class);
    }
}
