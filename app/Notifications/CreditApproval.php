<?php

namespace App\Notifications;

use OTPHP\Factory;
use OTPHP\TOTPInterface;
use Bavix\Wallet\Models\Transaction;
use Illuminate\Contracts\Queue\ShouldQueue;
use LBHurtado\EngageSpark\Notifications\BaseNotification;

class CreditApproval extends BaseNotification implements ShouldQueue
{
    /**
     * @var Transaction
     */
    protected $transaction;

    public function __construct(Transaction $transaction, $message = null)
    {
        parent::__construct($message);

        $this->transaction = $transaction;
    }

    public function getContent($notifiable)
    {
        return static::getFormattedMessage($notifiable, $this->getTOTP()->now());
    }

    public static function getFormattedMessage($notifiable, $message)
    {
        $handle = $notifiable->handle ?? $notifiable->mobile;
        $signature = config('kaching.signature');
        $otp = $message;

        return trans('kaching.verify', compact('handle', 'otp', 'signature'));
    }

    protected function getTOTP(): TOTPInterface
    {
        return Factory::loadFromProvisioningUri($this->transaction->meta['otp_uri']);
    }
}
