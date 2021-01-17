<?php

namespace App\Models;

use Bavix\Wallet\Traits\HasWallet;
use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Traits\CanConfirm;
use Bavix\Wallet\Traits\HasWallets;
use Bavix\Wallet\Interfaces\Confirmable;
use LBHurtado\Missive\Models\Contact as BaseContact;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Contact extends BaseContact implements Wallet, Confirmable
{
    use HasFactory, HasWallet, HasWallets, CanConfirm;

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
}
