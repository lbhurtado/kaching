<?php

namespace App\CommandBus\Commands;

use App\Models\Contact;

class GiveCommand extends BaseCommand
{
    /** @var Contact */
    public $origin;

    /** @var string */
    public $mobile;

    /** @var int */
    public $amount;

    /**
     * CodesCommand constructor.
     *
     * @param Contact $origin
     * @param string $mobile
     * @param int $amount
     */
    public function __construct(Contact $origin, string $mobile, int $amount)
    {
        $this->origin = $origin;
        $this->mobile = $mobile;
        $this->amount = $amount;
    }

}
