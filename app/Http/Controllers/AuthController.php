<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function postAuthenticate(string $response)
    {
        return response('O reponse escolhido é ' . $response . 200);
    }
}
