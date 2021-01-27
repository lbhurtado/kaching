<?php

namespace App\Actions\Wallet;

use App\Models\Contact;
use Illuminate\Validation\Rule;
use Illuminate\Console\Command;
use Bavix\Wallet\Models\Wallet;
use Lorisleiva\Actions\ActionRequest;
use App\Http\Resources\BalanceResource;
use Lorisleiva\Actions\Concerns\AsAction;

class RevealBalance
{
    use AsAction;

    public $commandSignature = 'wallet:balance {mobile} {wallet=default}';

    public $commandDescription = 'Retrieves balance from contact given its mobile and wallet.';

    public function handle(Contact $contact, string $wallet = 'default'): int
    {
        return $contact->getWallet($wallet)->balance;
    }

    public function asController(ActionRequest $request): Wallet
    {
        return $request->user()->getWallet($request->wallet);
    }

    public function rules(): array
    {
        return [
            'mobile' => [
                'required',
                'regex:/^(09|\+?639)\d{9}$/'
            ],
            'wallet' => [
                'required',
                Rule::in(['default', 'genx', 'pcso']),
            ]
        ];
    }

    public function authorize(ActionRequest $request): bool
    {
        if (!$request->has('wallet'))
            $request->merge(['wallet' => 'default' ]);

        $contact = $this->getContactFromMobile($request->mobile);
        $request->setUserResolver(function () use ($contact) {
            return $contact;
        });

        return true;
    }

    public function jsonResponse(Wallet $wallet): BalanceResource
    {
        return new BalanceResource($wallet);
    }

    public function asCommand(Command $command)
    {
        tap($this->getContactFromMobile($command->argument('mobile')), function ($contact) use ($command) {
            tap($this->handle($contact, $command->argument('wallet')), function ($balance) use ($command) {
                $command->line($balance);
            });
        });
    }

    protected function getContactFromMobile(string $mobile): Contact
    {
        return Contact::bearing($mobile);
    }
}
