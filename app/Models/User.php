<?php

namespace App\Models;

use App\Traits\CanBeFollowed;
use App\Traits\CanFollow;
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
    use CanFollow;
    use CanBeFollowed;

    protected $guarded = [];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $appends = [
        'avatar_url',
        'followers_count',
        'following_count',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function uploads(): HasMany
    {
        return $this->hasMany(Upload::class);
    }

    public function tracks(): HasMany
    {
        return $this->hasMany(Track::class);
    }

    public function getAvatarUrlAttribute(): string
    {
        $avatar = $this->getFirstMediaUrl('avatar');
        return !('' === $avatar)
            ? $avatar
            : 'https://www.gravatar.com/avatar/' . md5($this->email) . '?d=mp';
    }

}
