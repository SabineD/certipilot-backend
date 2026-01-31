<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Company;
use App\Models\Site;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::first();
        $site = Site::first();

        Employee::create([
            'company_id' => $company->id,
            'site_id' => $site->id,
            'first_name' => 'Pieter',
            'last_name' => 'Janssens',
            'name' => 'Pieter Janssens',
            'job_title' => 'Werfleider',
            'email' => 'pieter.janssens@example.com',
            'is_active' => true,
            'active' => true,
        ]);

        Employee::create([
            'company_id' => $company->id,
            'site_id' => null,
            'first_name' => 'Sarah',
            'last_name' => 'De Smet',
            'name' => 'Sarah De Smet',
            'job_title' => 'Preventieadviseur',
            'email' => 'sarah.desmet@example.com',
            'is_active' => true,
            'active' => true,
        ]);

        Employee::create([
            'company_id' => $company->id,
            'site_id' => $site->id,
            'first_name' => 'Tom',
            'last_name' => 'Vermeulen',
            'name' => 'Tom Vermeulen',
            'job_title' => 'Arbeider',
            'email' => 'tom.vermeulen@example.com',
            'is_active' => true,
            'active' => true,
        ]);
    }
}
