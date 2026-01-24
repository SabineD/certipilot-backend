<?php

namespace Database\Seeders;

use App\Models\CertificateType;
use Illuminate\Database\Seeder;

class CertificateTypeSeeder extends Seeder
{
    public function run(): void
    {
        CertificateType::create([
            'name' => 'VCA-attest',
            'applies_to' => 'employee',
        ]);

        CertificateType::create([
            'name' => 'EHBO-attest',
            'applies_to' => 'employee',
        ]);

        CertificateType::create([
            'name' => 'CE-certificaat machine',
            'applies_to' => 'machine',
        ]);
    }
}