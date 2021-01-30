<?php

namespace App\CommandBus;

use App\CommandBus\Commands\TipCommand;
use App\CommandBus\Handlers\TipHandler;

class TipAction extends TemplateAction
{
    protected $permission = 'send message';

    protected $command = TipCommand::class;

    protected $handler = TipHandler::class;
}
