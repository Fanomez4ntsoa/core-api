<?php

namespace App\Modules\Auth\Services;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthService
{
    public function register(array $data): array
    {
        // Créer le user — password hashé auto par le cast 'hashed'
        $user = User::create([
            'email'        => $data['email'],
            'password'     => $data['password'],
            'username'     => $data['username'],
            'display_name' => $data['display_name'],
            'user_type'    => $data['user_type'] ?? 'particulier',
            'is_active'    => true,
            'locale'       => 'fr',
        ]);

        // Attacher le rôle social_user par défaut (comme Emergent)
        $role = Role::where('slug', 'social_user')->first();
        if ($role) {
            $user->roles()->attach($role->id, ['universe_slug' => 'core']);
        }

        // Générer le JWT
        $token = JWTAuth::fromUser($user);

        return [
            'token'   => $token,
            'user'    => $this->formatUser($user),
            'profile' => $this->formatProfile($user),
        ];
    }

    public function login(array $credentials): array|false
    {
        if (!$token = Auth::guard('api')->attempt($credentials)) {
            return false;
        }

        $user = Auth::guard('api')->user();

        return [
            'token'   => $token,
            'user'    => $this->formatUser($user),
            'profile' => $this->formatProfile($user),
        ];
    }

    public function me(User $user): array
    {
        return $this->formatProfile($user);
    }

    // ============ HELPERS PRIVÉS ============

    private function formatUser(User $user): array
    {
        return [
            'id'        => $user->uuid,
            'email'     => $user->email,
            'name'      => $user->display_name,
            'role'      => $user->roles->first()?->slug ?? 'social_user',
            'user_type' => $user->user_type,
            'is_active' => $user->is_active,
        ];
    }

    private function formatProfile(User $user): array
    {
        return [
            'id'                   => $user->uuid,
            'user_id'              => $user->uuid,
            'email'                => $user->email,
            'username'             => $user->username,
            'display_name'         => $user->display_name,
            'user_type'            => $user->user_type,
            'profile_photo'        => $user->avatar_url,
            'bio'                  => $user->bio,
            'city'                 => $user->city,
            'company_name'         => $user->company_name,
            'metier'               => $user->metier,
            'is_verified'          => $user->is_verified,
            'identity_status'      => $user->identity_status,
            'role'                 => $user->roles->first()?->slug ?? 'social_user',
            'has_pro_subscription' => false,
            'shop_enabled'         => false,
        ];
    }
}