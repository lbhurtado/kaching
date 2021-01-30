<?php

namespace App\Actions\Wallet;

use Bavix\Wallet\Models\Wallet;
use Illuminate\Console\Command;
use Illuminate\Validation\Rule;
use Bavix\Wallet\Models\Transfer;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use \App\Http\Resources\TransferResource;

class TransferCredits
{
    use AsAction;

    public $commandSignature = 'wallet:transfer {from} {to} {amount}';

    public $commandDescription = 'Transfer amount from one mobile wallet to another.';

    public function handle(Wallet $origin, Wallet $destination, int $amount): Transfer
    {
        return $origin->transfer($destination, $amount);
    }

    public function asController(ActionRequest $request): Wallet
    {
        $origin = (InstantiateContact::run($request->from))->getWallet($request->wallet);
        $destination = (InstantiateContact::run($request->to))->getWallet($request->wallet);
        $this->handle($origin, $destination, $request->amount);

        return $origin;
    }

    public function rules(): array
    {
        return [
            'from' => [
                'required',
                'regex:/^(09|\+?639)\d{9}$/'
            ],
            'to' => [
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

        return true;
    }

    public function jsonResponse(Wallet $wallet): TransferResource
    {
        return new TransferResource($wallet);
    }

    public function asCommand(Command $command)
    {
        $origin = InstantiateContact::run($command->argument('from'));
        $destination = InstantiateContact::run($command->argument('to'));
        $amount = $command->argument('amount');

        $this->handle($origin, $destination, $amount);
        $command->line('Done!');
    }
}
