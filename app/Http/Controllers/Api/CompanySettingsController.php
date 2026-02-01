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
        $company->save();

        return response()->json($this->formatCompany($company));
    }

    private function formatCompany($company): array
    {
        return [
            'id' => $company->id,
            'name' => $company->name,
            'email_notifications_enabled' => (bool) $company->email_notifications_enabled,
        ];
    }
}
