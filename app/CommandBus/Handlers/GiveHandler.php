<?php

namespace App\CommandBus\Handlers;

use App\Actions\Wallet\TransferCredits;
use App\CommandBus\Commands\GiveCommand;
use App\Actions\Wallet\InstantiateContact;

class GiveHandler
{
    /**
     * @param GiveCommand $command
     */
    public function handle(GiveCommand $command)
    {
        $origin = $command->origin->getWallet('default');
        $destination = InstantiateContact::run($command->mobile)->getWallet('default');

        TransferCredits::run(
            $origin,
            $destination,
            $command->amount
        );
    }
}
