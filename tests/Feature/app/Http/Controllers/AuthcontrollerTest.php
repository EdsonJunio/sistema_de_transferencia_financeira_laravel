<?php

namespace Tests\Feature\app\Http\Controllers;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class AuthcontrollerTest extends TestCase
{
    use DatabaseMigrations;

    public function testUserCannotAuthenticateWithTheWrongSupplier()
    {
        $payload = [
            'email' => 'test@test.com',
            'password' => 'password',
        ];
        $response = $this->post(route('authenticate', ['provider' => 'unknownProvider']), $payload);

        $response->assertStatus(422);
        $response->assertJson(['errors' => ['main' => 'Wrong provider']]);
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

}

