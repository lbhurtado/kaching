<?php

namespace App\Models;

use Bavix\Wallet\Models\Wallet;
use App\Jobs\EmbedTransactionOTP;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Bavix\Wallet\Models\Transaction as BaseTransaction;

class Transaction extends BaseTransaction
{
    use HasFactory, DispatchesJobs;

    /**
     * @param Wallet $wallet
     * @param $amount
     * @param string $action
     * @param bool $confirmed
     * @param array|null $meta
     * @return mixed
     */
    public static function credit(Wallet $wallet, $amount, string $action = 'deposit', bool $confirmed = false, ?array $meta = [])
    {
        $transaction = $wallet->{$action}($amount, $meta, $confirmed);
        EmbedTransactionOTP::dispatch($transaction);

        return $transaction;
    }
}
