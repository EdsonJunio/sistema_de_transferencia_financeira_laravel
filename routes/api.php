<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\MeController;
use App\Http\Controllers\Transactions\TransactionsController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::get('/', function () {
    $user = User::factory()->create(['email' => 'teste@gmail.com']);
    return response()->json($user);
});

Route::post('/auth/{provider}', [AuthController::class, 'postAuthenticate'])->name('authenticate');

Route::middleware('auth:sanctum')->get('/users/me', [MeController::class, 'getMe']);

Route::post('/transactions', [TransactionsController::class, 'postTransaction'])->name('postTransaction');
