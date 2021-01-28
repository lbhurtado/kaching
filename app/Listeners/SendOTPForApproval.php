<?php

namespace App\Listeners;

use App\Events\TransactionEmbedded;
use Illuminate\Queue\InteractsWithQueue;
use App\Notifications\TransactionApproval;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendOTPForApproval
{
    /**
     * Handle the event.
     *
     * @param  TransactionEmbedded  $event
     * @return void
     */
    public function handle(TransactionEmbedded $event)
    {
        $transaction = $event->transaction;
        $transaction->payable->notify(new TransactionApproval($transaction));
    }
}
