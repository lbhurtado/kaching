<?php

namespace App\CommandBus\Commands;

use App\Models\Contact;

class TipCommand extends BaseCommand
{
    /** @var Contact */
    public $origin;

    /** @var int */
    public $amount;

    /** @var string */
    public $message;

    /**
     * CodesCommand constructor.
     *
     * @param Contact $origin
     * @param int $amount
     * @param string $message
     */
    public function __construct(Contact $origin, int $amount, string $message)
    {
        $this->origin = $origin;
        $this->amount = $amount;
        $this->message = $message;
    }

}
