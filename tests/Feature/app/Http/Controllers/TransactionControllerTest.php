<?php

namespace Tests\Feature\app\Http\Controllers;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Contracts\Console\Kernel;

class TransactionControllerTest extends TestCase
{


    public function createApplication()
    {
        $app = require './bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        return $app;
    }

    public function setUp(): void
    {
        parent::setUp();

    }

    public function testUserShouldNotSendWrongProvider()
    {
        $user = User::factory()->create();
        $payload = [
            'provider' => 'ddddd',
            'payee_id' => 123,
            'amount' => 100,
        ];

        $this->actingAs($user, 'users');

        $response = $this->postJson(route('postTransaction'), $payload);

        $response->assertStatus(422);
    }

    public function testUserShouldBeExistingOnProviderToTransfer()
    {

        $user = User::factory()->create();
        $payload = [
            'provider' => 'user',
            'payee_id' => 123,
            'amount' => 100,
        ];
        $request = $this->actingAs($user, 'users')
            ->post(route('postTransaction'), $payload);
        $request->assertStatus(404);
    }


}
