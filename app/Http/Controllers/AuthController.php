<?php

namespace App\Http\Controllers;

use App\Repositories\AuthRepository;
use App\Exceptions\InvalidDataProviderException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    private $repository;

    public function __construct(AuthRepository $authRepository)
    {
        $this->repository = $authRepository;
    }

    public function postAuthenticate(Request $request, string $provider)
    {
        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $fields = $request->only('email', 'password');

        try {
            $result = $this->repository->authenticate($provider, $fields);
            return response()->json($result);

        } catch (AuthenticationException $exception) {
            return response()->json(['errors' => ['main' => $exception->getMessage()]], 401);
        } catch (InvalidDataProviderException $exception) {
            return response()->json(['errors' => ['main' => $exception->getMessage()]], 422);
        } catch (\Exception $exception) {
            return response()->json(['errors' => ['main' => 'Erro interno do servidor']], 500);
        }
    }
}

