<?php

namespace App\CommandBus;

use App\CommandBus\Commands\BalanceCommand;
use App\CommandBus\Handlers\BalanceHandler;

class BalanceAction extends TemplateAction
{
    protected $permission = 'send message';

    protected $command = BalanceCommand::class;

    protected $handler = BalanceHandler::class;
}
