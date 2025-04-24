<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\MaintenanceRecord;
use Laravel\Passport\Passport;
use Spatie\Permission\Models\Role;
use Database\Seeders\PassportSeeder;
use Spatie\Permission\Models\Permission;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;



class ProductManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $technician;
    protected User $customer;

    protected function setUp(): void
    {
        parent::setUp();
        config(['auth.defaults.guard' => 'api']);

        $this->seed([
            \Database\Seeders\PassportSeeder::class,
            \Database\Seeders\RolesAndPermissionsSeeder::class,
        ]);
/*
         // Create and assign roles to users
         $this->admin = User::factory()->create();
         $this->admin->assignRole('admin');
 
         $this->technician = User::factory()->create();
         $this->technician->assignRole('technician');
 
         $this->customer = User::factory()->create();
         $this->customer->assignRole('customer');
         */
        $this->admin = $this->createUserWithRole('admin');
        $this->technician = $this->createUserWithRole('technician');
        $this->customer = $this->createUserWithRole('customer');
    }
// Helper function to create users with roles
private function createUserWithRole(string $role): User
{
    $user = User::factory()->create();
    $user->assignRole($role);
    return $user;
}

/******************************
 * ADMIN SPECIFIC TESTS
 ******************************/
public function test_admin_can_list_products()
{
    Product::factory()->count(3)->create(['user_id' => $this->admin->id]);
    Product::factory()->count(2)->create();

    Passport::actingAs($this->admin);

    $response = $this->getJson('/api/products');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'user_id',
                    'name',
                    'type',
                    'type_capacity',
                    'serial_number',
                    'status',
                    'location'
                ]
            ]
        ]);
}

public function test_admin_can_create_product()
{
    Passport::actingAs($this->admin);

    $productData = Product::factory()
        ->make([
            'user_id' => $this->admin->id,
            'serial_number' => 'TEST-123',
            'status' => 'Active',
            'assigned_to' => null
        ])
        ->toArray();

    $response = $this->postJson('/api/products', $productData);

    $response->assertStatus(201);
}

public function test_admin_can_create_product_needing_maintenance()
{
    Passport::actingAs($this->admin);

        $response = $this->postJson('/api/products', [
            ...Product::factory()->make()->toArray(),
            'status' => 'Needs Maintenance',
            'assigned_to' => $this->technician->id,
            'serial_number' => 'MAINT-001'
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.assigned_to', $this->technician->id)
            ->assertJsonPath('data.status', 'Needs Maintenance');
}

public function test_admin_can_update_product_basic_fields()
{
    $product = Product::factory()->create([
        'user_id' => $this->admin->id,
        'status' => 'Active'
    ]);

    Passport::actingAs($this->admin);

    $response = $this->putJson("/api/products/{$product->id}", [
        'location' => 'New Building, Floor 5',
        'status' => 'Expired'
    ]);

    $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'location' => 'New Building, Floor 5',
                    'status' => 'Expired'
                ]
            ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'location' => 'New Building, Floor 5',
            'status' => 'Expired'
        ]);
}

public function test_admin_can_assign_technician_for_maintenance()
{
    $product = Product::factory()->create([
        'user_id' => $this->admin->id,
        'status' => 'Active'
    ]);

    Passport::actingAs($this->admin);

    $response = $this->putJson("/api/products/{$product->id}", [
        'status' => 'Needs Maintenance',
        'assigned_to' => $this->technician->id
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.assigned_to', $this->technician->id)
        ->assertJsonPath('data.status', 'Needs Maintenance');
}

public function test_admin_can_delete_product()
{
    $product = Product::factory()->create([
        'user_id' => $this->admin->id
    ]);

    Passport::actingAs($this->admin);

    $response = $this->deleteJson("/api/products/{$product->id}");

    $response->assertStatus(204);
    $this->assertDatabaseMissing('products', ['id' => $product->id]);
}

public function test_non_admin_cannot_create_products()
{
    $customer = User::factory()->create();
    $customer->assignRole('customer');

    Passport::actingAs($customer);

    $response = $this->postJson('/api/products', [
        'name' => 'Fire Extinguisher',
        'type' => 'CO2',
    ]);

    $response->assertStatus(403);
}

public function test_admin_can_assign_product_to_customer()
{
    $productData = Product::factory()->make([
        'user_id' => $this->admin->id,
        'status' => 'Active',
        'assigned_to' => $this->customer->id
    ])->toArray();

    Passport::actingAs($this->admin);

    $response = $this->postJson('/api/products', $productData);

    $response->assertStatus(201)
        ->assertJsonPath('data.assigned_to', $this->customer->id);
}


/******************************
 * CUSTOMER SPECIFIC TESTS
 ******************************/

public function test_customer_can_view_their_products()
{
    // Create products assigned to this customer
    $assignedProducts = Product::factory()->count(2)->create([
        'user_id' => $this->customer->id,
        'status' => 'Active'
    ]);
    
    // Create products not assigned to this customer
    Product::factory()->count(3)->create();
    
    Passport::actingAs($this->customer);
    
    $response = $this->getJson('/api/customer/products');
    
    $response->assertStatus(200)
        ->assertJsonCount(2, 'data')
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'status',
                    'assigned_to'
                ]
            ]
        ]);
}

