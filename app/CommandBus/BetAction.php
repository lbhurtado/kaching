<?php

namespace App\CommandBus;

use App\CommandBus\Commands\BetCommand;
use App\CommandBus\Handlers\BetHandler;

class BetAction extends TemplateAction
{
    protected $permission = 'send message';

    protected $command = BetCommand::class;

    protected $handler = BetHandler::class;
}
