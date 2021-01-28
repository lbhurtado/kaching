<?php

namespace App\Actions\Wallet;

use Bavix\Wallet\Models\Transaction;
use App\Http\Resources\DepositResource;
use Illuminate\Http\Resources\Json\JsonResource;

class DepositCredits extends CreditCredits
{
    protected $action = 'deposit';

    public function jsonResponse(Transaction $transaction): JsonResource
    {
        return new DepositResource($transaction);
    }
}