public function test_customer_can_view_specific_assigned_product()
{
    $product = Product::factory()->create([
        'user_id' => $this->customer->id,
        'status' => 'Active'
    ]);

    Passport::actingAs($this->customer);

    $response = $this->getJson("/api/customer/products/{$product->id}");

    $response->assertStatus(200)
        ->assertJson([
            'data' => [
                'id' => $product->id,
                'user_id' => $this->customer->id
            ]
        ]);
}


public function test_customer_cannot_view_products_not_assigned_to_them()
{
    $otherCustomer = User::factory()->create()->assignRole('customer');

    $product = Product::factory()->create([
        'user_id' => $otherCustomer->id,
        'status' => 'Active'
    ]);

    Passport::actingAs($this->customer);

    $response = $this->getJson("/api/customer/products/{$product->id}");

    $response->assertStatus(403);
}

public function test_customer_can_view_maintenance_records_for_their_products()
{
    $product = Product::factory()->create([
        'user_id' => $this->customer->id
    ]);

    MaintenanceRecord::factory()->count(3)->create([
        'product_id' => $product->id
    ]);

    Passport::actingAs($this->customer);

    $response = $this->getJson("/api/customer/products/{$product->id}/maintenance_records");

    $response->assertStatus(200)
        ->assertJsonCount(3, 'data');
}

public function test_customer_can_update_their_own_products()
{
    $product = Product::factory()->create([
        'assigned_to' => $this->customer->id,
        'user_id' => $this->customer->id,
        'name' => 'Fire Extinguisher',
        'location' => 'Original Location'
    ]);

    Passport::actingAs($this->customer);

    $response = $this->putJson("/api/customer/products/{$product->id}", [
        'name' => 'Smoke Detector', 
        'location' => 'New Location'
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'data' => [
                'name' => 'Smoke Detector', 
                'location' => 'New Location'
            ]
        ]);
}

public function test_customer_cannot_update_other_customers_products()
{
    $otherCustomer = User::factory()->create()->assignRole('customer');

    $product = Product::factory()->create([
        'assigned_to' => $otherCustomer->id,
        'user_id' => $otherCustomer->id,
        'name' => 'Fire Extinguisher'
    ]);

    Passport::actingAs($this->customer);

    $response = $this->putJson("/api/customer/products/{$product->id}", [
        'name' => 'Fire Extinguisher'
    ]);

    $response->assertStatus(403);
}

public function test_customer_cannot_perform_admin_actions()
{
    Passport::actingAs($this->customer);

    // Test create product
    $createResponse = $this->postJson('/api/products', Product::factory()->make()->toArray());
    $createResponse->assertStatus(403);

    // Test delete product
    $product = Product::factory()->create(['user_id' => $this->customer->id]);
    $deleteResponse = $this->deleteJson("/api/products/{$product->id}");
    $deleteResponse->assertStatus(403);
}

public function test_customer_cannot_perform_technician_actions()
{
    $product = Product::factory()->create([
        'user_id' => $this->customer->id,
        'status' => 'Needs Maintenance'
    ]);

    Passport::actingAs($this->customer);

    // Try to create maintenance record
    $createMaintResponse = $this->postJson("/api/maintenance_records", [
        'product_id' => $product->id,
        'status' => 'in_progress'
    ]);
    $createMaintResponse->assertStatus(403);

    // Try to update maintenance record
    $record = MaintenanceRecord::factory()->create(['product_id' => $product->id]);
    $updateMaintResponse = $this->putJson("/api/maintenance_records/{$record->id}", [
        'status' => 'completed'
    ]);
    $updateMaintResponse->assertStatus(403);
}



   /******************************
     * TECHNICIAN SPECIFIC TESTS
     ******************************/

