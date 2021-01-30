<?php

namespace App\CommandBus\Commands;

use App\Models\Contact;

class BalanceCommand extends BaseCommand
{
    /** @var Contact */
    public $origin;

    /**
     * CodesCommand constructor.
     *
     * @param Contact $origin
     */
    public function __construct(Contact $origin)
    {
        $this->origin = $origin;
    }

}
