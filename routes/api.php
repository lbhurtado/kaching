<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Actions\Wallet\{TransferCredits, DepositCredits, WithdrawCredits, ConfirmTransaction, RevealBalance, CreateUserToken};

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware(['auth:sanctum'])->prefix('transact')->group(function() {
    Route::get(config('kaching.keywords.transactions.balance'), RevealBalance::class);
    Route::get('balances', \App\Actions\Wallet\RevealBalances::class);
    Route::post(config('kaching.keywords.transactions.transfer'), TransferCredits::class);
    Route::post(config('kaching.keywords.transactions.deposit'), DepositCredits::class);
    Route::post(config('kaching.keywords.transactions.withdraw'), WithdrawCredits::class);
    Route::post(config('kaching.keywords.transactions.confirm'), ConfirmTransaction::class);
});

Route::post('/token', CreateUserToken::class);
