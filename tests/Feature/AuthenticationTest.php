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

        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('Password123!'),
            'role' => 'customer'      
        ]);
    
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'Password123!'
        ]);
    
        $response->assertStatus(200)
        ->assertJson(['message' => 'logged in successfully']);
        $this->assertAuthenticatedAs($user);
    }



    public function test_login_fails_with_invalid_password()
    {
    $user = User::factory()->create();

    $response = $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'wrong-password'
    ]);

    $response->assertStatus(401)
    ->assertJson(['message' => 'invalid entry']);
    }

}
?>