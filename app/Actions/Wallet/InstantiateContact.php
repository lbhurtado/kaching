<?php

namespace App\Actions\Wallet;

use App\Models\Contact;
use Lorisleiva\Actions\Concerns\AsAction;

class InstantiateContact
{
    use AsAction;

    public function handle(string $mobile): Contact
    {
        $mobile = phone($mobile, config('kaching.country'))
            ->formatE164();

        return Contact::firstOrCreate(compact('mobile'));
    }
}
