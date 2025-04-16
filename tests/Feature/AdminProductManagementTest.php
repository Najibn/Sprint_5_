<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Product;
use App\Models\User;
use Laravel\Passport\Passport;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class AdminProductManagementTest extends TestCase
{

    use RefreshDatabase;

    protected $admin;
    protected $token;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Set up roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        $this->setupRolesAndPermissions();
        
        // Create an admin user
        $this->admin = User::factory()->create([
            'role' => 'admin'
        ]);
        $this->admin->assignRole('admin');
        
        // Create access token
        $this->token = $this->admin->createToken('test-token')->accessToken;
    }

    private function setupRolesAndPermissions()
    {
        // Create permissions for Products management
        Permission::create(['name' => 'api:view products']);
        Permission::create(['name' => 'api:create products']);
        Permission::create(['name' => 'api:edit products']);
        Permission::create(['name' => 'api:delete products']);
        
        // Create admin role
        $adminRole = Role::create(['name' => 'admin']);
        $adminRole->givePermissionTo(Permission::all());
    }


    public function test_admin_can_list_products()
    {
        // Create some products
        Product::factory()->count(3)->create();
        
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/products');
        
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
        $customer = User::factory()->create(['role' => 'customer']);
        $technician = User::factory()->create(['role' => 'technician']);
        
        $productData = [
            'user_id' => $customer->id,
            'name' => 'Fire Extinguisher',
            'type' => 'CO2',
            'type_capacity' => '5kg',
            'serial_number' => 'FE12345',
            'status' => 'Needs Maintenance',
            'assigned_to' => $technician->id,
            'location' => 'Building A, Floor 1'
        ];
        
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/products', $productData);
        
        $response->assertStatus(201)
            ->assertJsonFragment([
                'name' => 'Fire Extinguisher',
                'type' => 'CO2',
                'serial_number' => 'FE12345'
            ]);
        
        $this->assertDatabaseHas('products', [
            'name' => 'Fire Extinguisher',
            'serial_number' => 'FE12345'
        ]);
    }

    public function test_admin_can_show_product()
    {
        $product = Product::factory()->create();
        
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/products/' . $product->id);
        
        $response->assertStatus(200)
            ->assertJsonFragment([
                'id' => $product->id,
                'name' => $product->name,
                'serial_number' => $product->serial_number
            ]);
    }

    public function test_admin_can_update_product()
    {
        $product = Product::factory()->create();
        
        $updateData = [
            'name' => 'Updated Fire Extinguisher',
            'status' => 'Active',
            'location' => 'Building B, Floor 2'
        ];
        
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->putJson('/api/products/' . $product->id, $updateData);
        
        $response->assertStatus(200)
            ->assertJsonFragment([
                'name' => 'Updated Fire Extinguisher',
                'status' => 'Active',
                'location' => 'Building B, Floor 2'
            ]);
        
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated Fire Extinguisher',
            'status' => 'Active'
        ]);
    }

    public function test_admin_can_delete_product()
    {
        $product = Product::factory()->create();
        
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->deleteJson('/api/products/' . $product->id);
        
        $response->assertStatus(204);
        
        $this->assertDatabaseMissing('products', [
            'id' => $product->id
        ]);
    }
}


