<?php

namespace App\Modules\Auth\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email'        => 'required|email|unique:users,email',
            'password'     => 'required|min:6',
            'username'     => 'required|string|unique:users,username',
            'display_name' => 'required|string',
            'user_type'    => 'sometimes|in:particulier,professionnel',
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique'          => 'Email déjà utilisé',
            'username.unique'       => 'Nom d\'utilisateur déjà pris',
            'password.min'          => 'Le mot de passe doit faire au moins 6 caractères',
            'display_name.required' => 'Le nom d\'affichage est requis',
        ];
    }
}