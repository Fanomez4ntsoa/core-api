<?php

namespace App\Modules\Identity\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubmitVerificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'selfie_base64'      => 'required|string',
            'id_document_base64' => 'required|string',
            'id_document_type'   => 'required|in:passport,id_card,driver_license',
        ];
    }

    public function messages(): array
    {
        return [
            'id_document_type.in' => 'Type de document invalide. Acceptés : passport, id_card, driver_license',
        ];
    }
}