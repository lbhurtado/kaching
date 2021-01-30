<?php

$router = resolve('missive:router');

$router->register("BAL", \App\CommandBus\BalanceAction::class);
$router->register("GIVE {mobile} {amount}", \App\CommandBus\GiveAction::class);
$router->register("TIP {amount} {message}", \App\CommandBus\TipAction::class);
$router->register("ASK {mobile} {amount} {message}", \App\CommandBus\AskAction::class);
$router->register("BET {game} {prediction} {amount}", \App\CommandBus\BetAction::class);
