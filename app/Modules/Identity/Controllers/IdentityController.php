<?php

namespace App\Modules\Identity\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Identity\Requests\SubmitVerificationRequest;
use App\Modules\Identity\Services\IdentityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class IdentityController extends Controller
{
    public function __construct(
        private IdentityService $identityService
    ) {}

    public function status(): JsonResponse
    {
        $user = Auth::guard('api')->user();

        return response()->json(
            $this->identityService->getStatus($user)
        );
    }

    public function submit(SubmitVerificationRequest $request): JsonResponse
    {
        $user = Auth::guard('api')->user();

        $result = $this->identityService->submit($user, $request->validated());

        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], $result['code']);
        }

        return response()->json($result);
    }
}