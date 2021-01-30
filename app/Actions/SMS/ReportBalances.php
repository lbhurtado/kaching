<?php

namespace App\Actions\SMS;

use App\Models\Contact;
use App\Notifications\Balance;
use App\Actions\Wallet\RevealBalances;
use Lorisleiva\Actions\Concerns\AsAction;

class ReportBalances
{
    use AsAction;

    public function handle(Contact $contact)
    {
        $contact->notify(new Balance($this->getBalances($contact)));
    }

    protected function getBalances(Contact $contact): string
    {
        return RevealBalances::run($contact)->toJson();
    }
}
