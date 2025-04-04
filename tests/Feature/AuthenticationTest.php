<?php
namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Database\Factories\UserFactory;
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
        ->assertJson(['message' => 'Logged in successfully']);
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
    ->assertJson(['message' => 'Invalid Entry']);
    }


    //user logout testting 
    public function test_user_can_logout(){

        //authenticat and create the user
        $user = User::factory()->create();
        $this->actingAs($user);

        //try to logout
        $response = $this->postJson('/api/logout');

        //we assert
        $response -> assertStatus(200)->assertJson(['message' => 'successfully logged out']);
        
    }


    public function test_requiring_all_user_fields(){

        $response = $this->postJson('/api/register', []);

        $response ->assertStatus(422)-> assertJsonValidationErrors([
            'name', 'email', 'password', 'role'
        ]);
    }

    //verifying users email must be unique 
    public function test_user_email_uniqueness(){

        //$this->withoutMiddleware(); 

        User::factory()->create(['email'=> 'fakeemail@fske.com']);

        $response = $this->postJson('/api/register', [

            'name'                  => 'Test User',
            'email'                 => 'fakeemail@fske.com', // the fake  email
            'password'              => 'Password123!',
            'password_confirmation' => 'Password123!',
            'role'                  => 'customer'

        ]);
        $response -> assertStatus(422)->assertJsonValidationErrors(['email']);

    }

   //verifying user roles
    public function test_role_must_be_valid_enum_value()
    {

    // adding invalid role check
    $invalidData = [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
        'role' => 'invalid_role_value'                  
    ];
    
    $response = $this->postJson('/api/register', $invalidData);
    
    $response->assertStatus(422)     
             ->assertJsonValidationErrors(['role']);
    }



}
?>