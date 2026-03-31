<?php

namespace App\Modules\User\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function updateProfile(User $user, array $data): array
    {
        // Mise à jour seulement des champs envoyés — même logique qu'Emergent
        $user->update($data);
        $user->refresh();

        return $this->formatProfile($user);
    }

    public function changePassword(User $user, string $currentPassword, string $newPassword): bool
    {
        // Vérifier l'ancien mot de passe
        if (!Hash::check($currentPassword, $user->password)) {
            return false;
        }

        // Le cast 'hashed' dans le model gère le bcrypt auto
        $user->update(['password' => $newPassword]);

        return true;
    }

    private function formatProfile(User $user): array
    {
        return [
            'id'              => $user->uuid,
            'email'           => $user->email,
            'username'        => $user->username,
            'display_name'    => $user->display_name,
            'user_type'       => $user->user_type,
            'profile_photo'   => $user->avatar_url,
            'bio'             => $user->bio,
            'city'            => $user->city,
            'postal_code'     => $user->postal_code,
            'country'         => $user->country,
            'phone'           => $user->phone,
            'company_name'    => $user->company_name,
            'metier'          => $user->metier,
            'siret'           => $user->siret,
            'is_verified'     => $user->is_verified,
            'identity_status' => $user->identity_status,
            'role'            => $user->roles->first()?->slug ?? 'social_user',
        ];
    }
}