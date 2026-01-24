<?php

namespace Database\Seeders;

use App\Models\Machine;
use App\Models\Company;
use App\Models\Site;
use Illuminate\Database\Seeder;

class MachineSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::first();
        $site = Site::first();

        Machine::create([
            'company_id' => $company->id,
            'site_id' => $site->id,
            'name' => 'Heftruck Toyota',
            'type' => 'Heftruck',
            'serial_number' => 'HT-2021-001',
        ]);

        Machine::create([
            'company_id' => $company->id,
            'site_id' => $site->id,
            'name' => 'Graafmachine Caterpillar',
            'type' => 'Graafmachine',
            'serial_number' => 'CAT-2018-445',
        ]);

        Machine::create([
            'company_id' => $company->id,
            'site_id' => null,
            'name' => 'Mobiele kraan Liebherr',
            'type' => 'Kraan',
            'serial_number' => 'KR-2019-778',
        ]);
    }
}