public function test_technician_can_view_products_assigned_to_them()
    {
       // Create products assigned to this technician
       $assignedProducts = Product::factory()->count(2)->create([
        'assigned_to' => $this->technician->id,
        'status' => 'Needs Maintenance'
    ]);
    
    // Create products not assigned to this technician
    Product::factory()->count(3)->create([
        'assigned_to' => User::factory()->create()->id,
        'status' => 'Needs Maintenance'
    ]);
    
    Passport::actingAs($this->technician);
    
    $response = $this->getJson('/api/technician/assigned_products');
    
    $response->assertStatus(200)
        ->assertJsonCount(2, 'data')
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'status',
                    'assigned_to'
                ]
            ]
        ]);
    }


    public function test_technician_can_view_specific_assigned_product()
    {
        $product = Product::factory()->create([
            'assigned_to' => $this->technician->id,
            'status' => 'Needs Maintenance'
        ]);
        
        Passport::actingAs($this->technician);
        
        $response = $this->getJson("/api/technician/products/{$product->id}");
        
        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $product->id,
                    'assigned_to' => $this->technician->id
                ]
            ]);
    }


    public function test_technician_cannot_view_products_not_assigned_to_them()
    {
        $otherTechnician = User::factory()->create()->assignRole('technician');
        
        $product = Product::factory()->create([
            'assigned_to' => $otherTechnician->id,
            'status' => 'Needs Maintenance'
        ]);
        
        Passport::actingAs($this->technician);
        
        $response = $this->getJson("/api/technician/products/{$product->id}");
        
        $response->assertStatus(404);
    }

    public function test_technician_can_view_their_maintenance_records()
    {
        MaintenanceRecord::factory()->count(3)->create([
            'technician_id' => $this->technician->id
        ]);
        
        // Create records for other technicians
        MaintenanceRecord::factory()->count(2)->create();
        
        Passport::actingAs($this->technician);
        
        $response = $this->getJson('/api/technician/maintenance_records');
        
        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_technician_can_update_their_maintenance_records()
    {
        $record = MaintenanceRecord::factory()->create([
            'technician_id' => $this->technician->id,
            'status' => 'pending'
        ]);
        
        Passport::actingAs($this->technician);
        
        $response = $this->putJson("/api/technician/maintenance_records/{$record->id}", [
            'status' => 'completed',
            'notes' => 'Maintenance completed successfully'
        ]);
        
        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'status' => 'completed',
                    'notes' => 'Maintenance completed successfully'
                ]
            ]);
    }

    public function test_technician_cannot_update_other_technicians_records()
    {
        $otherTechnician = User::factory()->create()->assignRole('technician');
        
        $record = MaintenanceRecord::factory()->create([
            'technician_id' => $otherTechnician->id,
            'status' => 'pending'
        ]);
        
        Passport::actingAs($this->technician);
        
        $response = $this->putJson("/api/technician/maintenance_records/{$record->id}", [
            'status' => 'completed'
        ]);
        
        $response->assertStatus(403);
    }

    public function test_technician_can_view_product_maintenance_history()
    {
        $product = Product::factory()->create([
            'assigned_to' => $this->technician->id
        ]);
        
        MaintenanceRecord::factory()->count(3)->create([
            'product_id' => $product->id,
            'technician_id' => $this->technician->id
        ]);
        
        Passport::actingAs($this->technician);
        
        $response = $this->getJson("/api/technician/products/{$product->id}/maintenance-history");
        
        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_technician_cannot_access_admin_routes()
    {
        Passport::actingAs($this->technician);
        
        $product = Product::factory()->create();
        
        // Test product management routes
        $createResponse = $this->postJson('/api/products', Product::factory()->make()->toArray());
        $updateResponse = $this->putJson("/api/products/{$product->id}", ['name' => 'Updated']);
        $deleteResponse = $this->deleteJson("/api/products/{$product->id}");
        
        $createResponse->assertStatus(403);
        $updateResponse->assertStatus(403);
        $deleteResponse->assertStatus(403);
    }



}


