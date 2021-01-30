<?php

namespace App\CommandBus\Handlers;

use App\Actions\Wallet\TransferCredits;
use App\CommandBus\Commands\AskCommand;
use App\Actions\Wallet\InstantiateContact;

class AskHandler
{
    /**
     * @param AskCommand $command
     */
    public function handle(AskCommand $command)
    {
        $destination = $command->origin->getWallet('default');
        $origin = InstantiateContact::run($command->mobile)->getWallet('default');

        TransferCredits::run(
            $origin,
            $destination,
            $command->amount
        );
        //TODO: this is just a template. Not yet working!!! Implement the confirmation then message
    }
}
