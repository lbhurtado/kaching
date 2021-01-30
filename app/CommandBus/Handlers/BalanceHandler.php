<?php

namespace App\CommandBus\Handlers;

use App\Actions\SMS\ReportBalances;
use App\CommandBus\Commands\BalanceCommand;

class BalanceHandler
{
    /**
     * @param BalanceCommand $command
     */
    public function handle(BalanceCommand $command)
    {
        ReportBalances::dispatch($command->origin);
    }
}
