<?php

namespace App\Notifications;

use Bavix\Wallet\Models\Transaction;
use App\Actions\OTP\InstantiateOTPObject;
use Illuminate\Contracts\Queue\ShouldQueue;
use LBHurtado\EngageSpark\Notifications\BaseNotification;

class TransactionApproval extends BaseNotification implements ShouldQueue
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
        return static::getFormattedMessage($notifiable, $this->getData($notifiable));
    }

    public static function getFormattedMessage($notifiable, $message)
    {
        $data = json_decode($message, true);

        return trans('kaching.approval', $data);
    }

    protected function getData($notifiable)
    {
        $otp = InstantiateOTPObject::run($this->transaction)->now();
        $action = $this->transaction->type;
        $amount = $this->transaction->amount;
        $mobile = $notifiable->mobile;
        $signature = config('kaching.signature');

        return json_encode(compact('otp', 'action', 'amount', 'mobile', 'signature'));
    }
}
