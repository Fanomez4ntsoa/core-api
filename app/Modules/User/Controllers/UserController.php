<?php

namespace App\Modules\User\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\User\Requests\ChangePasswordRequest;
use App\Modules\User\Requests\UpdateProfileRequest;
use App\Modules\User\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function __construct(
        private UserService $userService
    ) {}

    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        $user = Auth::guard('api')->user();
        $profile = $this->userService->updateProfile($user, $request->validated());

        return response()->json([
            'message' => 'Profil mis à jour',
            'profile' => $profile,
        ]);
    }

    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $user = Auth::guard('api')->user();

        $success = $this->userService->changePassword(
            $user,
            $request->current_password,
            $request->new_password
        );

        if (!$success) {
            return response()->json([
                'error' => 'Mot de passe actuel incorrect'
            ], 422);
        }

        return response()->json([
            'message' => 'Mot de passe modifié avec succès'
        ]);
    }
}