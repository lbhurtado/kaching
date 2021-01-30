<?php

namespace App\CommandBus\Handlers;

use App\Actions\Wallet\TransferCredits;
use App\CommandBus\Commands\TipCommand;
use App\Actions\Wallet\InstantiateContact;

class TipHandler
{
    /**
     * @param TipCommand $command
     */
    public function handle(TipCommand $command)
    {
        $origin = $command->origin->getWallet('default');
        $default_mobile = config('kaching.tip.mobile', '09173011987');
        $destination = InstantiateContact::run($default_mobile)->getWallet('default');

        TransferCredits::run(
            $origin,
            $destination,
            $command->amount
        );
        //TODO: this is just a template. Not yet working!!! Implement the message
    }
}
