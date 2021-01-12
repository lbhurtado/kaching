<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::prefix('transact')->group(function() {
    Route::post('{mobile}/{action}/{amount}', [\App\Http\Controllers\TransactController::class, 'transfer'])
        ->where('action', 'deposit|withdraw')
        ->where('mobile', '^(09|\+?639)\d{9}$')
        ->where('amount', '[0-9]+');

    Route::get('{mobile}/{action}', [\App\Http\Controllers\TransactController::class, 'balance'])
        ->where('action', 'balance')
        ->where('mobile', '^(09|\+?639)\d{9}$');
});
