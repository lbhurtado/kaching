<?php

namespace App\CommandBus\Middlewares;

use Log;
use League\Tactician\Middleware;

class LogMiddleware implements Middleware
{
    public function execute($command, callable $next)
    {
        Log::info("*********** START ***********");
        Log::info($command);
        Log::info("************ END ************");

        $next($command);
    }
}
