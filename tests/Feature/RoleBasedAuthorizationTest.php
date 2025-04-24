<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use Laravel\Passport\Passport;
use App\Models\MaintenanceRecord;
use Database\Seeders\PassportSeeder;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class RoleBasedAuthorizationTest extends TestCase
{

    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        config(['auth.defaults.guard' => 'api']);

        $this->artisan('migrate:fresh');
        $this->seed([
            \Database\Seeders\PassportSeeder::class,
            \Database\Seeders\RolesAndPermissionsSeeder::class,
            \Database\Seeders\TestingSeeder::class,
        ]);
    }

    // Admin Tests
    public function test_admin_can_view_all_users()
    {
        $admin = User::where('email', 'admin@test.com')->first();
        Passport::actingAs($admin);

        $response = $this->getJson('/api/users');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'email']
                ]
            ]);
    }

    public function test_non_admin_cannot_view_all_users()
    {
        $user = User::factory()->create();
        Passport::actingAs($user);

        $response = $this->getJson('/api/users');
        $response->assertStatus(403);
    }

    public function test_unauthenticated_user_cannot_view_users()
    {
        $response = $this->getJson('/api/users');
        $response->assertStatus(401);
    }

    // Customer Tests
    public function test_that_customer_cannot_view_all_users()
    {
        $customer = User::factory()->create()->assignRole('customer');
        Passport::actingAs($customer);

        $response = $this->getJson('/api/users');
        $response->assertStatus(403);
    }

    public function test_that_a_customer_can_view_own_products()
    {
        $customer = User::factory()->create()->assignRole('customer');

        $product = Product::factory()->create([
            'user_id' => $customer->id,
            'assigned_to' => $customer->id,
        ]);

        Passport::actingAs($customer);

        $response = $this->getJson("/api/customer/products/{$product->id}");
        $response->assertStatus(200);
    }

    public function test_that_customers_cannot_update_maintenance_records()
    {
        $customer = User::factory()->create()->assignRole('customer');

        $record = MaintenanceRecord::factory()->create();

        Passport::actingAs($customer);

        $response = $this->putJson("/api/maintenance_records/{$record->id}", [
            'notes' => 'Hacked by customer'
        ]);

        $response->assertStatus(403);
    }

    // Technician Tests
    public function test_that_a_technician_can_view_products()
    {
        $technician = User::factory()->create()->assignRole('technician');
        Passport::actingAs($technician);

        $response = $this->getJson("/api/technician/assigned_products");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => []
            ]);
    }

    public function test_that_a_technician_can_update_maintenance_records()
    {
        $technician = User::factory()->create();
        $technician->assignRole('technician');
    
        // Creat a product and assign it to the technician
        $product = \App\Models\Product::factory()->create([
            'assigned_to' => $technician->id,
            'status' => 'Needs Maintenance',
        ]);
    
        // Create a maintenance record assigned to this technician
        $record = \App\Models\MaintenanceRecord::factory()->create([
            'product_id' => $product->id,
            'technician_id' => $technician->id,
        ]);
    
        Passport::actingAs($technician);
    
        $response = $this->putJson("/api/technician/maintenance_records/{$record->id}", [
            'notes' => 'Replaced valve',
            'status' => 'completed',
        ]);
    
        $response->assertStatus(200)
                 ->assertJsonFragment(['notes' => 'Replaced valve']);
    
    }
/*
    //test series for customers role
    public fucntion test_that_customer_cannot_view_all_users():void{}
    public fucntion test_that_a_customer_can_view_own_products():void{}
    public fucntion test_that_customers_cannot_update_maintenance_records():void{}
    //test series for techn role
    public fucntion test_that_a_technician_can_view_products():void{}
    public fucntion test_that_a_technician_can_update_maintenance_records():void{}
    
    */
}
