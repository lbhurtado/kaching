<?php

namespace App\Actions\Wallet;

use Illuminate\Console\Command;
use Bavix\Wallet\Models\Transaction;
use Lorisleiva\Actions\ActionRequest;
use App\Http\Resources\ConfirmResource;
use Lorisleiva\Actions\Concerns\AsAction;

class ConfirmTransaction
{
    use AsAction;

    public $commandSignature = 'wallet:confirm {uuid} {otp}';

    public $commandDescription = 'Confirms transaction given the uuid and otp.';

    public function handle(Transaction $transaction, string $otp): bool
    {
        if (InstantiateOTPObject::run($transaction)->verify($otp)) {
            return $transaction->wallet->confirm($transaction);
        }

        return false;
    }

    public function asController(ActionRequest $request): Transaction
    {
        return tap(InstantiateTransaction::run($request->uuid), function ($transaction) use ($request) {
            $this->handle($transaction, $request->otp);
        });
    }

    public function rules(): array
    {
        return [
            'uuid' => [
                'required',
                'uuid'
            ],
            'otp' => [
                'required',
                'regex:/^[0-9]+$/'
            ]
        ];
    }

    public function authorize(ActionRequest $request): bool
    {
        return true;
    }

    public function jsonResponse(Transaction $transaction): ConfirmResource
    {
        return new ConfirmResource($transaction);
    }

    public function asCommand(Command $command)
    {
        tap(InstantiateTransaction::run($command->argument('uuid')), function ($transaction) use ($command) {
            tap($this->handle($transaction, $command->argument('otp')), function ($result) use ($command) {
                $command->line($result);
            });
        });
    }
}
