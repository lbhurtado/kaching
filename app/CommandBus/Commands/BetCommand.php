<?php

namespace App\CommandBus\Commands;

use App\Models\Contact;

class BetCommand extends BaseCommand
{
    /** @var Contact */
    public $origin;

    /** @var string */
    public $game;

    /** @var string */
    public $prediction;

    /** @var int */
    public $amount;

    /**
     * CodesCommand constructor.
     *
     * @param Contact $origin
     * @param string $game
     * @param string $prediction
     * @param int $amount

     */
    public function __construct(Contact $origin, string $game, string $prediction, int $amount)
    {
        $this->origin = $origin;
        $this->game = $game;
        $this->prediction = $prediction;
        $this->amount = $amount;
    }

}
