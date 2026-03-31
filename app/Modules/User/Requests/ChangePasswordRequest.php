<?php

namespace App\Modules\User\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChangePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'current_password' => 'required',
            'new_password'     => 'required|min:6|confirmed',
        ];
    }

    public function messages(): array
    {
        return [
            'new_password.confirmed' => 'La confirmation du mot de passe ne correspond pas',
            'new_password.min'       => 'Le nouveau mot de passe doit faire au moins 6 caractères',
        ];
    }
}