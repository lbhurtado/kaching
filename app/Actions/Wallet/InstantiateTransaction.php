<?php

namespace App\Actions\Wallet;

use Bavix\Wallet\Models\Transaction;
use Lorisleiva\Actions\Concerns\AsAction;

class InstantiateTransaction
{
    use AsAction;

    public function handle(string $uuid): Transaction
    {
        return Transaction::where('uuid', $uuid)->firstOrFail();
    }
}
