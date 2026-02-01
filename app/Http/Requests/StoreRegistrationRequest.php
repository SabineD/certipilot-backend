<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user' => ['required', 'array'],
            'user.name' => ['required', 'string', 'max:255'],
            'user.email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users', 'email')],
            'user.password' => ['required', 'string', Password::min(8)],
            'company' => ['required', 'array'],
            'company.name' => ['required', 'string', 'max:255'],
            'company.address' => ['nullable', 'string', 'max:255'],
            'company.postal_code' => ['nullable', 'string', 'max:20'],
            'company.city' => ['nullable', 'string', 'max:255'],
            'company.country' => ['nullable', 'string', 'size:2'],
            'company.vat_number' => ['required', 'string', 'max:50'],
        ];
    }
}
