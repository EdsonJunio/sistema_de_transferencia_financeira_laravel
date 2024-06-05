<?php

namespace Tests\Feature\app\Http\Controllers;

use App\Events\SendNotification;
use App\Models\Merchant;
use App\Models\User;
use App\Services\MockyService;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Event;

class TransactionControllerTest extends TestCase
{
    public function createApplication()
    {
        $app = require './bootstrap/app.php';
        $app->make(Kernel::class)->bootstrap();
        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();
    }

    private function createPayload(array $overrides = []): array
    {
        return array_merge([
            'provider' => 'users',
            'payee_id' => 123,
            'amount' => 100,
        ], $overrides);
    }

    private function actingAsUser($user, string $guard = 'users')
    {
        return $this->actingAs($user, $guard);
    }

    public function testUserShouldNotSendWrongProvider()
    {
        $user = User::factory()->create();
        $payload = $this->createPayload(['provider' => 'ddddd']);

        $response = $this->actingAsUser($user)->postJson(route('postTransaction'), $payload);

        $response->assertStatus(422);
    }

    public function testUserShouldBeExistingOnProviderToTransfer()
    {
        $user = User::factory()->create();
        $payload = $this->createPayload();

        $response = $this->actingAsUser($user)->post(route('postTransaction'), $payload);

        $response->assertStatus(422);
    }

    public function testUserShouldBeAValidUserToTransfer()
    {
        $user = User::factory()->create();
        $payload = $this->createPayload();

        $response = $this->actingAsUser($user)->post(route('postTransaction'), $payload);

        $response->assertStatus(422);
    }

    public function testMerchantShouldNotTransfer()
    {
        $merchant = Merchant::factory()->create();
        $payload = $this->createPayload();

        $response = $this->actingAsUser($merchant, 'merchants')->post(route('postTransaction'), $payload);

        $response->assertStatus(401);
    }

    public function testUserCanTransferMoney()
    {
        Event::fake();

        $this->mock(MockyService::class, function ($mock) {
            $mock->shouldReceive('authorizetTransaction')
                ->once()
                ->andReturn(['message' => 'Autorizado']);
        });

        $userPayer = User::factory()->create();
        $userPayer->wallet()->create(['balance' => 1000]);

        $userPayee = User::factory()->create();
        $userPayee->wallet()->create(['balance' => 0]);

        $payload = $this->createPayload(['payee_id' => $userPayee->id, 'amount' => 100]);

        $response = $this->actingAsUser($userPayer)->post(route('postTransaction'), $payload);

        $response->assertStatus(200);

        Event::assertDispatched(SendNotification::class);

        $this->assertDatabaseHas('wallets', [
            'id' => $userPayer->wallet->id,
            'balance' => 900,
        ]);

        $this->assertDatabaseHas('wallets', [
            'id' => $userPayee->wallet->id,
            'balance' => 100,
        ]);

        $this->assertDatabaseHas('wallet_transactions', [
            'payer_wallet_id' => $userPayer->wallet->id,
            'payee_wallet_id' => $userPayee->wallet->id,
            'amount' => 100,
        ]);
    }
}
