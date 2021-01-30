<?php

namespace App\CommandBus;

use App\CommandBus\Commands\AskCommand;
use App\CommandBus\Handlers\AskHandler;

class AskAction extends TemplateAction
{
    protected $permission = 'send message';

    protected $command = AskCommand::class;

    protected $handler = AskHandler::class;
}
