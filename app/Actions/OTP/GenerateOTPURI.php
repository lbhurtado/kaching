<?php

namespace App\Actions\OTP;

use OTPHP\TOTP;
use Lorisleiva\Actions\Concerns\AsAction;

class GenerateOTPURI
{
    use AsAction;

    public function handle(int $period = 600): string
    {
        return tap(TOTP::create(null, $period), function ($totp) {
            $totp->setLabel(config('kaching.label.otp'));
        })->getProvisioningUri();
    }
}
