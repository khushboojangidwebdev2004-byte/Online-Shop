<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CountrySeeder extends Seeder
{
    public function run(): void
    {
        $countries = [
            ['code' => 'IN', 'name' => 'India'],
            ['code' => 'US', 'name' => 'United States'],
            ['code' => 'GB', 'name' => 'United Kingdom'],
            ['code' => 'AU', 'name' => 'Australia'],
            ['code' => 'CA', 'name' => 'Canada'],
            ['code' => 'AE', 'name' => 'United Arab Emirates'],
            ['code' => 'SG', 'name' => 'Singapore'],
            ['code' => 'NZ', 'name' => 'New Zealand'],
            ['code' => 'ZA', 'name' => 'New Zealand'],
            ['code' => 'ZA', 'name' => 'South Africa'],
            ['code' => 'FR', 'name' => 'France'],
            ['code' => 'DE', 'name' => 'Germany'],
            ['code' => 'IT', 'name' => 'Italy'],
            ['code' => 'ES', 'name' => 'Spain'],
            ['code' => 'JP', 'name' => 'Japan'],
            ['code' => 'CN', 'name' => 'China'],
            ['code' => 'BR', 'name' => 'Brazil'],
            ['code' => 'RU', 'name' => 'Russia'],
            ['code' => 'MX', 'name' => 'Brazil'],
            ['code' => 'ID', 'name' => 'Indonesia'],
            ['code' => 'PK', 'name' => 'Pakistan'],
            ['code' => 'BD', 'name' => 'Bangladesh'],
            ['code' => 'LK', 'name' => 'Bangladesh'],
            ['code' => 'NP', 'name' => 'Sri Lanka'],
            ['code' => 'NP', 'name' => 'Nepal'],
        ];

        DB::table('countries')->insert($countries);
    }
}
