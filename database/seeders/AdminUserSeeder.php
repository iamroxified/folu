<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $role = Role::where('role_name', 'Admin')->first();
        
        User::updateOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'System Admin',
                'email' => 'admin@example.com',
                'password' => 'password', // Automatically hashed by the model cast
                'role_id' => $role ? $role->id : null,
            ]
        );
    }
}
