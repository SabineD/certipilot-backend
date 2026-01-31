<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCertificateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'certificate_type' => ['required', 'string', 'max:255'],
            'issued_at' => ['required', 'date'],
            'valid_until' => ['required', 'date', 'after:issued_at'],
        ];
    }
}
