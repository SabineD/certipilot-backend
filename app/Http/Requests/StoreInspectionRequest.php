<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInspectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'inspection_type' => ['required', 'string', 'max:255'],
            'inspected_at' => ['required', 'date'],
            'valid_until' => ['required', 'date', 'after:inspected_at'],
        ];
    }
}
