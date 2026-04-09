<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SchoolSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\SchoolSetting::create([
            'school_name' => 'Folu School Management System',
            'school_address' => '123 School Street, City, Country',
            'school_phone' => '+1 234 567 8900',
            'school_email' => 'info@foluschool.com',
            'school_motto' => 'Excellence in Education',
            'currency' => 'NGN',
            'timezone' => 'Africa/Lagos',
            'is_installed' => false,
        ]);
    }
}
