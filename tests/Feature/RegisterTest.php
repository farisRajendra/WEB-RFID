<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_page_can_be_accessed()
    {
        $response = $this->get('/register');
        $response->assertSee('Register'); 
    }

    public function test_user_can_register()
    {
        $response = $this->post('/register', [
            'name' => 'Tes User',
            'email' => 'tesuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('users', [
            'email' => 'tesuser@example.com',
        ]);

        $this->assertAuthenticated();
    }

    public function test_register_validation_error()
    {
        $response = $this->post('/register', [
            'name' => '', 
            'email' => 'notanemail',
            'password' => '123',
            'password_confirmation' => 'different',
        ]);

        $response->assertSessionHasErrors(['name', 'email', 'password']);
    }
}
