<?php

namespace App\Models;

use Bavix\Wallet\Traits\HasWallet;
use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Traits\CanConfirm;
use Bavix\Wallet\Traits\HasWallets;
use Bavix\Wallet\Models\Transaction;
use App\Jobs\ProvisionURIToTransaction;
use Bavix\Wallet\Interfaces\Confirmable;
use Illuminate\Notifications\Notifiable;
use LBHurtado\Missive\Models\Contact as BaseContact;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Contact extends BaseContact implements Wallet, Confirmable
{
    use HasFactory, HasWallet, HasWallets, CanConfirm, Notifiable;

    /**
     * @param string $mobile
     * @return Contact
     */
    public static function bearing(string &$mobile): Contact
    {
        $mobile = phone($mobile, config('kaching.country'))
            ->formatE164();

        return static::firstOrCreate(compact('mobile'));
    }

    public function credit($amount, $wallet = 'default', $action = 'deposit',  bool $confirmed = false, ?array $meta = []): Transaction
    {
        ProvisionURIToTransaction::dispatch($transaction = $this->getWallet($wallet)->{$action}($amount, $meta, $confirmed));

        return $transaction;
    }
}
