<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //Clear cached permissions
        
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions for Users management
        Permission::create(['name' => 'api:view users',   'guard_name' => 'api']);
        Permission::create(['name' => 'api:create users', 'guard_name' => 'api']);
        Permission::create(['name' => 'api:edit users',   'guard_name' => 'api']);
        Permission::create(['name' => 'api:delete users', 'guard_name' => 'api']);

        // Create permissions for Products management
        Permission::create(['name' => 'api:view products',   'guard_name' => 'api']);
        Permission::create(['name' => 'api:create products', 'guard_name' => 'api']);
        Permission::create(['name' => 'api:edit products',   'guard_name' => 'api']);
        Permission::create(['name' => 'api:delete products', 'guard_name' => 'api']);
        Permission::create(['name' => 'api:assign products', 'guard_name' => 'api']);  // Admin can assign products

        // Create permissions for Maintenance Records
        Permission::create(['name' => 'api:view maintenance_records',     'guard_name' => 'api']);
        Permission::create(['name' => 'api:create maintenance_records',   'guard_name' => 'api']);
        Permission::create(['name' => 'api:edit maintenance_records',     'guard_name' => 'api']);
        Permission::create(['name' => 'api:complete maintenance_records', 'guard_name' => 'api']);
        Permission::create(['name' => 'api:delete maintenance_records',   'guard_name' => 'api']);

        // Create permissions for Profile management
        Permission::create(['name' => 'api:edit profile',   'guard_name' => 'api']);
        Permission::create(['name' => 'api:delete profile', 'guard_name' => 'api']);

        // Create specific permissions for products for customers
        Permission::create(['name' => 'api:view own products', 'guard_name' => 'api']);
        Permission::create(['name' => 'api:edit own products', 'guard_name' => 'api']);

        // Create roles and assign created permissions

        // Admin Role (Granted Full access)
        $adminRole = Role::create(['name' => 'admin', 'guard_name' => 'api']);
        $adminRole->givePermissionTo(Permission::all());





        
        // Technician Role (Moderate Limited access to products and maintenance records)
        $technicianRole = Role::create(['name' => 'technician', 'guard_name' => 'api']);
        $technicianRole->givePermissionTo([
            'api:view products',                      // View products assigned to technician
            'api:view maintenance_records',           // View maintenance records
            'api:create maintenance_records',         // Create maintenance records
            'api:edit maintenance_records',           // Edit maintenance records
            'api:complete maintenance_records',       // Complete maintenance records
            'api:edit profile'                        // Technicians can edit their profile
        ]);

        // Customer Role (Given Limited access to their own products and profile)
        $customerRole = Role::create(['name' => 'customer',  'guard_name' => 'api']);
        $customerRole->givePermissionTo([
            'api:view products',                      // View their own products
            'api:edit profile',                       // opt Edit their profile
            'api:view own products',                  // View own products (specific)
            'api:edit own products'                   // Edit own products (specific)
        ]);
    
    }
}
