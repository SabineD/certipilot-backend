<?php

namespace Database\Seeders;

use App\Models\InspectionType;
use Illuminate\Database\Seeder;

class InspectionTypeSeeder extends Seeder
{
    public function run(): void
    {
        InspectionType::create([
            'name' => 'Periodieke keuring machine',
            'applies_to' => 'machine',
            'validity_months' => 12,
        ]);

        InspectionType::create([
            'name' => 'Medische keuring werknemer',
            'applies_to' => 'employee',
            'validity_months' => 24,
        ]);

        InspectionType::create([
            'name' => 'Keuring elektrische installatie',
            'applies_to' => 'site',
            'validity_months' => 60,
        ]);
    }
}