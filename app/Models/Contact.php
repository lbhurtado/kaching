<?php

namespace App\Models;

use Bavix\Wallet\Traits\HasWallet;
use Bavix\Wallet\Interfaces\Wallet;
use LBHurtado\Missive\Models\Contact as BaseContact;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Contact extends BaseContact implements Wallet
{
    use HasFactory, HasWallet;

    public $casts = [
        'extra_attributes' => 'array',
        'balance' => 'int'
    ];

    /**
     * @param string $mobile
     * @return Contact|null
     */
    public static function bearing(string &$mobile):? Contact //TODO change this to by
    {
        $mobile = phone($mobile, config('kaching.country'))
            ->formatE164();

        return static::where('mobile', $mobile)->first();
    }
}
