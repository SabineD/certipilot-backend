<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Company;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::first();

        User::create([
            'name' => 'Jan Peeters',
            'email' => 'jan.peeters@demobouw.be',
            'password' => Hash::make('password'),
            'company_id' => $company->id,
        ]);
    }
}
