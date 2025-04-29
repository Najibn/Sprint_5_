<?php
namespace Tests\Feature;

use App\Models\MaintenanceRecord;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Passport\Passport;
use Tests\TestCase;

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

    /**************************************************************
     * ADMIN SPECIFIC TESTS
     ****************************************************************/
    public function test_admin_can_view_all_users()
    {
        $admin = User::where('email', 'admin@test.com')->first();
        Passport::actingAs($admin);

        $response = $this->getJson('/api/users');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'email'],
                ],
            ]);
    }

    public function test_admin_can_create_products(): void
    {
        //Create admin with permissions (NO scope needed)
        $admin = User::factory()->create(['role' => 'admin']);
        $admin->assignRole('admin');
        $admin->givePermissionTo('api:create products');

        // Create customer
        $customer = User::factory()->create(['role' => 'customer']);

        //Generate token WITHOUT scopes
        $token = $admin->createToken('test-token')->accessToken;

        //Make request with required fields
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept'        => 'application/json',
        ])->postJson('/api/products', [
            'user_id'       => $customer->id,
            'name'          => 'Fire Extinguisher',
            'type'          => 'CO2',
            'type_capacity' => '5kg',
            'serial_number' => 'FE-' . rand(1000, 9999),
            'status'        => 'Active',
            'location'      => 'Main Lobby',
        ]);

        //Assertions
        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'user_id',
                    'name',
                    'type',
                    'serial_number',
                    'status',
                ],
            ]);
    }

    public function test_admin_can_filter_users_by_role(): void
    {

        //Create admin with permissions
        $admin = User::factory()->create(['role' => 'admin']);
        $admin->assignRole('admin');

        //Creating exactly ONE technician and ONE other user
        //and  admin with ALL required permissions
        $admin = User::factory()->create(['role' => 'admin']);
        $admin->assignRole('admin');

        // 2. Create technician and product
        $technician = User::factory()->create(['role' => 'technician']);
        $product    = Product::factory()->create(['assigned_to' => null]);
        // 3. Generate valid admin token
        $token = $admin->createToken('admin-token')->accessToken;

        // 4. Make authenticated request
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept'        => 'application/json',
        ])->putJson("/api/products/{$product->id}", [
            'assigned_to' => $technician->id,
            'status'      => 'Needs Maintenance',
        ]);

        // 5. Assertions
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'assigned_to',
                    'status',
                ],
            ])
            ->assertJsonPath('data.assigned_to', $technician->id)
            ->assertJsonPath('data.status', 'Needs Maintenance');

    }

    public function test_admin_can_access_protected_routes()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $admin->assignRole('admin');
        $token = $admin->createToken('test')->accessToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->get('/api/users');

        $response->assertStatus(200);

    }

    public function test_admin_can_assign_any_product_to_technician(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $admin->assignRole('admin');
        //$admin->givePermissionTo('api:assign products');

        $technician = User::factory()->create(['role' => 'technician']);
        $product    = Product::factory()->create(['assigned_to' => null]);

        $response = $this->actingAs($admin, 'api')
            ->putJson("/api/products/{$product->id}", [
                'assigned_to' => $technician->id,
                'status'      => 'Needs Maintenance',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.assigned_to', $technician->id)
            ->assertJsonPath('data.status', 'Needs Maintenance');
    }

    public function test_admin_can_view_expired_products(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $admin->assignRole('admin');
        Product::factory()->create(['status' => 'Expired']);
        Product::factory()->create(['status' => 'Active']);

        $response = $this->actingAs($admin, 'api')
            ->getJson('/api/products?status=Expired');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.status', 'Expired');
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

    /****************************************************************
     * CUSTOMER SPECIFIC TESTS
     ****************************************************************/
    public function test_customer_cannot_access_technician_routes()
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $response = $this->actingAs($customer)
            ->get('/api/technician/assigned_products');
        $response->assertStatus(403);
    }

    public function test_customer_cannot_view_all_users()
    {
        $customer = User::factory()->create()->assignRole('customer');
        Passport::actingAs($customer);

        $response = $this->getJson('/api/users');
        $response->assertStatus(403);
    }

    public function test_customer_can_view_own_products()
    {
        $customer = User::factory()->create()->assignRole('customer');

        $product = Product::factory()->create([
            'user_id'     => $customer->id,
            'assigned_to' => $customer->id,
        ]);

        Passport::actingAs($customer);

        $response = $this->getJson("/api/customer/products/{$product->id}");
        $response->assertStatus(200);
    }

    public function test_customers_cannot_update_maintenance_records()
    {
        $customer = User::factory()->create()->assignRole('customer');

        $record = MaintenanceRecord::factory()->create();

        Passport::actingAs($customer);

        $response = $this->putJson("/api/maintenance_records/{$record->id}", [
            'notes' => 'Hacked by customer',
        ]);

        $response->assertStatus(403);
    }

    public function test_customer_cannot_access_admin_routes()
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $token    = $customer->createToken('test')->accessToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->get('/api/users');

        $response->assertStatus(403);
    }

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
            'user_id'     => $customer->id,
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
            'notes' => 'Hacked by customer',
        ]);

        $response->assertStatus(403);
    }

    /****************************************************************
     * TECHNICIAN SPECIFIC TESTS
     ****************************************************************/
    public function test_technician_can_view_products()
    {
        $technician = User::factory()->create()->assignRole('technician');
        Passport::actingAs($technician);

        $response = $this->getJson("/api/technician/assigned_products");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [],
            ]);
    }

    public function test_technician_can_update_maintenance_records()
    {
        $technician = User::factory()->create();
        $technician->assignRole('technician');

        // Creat a product and assign it to the technician
        $product = \App\Models\Product::factory()->create([
            'assigned_to' => $technician->id,
            'status'      => 'Needs Maintenance',
        ]);

        // Create a maintenance record assigned to this technician
        $record = \App\Models\MaintenanceRecord::factory()->create([
            'product_id'    => $product->id,
            'technician_id' => $technician->id,
        ]);

        Passport::actingAs($technician);

        $response = $this->putJson("/api/technician/maintenance_records/{$record->id}", [
            'notes'  => 'Replaced valve',
            'status' => 'completed',
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['notes' => 'Replaced valve']);

    }

    public function test_technician_see_only_assigned_records()
    {
        // Create and prepare technician
        $technician = User::factory()->create(['role' => 'technician']);
        $technician->assignRole('technician');

        //Create test data
        $product        = Product::factory()->create();
        $assignedRecord = MaintenanceRecord::factory()->create([
            'technician_id' => $technician->id,
            'product_id'    => $product->id,
        ]);

        // Create unassigned record
        MaintenanceRecord::factory()->create();

        // Generate valid token
        $token = $technician->createToken('test-token')->accessToken;

        // Make authenticated request
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept'        => 'application/json',
        ])->getJson('/api/technician/maintenance_records');

        // Make request and assert
        $this->actingAs($technician)
            ->getJson('/api/technician/maintenance_records')
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'product_id',
                        'technician_id',
                        'maintenance_date',
                        'status',
                        'notes',
                        'created_at',
                        'updated_at',
                        'product' => [
                            'id',
                            'user_id',
                            'name',
                            'type',
                            'type_capacity',
                            'serial_number',
                            'status',
                            'location',
                            'assigned_to',
                            'created_at',
                            'updated_at',
                        ],
                    ],
                ],
            ])
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.technician_id', $technician->id);
    }

    public function test_technician_cannot_update_others_records(): void
    {
        $tech1 = User::factory()->create(['role' => 'technician']);
        $tech2 = User::factory()->create(['role' => 'technician']);

        $record = MaintenanceRecord::factory()->create([
            'technician_id' => $tech2->id,
        ]);

        $this->actingAs($tech1, 'api')
            ->putJson("/api/technician/maintenance_records/{$record->id}", [
                'status' => 'completed',
            ])
            ->assertStatus(403);
    }

    public function test_that_a_technician_can_view_products()
    {
        $technician = User::factory()->create()->assignRole('technician');
        Passport::actingAs($technician);

        $response = $this->getJson("/api/technician/assigned_products");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [],
            ]);
    }

    public function test_that_a_technician_can_update_maintenance_records()
    {
        $technician = User::factory()->create();
        $technician->assignRole('technician');

        // Creat a product and assign it to the technician
        $product = \App\Models\Product::factory()->create([
            'assigned_to' => $technician->id,
            'status'      => 'Needs Maintenance',
        ]);

        // Create a maintenance record assigned to this technician
        $record = \App\Models\MaintenanceRecord::factory()->create([
            'product_id'    => $product->id,
            'technician_id' => $technician->id,
        ]);

        Passport::actingAs($technician);

        $response = $this->putJson("/api/technician/maintenance_records/{$record->id}", [
            'notes'  => 'Replaced valve',
            'status' => 'completed',
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['notes' => 'Replaced valve']);

    }
}
