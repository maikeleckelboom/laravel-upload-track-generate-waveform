<?php

namespace App\Traits;

use App\Models\User;

trait CanBeFollowed
{
    public function followers()
    {
        return $this->belongsToMany(User::class, 'follows', 'following_id', 'follower_id')
            ->withTimestamps();
    }

    public function isFollowedBy(User $user): bool
    {
        return $this->followers->contains($user);
    }

    public function getFollowersCountAttribute(): int
    {
        return $this->followers()->count();
    }
}
