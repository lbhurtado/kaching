<?php

namespace App\Actions\Wallet;

use App\Actions\OTP\GenerateOTPURI;
use App\Events\TransactionEmbedded;
use Bavix\Wallet\Models\Transaction;
use Lorisleiva\Actions\Concerns\AsAction;

class EmbedTransactionOTP
{
    use AsAction;

    public function handle(Transaction $transaction): void
    {
        $meta = $this->mergeToMeta($transaction->meta, GenerateOTPURI::run());
        $transaction->update(compact('meta'));

        TransactionEmbedded::dispatch($transaction);
    }

    protected function mergeToMeta(array $meta, string $otp_uri): array
    {
        return array_merge($meta, compact('otp_uri'));
    }
}
