<?php

namespace App\Modules\Identity\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class SubmitVerificationRequest extends FormRequest
{
    private const MAX_BYTES = 5 * 1024 * 1024; // 5 MB après décodage

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

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            foreach (['selfie_base64', 'id_document_base64'] as $field) {
                $value = $this->input($field);
                if (!is_string($value) || $value === '') {
                    continue;
                }
                $decoded = base64_decode($value, true);
                if ($decoded === false) {
                    $v->errors()->add($field, "Le champ {$field} doit être en base64 valide.");
                    continue;
                }
                if (strlen($decoded) > self::MAX_BYTES) {
                    $v->errors()->add($field, "Le champ {$field} dépasse la taille max de 5 Mo.");
                }
            }
        });
    }
}