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
            'name' => 'Pieter Janssens',
            'job_title' => 'Werfleider',
            'active' => true,
        ]);

        Employee::create([
            'company_id' => $company->id,
            'site_id' => null,
            'name' => 'Sarah De Smet',
            'job_title' => 'Preventieadviseur',
            'active' => true,
        ]);

        Employee::create([
            'company_id' => $company->id,
            'site_id' => $site->id,
            'name' => 'Tom Vermeulen',
            'job_title' => 'Arbeider',
            'active' => true,
        ]);
    }
}