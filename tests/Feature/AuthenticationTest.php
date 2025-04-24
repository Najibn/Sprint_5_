<?php
namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Laravel\Passport\Passport;
use Database\Factories\UserFactory;
use Database\Seeders\PassportSeeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseTransactions;


class AuthenticationTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate:fresh');

        $this->seed(PassportSeeder::class); // Explicitly seedign Passport clients
    }
    
    public function test_user_can_register()
    {
        $response = $this->postJson('/api/register', [
            'name'      => 'Test User',
            'email'         => 'test@example.com',
            'password'              => 'Password123!',
            'password_confirmation' => 'Password123!',
            'role'          => 'customer',
            'phone'     => '1234567890'
        ]);
    
        $response->assertStatus(200);
        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
    }


    public function test_user_can_login()
    {

        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => 'Password123!' // Let model's mutator handle hashing no bcrypt
        ]);
    
        // DEBUG: Check if password was hashed correctly
     /*   dd([
            'input_password' => 'Password123!',
            'hashed_password_in_db' => $user->password,
            'password_matches' => Hash::check('Password123!', $user->password)
        ]);
    */
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'Password123!'
        ]);
    
        $response->assertStatus(200);
        
    }



    public function test_login_fails_with_invalid_password()
    {
    $user = User::factory()->create();

    $response = $this->postJson('/api/login', [
        'email'    => $user->email,
        'password' => 'wrong-password'
    ]);

    $response->assertStatus(401)
    ->assertJson(['message' => 'Invalid Entry']);
    }


    //user logout testting 
    public function test_user_can_logout(){

        //authenticat and create the user
        $user = User::factory()->create();
        Passport::actingAs($user); 

        //try to logout
        $response = $this->postJson('/api/logout');

        //we assert
        $response -> assertStatus(200)->assertJson(['message' => 'User Logged Out Successfully',
        'status' => true
    ]);
        
    }


    public function test_requiring_all_user_fields(){

        $response = $this->postJson('/api/register', []);

        $response ->assertStatus(422)-> assertJsonValidationErrors([
            'name', 
            'email', 
            'password', 
            'role'
        ]);
    }

    //verifying users email must be unique 
    public function test_user_email_uniqueness(){

        //$this->withoutMiddleware(); 

        User::factory()->create(['email'=> 'fakeemail@fske.com']);

        $response = $this->postJson('/api/register', [

            'name'                  => 'Test User',
            'email'                   => 'fakeemail@fske.com', // the fake  email
            'password'                 => 'Password123!',
            'password_confirmation'   => 'Password123!',
            'role'                  => 'customer'

        ]);
        $response -> assertStatus(422)->assertJsonValidationErrors(['email']);

    }

   //verifying user roles
    public function test_role_must_be_valid_enum_value()
    {

    // adding invalid role check
    $invalidData = [
        'name'            => 'Test User',
        'email'             => 'test@example.com',
        'password'            => 'Password123!',
        'password_confirmation' => 'Password123!',
        'role'                    => 'invalid_role_value'                  
    ];
    
    $response = $this->postJson('/api/register', $invalidData);
    
    $response->assertStatus(422)     
             ->assertJsonValidationErrors(['role']);
    }



}
?>