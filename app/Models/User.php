<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use TaylorNetwork\UsernameGenerator\FindSimilarUsernames;
use TaylorNetwork\UsernameGenerator\GeneratesUsernames;

class User extends Authenticatable implements HasMedia
{
    use HasFactory;
    use Notifiable;
    use FindSimilarUsernames;
    use GeneratesUsernames;
    use InteractsWithMedia;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'username',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];


    protected $appends = [
        'avatar_url',
        'followers_count',
        'following_count',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // CanFollow, CanBeFollowed traits

    /**
     * Users that this user is following.
     */
    public function following()
    {
        return $this->belongsToMany(User::class, 'follows', 'follower_id', 'following_id')
            ->withTimestamps();
    }

    /**
     * Users that follow this user.
     */
    public function followers()
    {
        return $this->belongsToMany(
            User::class, 'follows', 'following_id', 'follower_id')
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

    /**
     * Check if this user is followed by another user.
     */
    public function isFollowedBy(User $user): bool
    {
        return $this->followers->contains($user);
    }

    /**
     * Check if this user follows another user.
     */
    public function isFollowing(User $user): bool
    {
        return $this->following->contains($user);
    }

    /**
     * Get the number of followers for this user.
     */
    public function getFollowersCountAttribute(): int
    {
        return $this->followers()->count();
    }

    /**
     * Get the number of users this user is following.
     */
    public function getFollowingCountAttribute(): int
    {
        return $this->following()->count();
    }

    // Other ....

    /**
     * Get the user's avatar.
     */
    public function getAvatarUrlAttribute(): string
    {
        $avatar = $this->getFirstMediaUrl('avatar');
        return !('' === $avatar)
            ? $avatar
            : 'https://www.gravatar.com/avatar/' . md5($this->email) . '?d=mp';
    }

    public function temporaryUploads(): HasMany
    {
        return $this->hasMany(TemporaryUpload::class);
    }

}
