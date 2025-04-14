<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Laravel\Passport\Passport;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Client as PassportClient;
use Laravel\Passport\Client;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Run the seeders to set up Passport clients and roles/permissions
        $this->seed(\Database\Seeders\PassportSeeder::class);
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    }


    public function test_user_can_register(): void
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
            'role' => 'customer',
            'phone' => '1234567890'
        ];

        $response = $this->postJson('/api/register', $userData);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'status',
                     'message',
                     'data' => [
                         'id',
                         'name',
                         'email',
                         'role',
                         'phone',
                         'created_at',
                         'updated_at'
                     ]
                 ])
                 ->assertJson([
                     'status' => true,
                     'message' => 'User Registered Successfully'
                 ]);
                 
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'role' => 'customer'
        ]);
    }

    public function test_user_can_login()
    {
        // Create a user
        $user = User::factory()->create([
            'email' => 'login@example.com',
            'password' => bcrypt('Password123'),
            'role' => 'customer'
        ]);

        // Assign role to user
        $user->assignRole('customer');

        $loginData = [
            'email' => 'login@example.com',
            'password' => 'Password123'
        ];

        $response = $this->postJson('/api/login', $loginData);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'status',
                     'message',
                     'user',
                     'token'
                 ])
                 ->assertJson([
                     'status' => true,
                     'message' => 'User Logged In Successfully'
                 ]);
    }


    public function test_user_can_refresh_token(): void
    {
        // Create a user
        $user = User::factory()->create();
        $user->assignRole('customer');

        // Generate an access token for the user
        $token = $user->createToken("Auth API")->accessToken;
        
        // Acting as the user with an active token
        Passport::actingAs($user);

       // Call the refresh token endpoint
        $response = $this->postJson('/api/refreshToken', [], [
            'Authorization' => "Bearer {$token}"
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'status',
                     'message',
                     'token'
                 ])
                 ->assertJson([
                     'status' => true,
                     'message' => 'Token Re-issued Successfully'
                 ]);
    }



    public function test_user_can_logout(): void
    {
        // Create a user
        $user = User::factory()->create();
        $user->assignRole('customer');


        // Generate an access token for the user
        $token = $user->createToken("Auth API")->accessToken;

        // Act as the user
        Passport::actingAs($user);

        $response = $this->postJson('/api/logout', [], [
            'Authorization' => "Bearer {$token}"
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => true,
                     'message' => 'User Logged Out Successfully'
                 ]);
    }
    



    public function test_user_cannot_register_with_a_duplicate_email(): void
    {
        // Create a user first
        User::factory()->create([
            'email' => 'duplicate@example.com'
        ]);

        $userData = [
            'name' => 'Duplicate User',
            'email' => 'duplicate@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
            'role' => 'customer',
            'phone' => '1234567890'
        ];

        $response = $this->postJson('/api/register', $userData);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['email']);
    }



    public function test_user_cannot_login_with_invalid_credentials(): void
    {
        // Create a user
        $user = User::factory()->create([
            'email' => 'login@example.com',
            'password' => bcrypt('Password123')
        ]);

        $loginData = [
            'email' => 'login@example.com',
            'password' => 'WrongPassword123'
        ];

        $response = $this->postJson('/api/login', $loginData);

        $response->assertStatus(401)
                 ->assertJson([
                     'status' => false,
                     'message' => 'Invalid credentials'
                 ]);
    }



    public function test_login_fails_with_invalid_password(): void
    {
    // Create a user
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('correct-password'),
    ]);

    // Attempt to log in with an invalid password
    $response = $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    // Assert that the response is a 401 Unauthorized
    $response->assertStatus(401)
             ->assertJson([
                 'status' => false,
                 'message' => 'Invalid credentials',
             ]);
    }


    public function test_requiring_all_user_fields(): void
    {
    // Attempting to create a user with missing fields
    $response = $this->postJson('/api/register', [
        // Intentionally leave out required fields -> name, email, password, etc.
    ]);

    // Assert validation errors for each missing field
    $response->assertStatus(422)
             ->assertJsonValidationErrors(['name', 'email', 'password', 'role']);
    }



    public function test_user_email_uniqueness(): void
    {
    // Create a user with a specific email
    User::factory()->create([
        'email' => 'unique@example.com',
    ]);

    // Attempt to create another user with the same email
    $response = $this->postJson('/api/register', [
        'name' => 'Test User',
        'email' => 'unique@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'customer',
    ]);

    // Assert validation error for the email field
    $response->assertStatus(422)
             ->assertJsonValidationErrors(['email']);
    }

    public function test_role_must_be_valid_enum_value(): void
    {
    // Attemptin to create a user with an invalid role
    $response = $this->postJson('/api/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'invalid-role', // Invalid role value
    ]);

    // Asserting validation error for the role field
    $response->assertStatus(422)
             ->assertJsonValidationErrors(['role']);
    }


}
