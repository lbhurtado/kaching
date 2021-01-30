<?php

namespace App\CommandBus;

use App\CommandBus\Commands\GiveCommand;
use App\CommandBus\Handlers\GiveHandler;

class GiveAction extends TemplateAction
{
    protected $permission = 'send message';

    protected $command = GiveCommand::class;

    protected $handler = GiveHandler::class;
}
