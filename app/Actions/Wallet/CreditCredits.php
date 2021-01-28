<?php

namespace App\Actions\Wallet;

use Illuminate\Validation\Rule;
use Bavix\Wallet\Models\Wallet;
use Bavix\Wallet\Models\Transaction;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Illuminate\Http\Resources\Json\JsonResource;

abstract class CreditCredits
{
    use AsAction;

    public function handle(Wallet $wallet, int $amount, $meta = []): Transaction
    {
        $transaction = $wallet->{$this->action}($amount, $meta, false);
        EmbedTransactionOTP::run($transaction);

        return $transaction;
    }

    public function asController(ActionRequest $request): Transaction
    {
        $wallet = (InstantiateContact::run($request->mobile))->getWallet($request->wallet);
        $transaction = $this->handle($wallet, $request->amount, $request->meta);

        return $transaction;
    }

    public function rules(): array
    {
        return [
            'mobile' => [
                'required',
                'regex:/^(09|\+?639)\d{9}$/'
            ],
            'amount' => [
                'required',
                'regex:/^[0-9]+$/'
            ],
            'wallet' => [
                Rule::in(['default', 'genx', 'pcso']),
            ]
        ];
    }

    public function authorize(ActionRequest $request): bool
    {
        if (!$request->has('wallet'))
            $request->merge(['wallet' => 'default' ]);

        if (!$request->has('meta'))
            $request->merge(['meta' => [] ]);

        return true;
    }

    abstract public function jsonResponse(Transaction $transaction): JsonResource;
}
