<?php

namespace Tests\Feature\app\Http\Controllers;

use App\Models\User;
use Tests\TestCase;

class AuthcontrollerTest extends TestCase
{

    public function testUserCannotAuthenticateWithTheWrongSupplier()
    {
        $payload = [
            'email' => 'test@test.com',
            'password' => 'testepassword',
        ];
        $response = $this->post(route('authenticate', ['provider' => 'unknownProvider']), $payload);

        $response->assertStatus(422);
        $response->assertJson(['errors' => ['main' => 'Invalid provider']]);
    }

    public function testUserShouldBeDeniedIfNotRegistered()
    {
        $payload = [
            'email' => 'test@test.com',
            'password' => 'password',
        ];
        $response = $this->post(route('authenticate', ['provider' => 'merchant']), $payload);
        $response->assertStatus(401);
        $response->assertJson(['errors' => ['main' => 'Wrong credentials']]);
    }

    public function testUserShouldSendWrongPassword()
    {
        $user = User::factory()->create();
        $payload = [
            'email' => $user->email,
            'password' => 'testepassword',
        ];
        $response = $this->post(route('authenticate', ['provider' => 'user']), $payload);
        $response->assertStatus(401);
        $response->assertJson(['errors' => ['main' => 'Wrong credentials']]);
    }

    public function testUserCanAuthenticateWithCorrectCredentials()
    {
        $user = User::factory()->create();
        $payload = [
            'email' => $user->email,
            'password' => 'password123',
        ];
        $response = $this->post(route('authenticate', ['provider' => 'user']), $payload);
        $response->assertStatus(200);
        $response->assertJsonStructure(['token', 'provider', 'expires_at']);
    }
}

