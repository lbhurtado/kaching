<?php

namespace App\CommandBus\Handlers;

use App\CommandBus\Commands\BetCommand;

class BetHandler
{
    /**
     * @param BetCommand $command
     */
    public function handle(BetCommand $command)
    {
        $origin = $command->origin->getWallet('default');

        //TODO: this is just a template. Not yet working!!! Implement the everything
    }
}
