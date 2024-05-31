<?php

namespace App\Http\Controllers;

use App\Models\Merchant;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function postAuthenticate(Request $request, string $provider)
    {

        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required'
        ]);


        $providers = ['user', 'merchant'];

        if (!in_array($provider, $providers)) {
            return response()->json(['errors' => ['main' => 'Wrong provider']], 422);
        }

        $provider = $this->getProvider($provider);

        $model = $provider->where('email', '=', $request->input('email'))->first();

        if(!$model) {
            return response()->json(['errors' => ['main' => 'Wrong credentials']], 401);
        }

        return 'O response escolhido é ' . $provider;
    }

    public function getProvider(string $provider): Authenticatable
    {
        if ($provider == 'user') {
            return new User();
        }else if ($provider == 'merchant') {
            return new Merchant();
        } else {
            throw new \InvalidArgumentException('Invalid provider');
        }
    }
}
