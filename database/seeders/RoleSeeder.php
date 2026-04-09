<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Role::firstOrCreate(['role_name' => 'Admin'], ['description' => 'Controls everything']);
        \App\Models\Role::firstOrCreate(['role_name' => 'Teacher'], ['description' => 'Manages academic records']);
        \App\Models\Role::firstOrCreate(['role_name' => 'Accountant'], ['description' => 'Manages payments and payroll']);
        \App\Models\Role::firstOrCreate(['role_name' => 'Student'], ['description' => 'Student/Parent access']);
    }
}
