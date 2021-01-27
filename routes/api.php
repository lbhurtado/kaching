<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{TransactController};
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
    Route::post('{action}/{from}/{to}/{amount}/{wallet?}', [TransactController::class, 'transfer'])
        ->where('action', 'transfer')
        ->where('from', '^(09|\+?639)\d{9}$')
        ->where('to', '^(09|\+?639)\d{9}$')
        ->where('amount', '[0-9]+')
    ;

    Route::post('{action}/{mobile}/{amount}/{wallet?}', [TransactController::class, 'credit'])
        ->where('action', 'deposit|withdraw')
        ->where('mobile', '^(09|\+?639)\d{9}$')
        ->where('amount', '[0-9]+');

    Route::post('{action}', \App\Actions\Wallet\ConfirmTransaction::class)
        ->where('action', config('kaching.keywords.transactions.confirm'));

    Route::get('{action}', \App\Actions\Wallet\RevealBalance::class)
        ->where('action', 'balance');
});

Route::post('/token', \App\Actions\Wallet\CreateUserToken::class);
