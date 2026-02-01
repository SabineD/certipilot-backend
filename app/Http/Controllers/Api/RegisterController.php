<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRegistrationRequest;
use App\Models\Company;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    /**
     * Registreer nieuw bedrijf + eerste gebruiker
     */
    public function store(StoreRegistrationRequest $request)
    {
        $data = $request->validated();

        $result = DB::transaction(function () use ($data) {
            $company = Company::create([
                'name' => $data['company']['name'],
                'address' => $data['company']['address'] ?? null,
                'postal_code' => $data['company']['postal_code'] ?? null,
                'city' => $data['company']['city'] ?? null,
                'country' => $data['company']['country'] ?? 'BE',
                'vat_number' => $data['company']['vat_number'],
                'email_notifications_enabled' => true,
            ]);

            $user = User::create([
                'company_id' => $company->id,
                'name' => $data['user']['name'],
                'email' => $data['user']['email'],
                'password' => Hash::make($data['user']['password']),
                'role' => User::ROLE_ZAAKVOERDER,
            ]);

            return compact('company', 'user');
        });

        event(new Registered($result['user']));
        Auth::login($result['user']);

        return response()->json([
            'user' => [
                'id' => $result['user']->id,
                'name' => $result['user']->name,
                'email' => $result['user']->email,
                'role' => $result['user']->role,
            ],
            'company' => [
                'id' => $result['company']->id,
                'name' => $result['company']->name,
                'address' => $result['company']->address,
                'postal_code' => $result['company']->postal_code,
                'city' => $result['company']->city,
                'country' => $result['company']->country,
                'vat_number' => $result['company']->vat_number,
                'email_notifications_enabled' => (bool) $result['company']->email_notifications_enabled,
            ],
        ], 201);
    }
}
