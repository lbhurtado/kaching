<?php

namespace App\Notifications;

use OTPHP\Factory;
use OTPHP\TOTPInterface;
use Illuminate\Bus\Queueable;
use Bavix\Wallet\Models\Transaction;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendCreditActionOTP extends Notification
{
    use Queueable;

    /**
     * @var Transaction
     */
    protected $transaction;

    /**
     * SendCreditActionOTP constructor.
     * @param Transaction $transaction
     */
    public function __construct(Transaction $transaction)
    {
        $this->transaction = $transaction;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->line('The introduction to the notification.')
                    ->action('Notification Action', url('/'))
                    ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable): array
    {
        return [
            'uuid' => $this->transaction->uuid,
            'otp' => $this->getTOTP()->now()
        ];
    }

    /**
     * @return TOTPInterface
     */
    public function getTOTP(): TOTPInterface
    {
        return Factory::loadFromProvisioningUri($this->transaction->meta['otp_uri']);
    }
}
