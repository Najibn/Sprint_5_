<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class TestingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Creating test admin with known credentials
        $admin = User::factory()->create([
            'email' => 'admin@test.com',
            'password' => 'AdminPass123!', 
            'role' => 'admin'
        ]);
        
        $admin->assignRole('admin'); // Using API guard from RolesAndPermissionsSeeder
        $admin->assignRole(Role::where('name', 'admin')->where('guard_name', 'api')->first());

        // Create regular users
        User::factory()->count(5)->create();
    }
}
