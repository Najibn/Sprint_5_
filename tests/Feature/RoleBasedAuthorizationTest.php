<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Laravel\Passport\Passport;
use Database\Seeders\PassportSeeder;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RoleBasedAuthorizationTest extends TestCase
{

    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Set default guard to API for tests
        config(['auth.defaults.guard' => 'api']);


         // Run migrations and seed test data
        $this->artisan('migrate:fresh');
        $this->seed([
            \Database\Seeders\PassportSeeder::class,
            \Database\Seeders\RolesAndPermissionsSeeder::class,
            \Database\Seeders\TestingSeeder::class,
        ]);
    }


    //tests series foor admin
    public function test_admin_can_view_all_users()
    {
        // Get the pre-seeded admin
        $admin = User::where('email', 'admin@test.com')->first();
        Passport::actingAs($admin);
         
    // Debug middleware
    $middleware = app('router')->getRoutes()->getByName('users.index')->gatherMiddleware();
    
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
