<?php

namespace App\Traits;

use App\Models\User;

trait CanFollow
{
    public function following()
    {
        return $this->belongsToMany(User::class, 'follows', 'follower_id', 'following_id')
            ->withTimestamps();
    }

    public function follow(User $user): void
    {
        $this->following()->syncWithoutDetaching($user);
    }

    public function unfollow(User $user): void
    {
        $this->following()->detach($user);
    }

    public function isFollowing(User $user): bool
    {
        return $this->following->contains($user);
    }

    public function getFollowingCountAttribute(): int
    {
        return $this->following()->count();
    }
}
