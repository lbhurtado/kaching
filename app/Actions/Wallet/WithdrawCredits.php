<?php

namespace App\Actions\Wallet;

use Bavix\Wallet\Models\Transaction;
use App\Http\Resources\WithdrawResource;
use Illuminate\Http\Resources\Json\JsonResource;

class WithdrawCredits extends CreditCredits
{
    protected $action = 'withdraw';

    public function jsonResponse(Transaction $transaction): JsonResource
    {
        return new WithdrawResource($transaction);
    }
}
