<?php

namespace App\Jobs;

use OTPHP\TOTP;
use Illuminate\Bus\Queueable;
use Bavix\Wallet\Models\Transaction;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use App\Notifications\TransactionApproval;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class EmbedTransactionOTP implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var Transaction
     */
    protected $transaction;

    /**
     * ProvisionURIToTransaction constructor.
     * @param Transaction $transaction
     */
    public function __construct(Transaction $transaction)
    {
        $this->transaction = $transaction;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->updateTransactionMeta();
        $this->sendOTP();
    }

    /**
     * @return $this
     */
    protected function updateTransactionMeta(): self
    {
        $otp_uri = $this->getOTP_URI();
        $this->transaction->update(['meta' => array_merge($this->transaction->meta, compact('otp_uri'))]);

        return $this;
    }

    /**
     *
     */
    protected function sendOTP(): void
    {
        $this->transaction->payable->notify(new TransactionApproval($this->transaction));
    }

    /**
     * @return string
     */
    protected function getOTP_URI(): string
    {
        $period = config('kaching.otp.period', 10 * 60); //10 minutes
        $otp_uri = tap(TOTP::create(null, $period), function ($totp) {
            $totp->setLabel(env('APP_NAME', 'TEST'));
        })->getProvisioningUri();

        return $otp_uri;
    }
}
