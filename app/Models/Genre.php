<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Genre extends Model
{
    use HasFactory;

    public function tracks(): HasMany
    {
        return $this->hasMany(Track::class);
    }

    /**
     * @return BelongsTo
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Genre::class);
    }

    public function children(): HasMany
    {
        return $this->hasMany(Genre::class, 'parent_id');
    }

    public function getAncestors(): array
    {
        $ancestors = [];
        $genre = $this;
        while ($genre->parent) {
            $ancestors[] = $genre->parent;
            $genre = $genre->parent;
        }
        return $ancestors;
    }

    public function getDescendants(): array
    {
        $descendants = [];
        $genres = $this->children;
        while ($genres->isNotEmpty()) {
            $descendants = $descendants->merge($genres);
            $genres = $genres->flatMap(fn(Genre $genre) => $genre->children);
        }
        return $descendants;
    }
}
