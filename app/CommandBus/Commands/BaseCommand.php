<?php

namespace App\CommandBus\Commands;

abstract class BaseCommand
{
    public function __toString()
    {
        return json_encode((array) $this);
    }
}
