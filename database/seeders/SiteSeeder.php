<?php

namespace Database\Seeders;

use App\Models\Site;
use App\Models\Company;
use Illuminate\Database\Seeder;

class SiteSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::first();

        Site::create([
            'company_id' => $company->id,
            'name' => 'Werf Antwerpen',
            'address' => 'Noorderlaan 100, 2030 Antwerpen',
        ]);

        Site::create([
            'company_id' => $company->id,
            'name' => 'Werf Gent',
            'address' => 'Industrieweg 12, 9000 Gent',
        ]);
    }
}
