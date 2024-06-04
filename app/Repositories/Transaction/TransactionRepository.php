<?php

namespace App\Repositories\Transaction;

use App\Exceptions\InvalidDataProviderException;
use App\Models\Merchant;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;

class TransactionRepository
{
    public function handle(array $data): array
    {
        $model = $this->getProvider($data['provider']);
        $user = $model->findOrFail($data['payee_id']);
        return [];
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
}
