<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateCompanySettingsRequest;
use Illuminate\Http\Request;

class CompanySettingsController extends Controller
{
    /**
     * Toon bedrijfsinstellingen van ingelogde gebruiker
     */
    public function show(Request $request)
    {
        $company = $request->user()->company;

        if (!$company) {
            return response()->json(['message' => 'Company not found.'], 404);
        }

        return response()->json($this->formatCompany($company));
    }

    /**
     * Werk bedrijfsinstellingen bij
     */
    public function update(UpdateCompanySettingsRequest $request)
    {
        $company = $request->user()->company;

        if (!$company) {
            return response()->json(['message' => 'Company not found.'], 404);
        }

        $data = $request->validated();

        $company->name = $data['name'];
        if (array_key_exists('email_notifications_enabled', $data)) {
            $company->email_notifications_enabled = (bool) $data['email_notifications_enabled'];
        }
        if (array_key_exists('address', $data)) {
            $company->address = $data['address'];
        }
        if (array_key_exists('postal_code', $data)) {
            $company->postal_code = $data['postal_code'];
        }
        if (array_key_exists('city', $data)) {
            $company->city = $data['city'];
        }
        if (array_key_exists('country', $data)) {
            $company->country = $data['country'];
        }
        if (array_key_exists('vat_number', $data)) {
            $company->vat_number = $data['vat_number'];
        }
        $company->save();

        return response()->json($this->formatCompany($company));
    }

    private function formatCompany($company): array
    {
        return [
            'id' => $company->id,
            'name' => $company->name,
            'address' => $company->address,
            'postal_code' => $company->postal_code,
            'city' => $company->city,
            'country' => $company->country,
            'vat_number' => $company->vat_number,
            'email_notifications_enabled' => (bool) $company->email_notifications_enabled,
        ];
    }
}
