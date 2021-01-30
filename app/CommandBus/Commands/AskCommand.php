<?php

namespace App\CommandBus\Commands;

use App\Models\Contact;

class AskCommand extends BaseCommand
{
    /** @var Contact */
    public $origin;

    /** @var string */
    public $mobile;

    /** @var int */
    public $amount;

    /** @var string */
    public $message;

    /**
     * CodesCommand constructor.
     *
     * @param Contact $origin
     * @param string $mobile
     * @param int $amount
     * @param string $message
     */
    public function __construct(Contact $origin, string $mobile, int $amount, string $message)
    {
        $this->origin = $origin;
        $this->mobile = $mobile;
        $this->amount = $amount;
        $this->message = $message;
    }

}
