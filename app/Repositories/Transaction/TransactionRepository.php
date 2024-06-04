<?php

namespace App\Repositories\Transaction;

use App\Exceptions\InvalidDataProviderException;
use App\Exceptions\NoMoneyAtTheMomentException;
use App\Exceptions\TransactionDeniedException;
use App\Models\Merchant;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;

class TransactionRepository
{
    public function handle(array $data): array
    {
        if (!$this->guardCanTransfer()) {
            throw new TransactionDeniedException('Merchant is not authorized to make transactions', 401);
        }

        $model = $this->getProvider($data['provider']);
        $user = $model->findOrFail($data['payee_id']);

        if (!$this->checkUserBalance($user, $data['amount'])) {
            throw new NoMoneyAtTheMomentException('No money at the moment', 422);
        }

        return [];
    }


    public function guardCanTransfer(): bool
    {
        if (Auth::guard('users')->check()) {
            return true;
        } elseif (Auth::guard('merchant')->check()) {
            return false;
        } else {
            throw new InvalidDataProviderException('Invalid provider');
        }
    }


    public function getProvider(string $provider): Authenticatable
    {
        if ($provider == 'user') {
            return new User();
        } else if ($provider == 'merchant') {
            return new Merchant();
        } else {
            throw new InvalidDataProviderException('Invalid provider');
        }
    }

    private function checkUserBalance($user, $money)
    {
        return $user->wallet->balance >= $money;
    }
}
