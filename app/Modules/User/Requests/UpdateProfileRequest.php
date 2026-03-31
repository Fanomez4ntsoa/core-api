<?php

namespace App\Modules\User\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'display_name' => 'sometimes|string|max:100',
            'username'     => 'sometimes|string|unique:users,username,' . $this->user()->id,
            'bio'          => 'sometimes|nullable|string|max:500',
            'city'         => 'sometimes|nullable|string|max:100',
            'postal_code'  => 'sometimes|nullable|string|max:10',
            'country'      => 'sometimes|nullable|string|max:2',
            'phone'        => 'sometimes|nullable|string|max:20',
            'metier'       => 'sometimes|nullable|string|max:100',
            'company_name' => 'sometimes|nullable|string|max:150',
            'siret'        => 'sometimes|nullable|string|max:14',
            'user_type'    => 'sometimes|in:particulier,professionnel',
        ];
    }
}