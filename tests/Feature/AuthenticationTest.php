<?php
namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Laravel\Passport\Passport;
use Illuminate\Foundation\Testing\RefreshDatabase;



class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate:fresh');
    }
    
    public function test_user_can_register()
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'role' => 'customer',
            'phone' => '1234567890'
        ]);
    
        $response->assertStatus(201);
        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
    }


    public function test_user_can_login()
    {
    // Create a test user directly
    $user = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('Password123!'),
        'role' => 'customer',
        'phone' => '1234567890'
    ]);

    // Attempt login
    $response = $this->postJson('/api/login', [
        'email' => 'test@example.com',
        'password' => 'Password123!'
    ]);

    // Basic response assertion
    $response->assertStatus(200);
    }



}

?>