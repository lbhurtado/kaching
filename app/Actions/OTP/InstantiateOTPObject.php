<?php

namespace App\Actions\OTP;

use OTPHP\Factory;
use OTPHP\OTPInterface;
use Bavix\Wallet\Models\Transaction;
use Lorisleiva\Actions\Concerns\AsAction;

class InstantiateOTPObject
{
    use AsAction;

    public function handle(Transaction $transaction): OTPInterface
    {
        return Factory::loadFromProvisioningUri($transaction->meta['otp_uri']);
    }
}
