<?php

namespace App\Repositories;

use App\Models\Merchant;
use App\Models\User;
use App\Exceptions\InvalidDataProviderException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Hash;

class AuthRepository
{
    public function authenticate(string $provider, array $fields)
    {

        $provider = $this->getProvider($provider);
        $model = $provider->where('email', $fields['email'])->first();

        if (!$model) {
            throw new AuthenticationException('Wrong credentials');
        }

        if (!Hash::check($fields['password'], $model->password)) {
            throw new AuthenticationException('Wrong credentials');
        }

        $token = $model->createToken($provider);

        return [
            'token' => $token,
            'provider' => $provider,
            'expires_at' => $token
        ];
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

