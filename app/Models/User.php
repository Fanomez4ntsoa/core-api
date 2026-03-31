<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Contracts\JWTSubject;

#[Fillable([
    'uuid', 'email', 'password', 'username',
    'first_name', 'last_name', 'display_name',
    'phone', 'avatar_url', 'cover_photo', 'bio',
    'user_type', 'city', 'postal_code', 'country',
    'company_name', 'siret', 'metier',
    'is_verified', 'verified_at', 'identity_status',
    'is_active', 'locale',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, SoftDeletes;

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($user) {
            $user->uuid = (string) Str::uuid();
        });
    }

    protected function casts(): array
    {
        return [
            'is_verified' => 'boolean',
            'is_active'   => 'boolean',
            'verified_at' => 'datetime',
            'password'    => 'hashed',
        ];
    }

    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [
            'uuid'      => $this->uuid,
            'email'     => $this->email,
            'user_type' => $this->user_type,
        ];
    }

    // Relations
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles')
                        ->withPivot('universe_slug')
                        ->withTimestamps();
    }
}
