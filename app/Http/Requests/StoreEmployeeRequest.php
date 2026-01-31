<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $companyId = $this->user()?->company?->id;

        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'job_title' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'site_id' => [
                'nullable',
                'uuid',
                Rule::exists('sites', 'id')->where(fn ($query) => $query->where('company_id', $companyId)),
            ],
        ];
    }
}
