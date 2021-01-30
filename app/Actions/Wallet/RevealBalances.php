<?php

namespace App\Actions\Wallet;

use App\Models\Contact;
use Illuminate\Console\Command;
use Lorisleiva\Actions\ActionRequest;
use App\Http\Resources\BalancesResource;
use Lorisleiva\Actions\Concerns\AsAction;
use Illuminate\Support\Collection;

class RevealBalances
{
    use AsAction;

    public $commandSignature = 'wallet:balance {mobile} {wallet=default}';

    public $commandDescription = 'Retrieves wallet balances from contact given its mobile.';

    public function handle(Contact $contact): Collection
    {
       return  $contact->wallets->pluck('balance', 'slug');
    }

    public function asController(ActionRequest $request): Collection
    {
        return $this->handle($request->user());
    }

    public function rules(): array
    {
        return [
            'mobile' => [
                'required',
                'regex:/^(09|\+?639)\d{9}$/'
            ]
        ];
    }

    public function authorize(ActionRequest $request): bool
    {
        $contact = InstantiateContact::run($request->mobile);

        $request->setUserResolver(function () use ($contact) {
            return $contact;
        });

        return true;
    }

    public function jsonResponse(Collection $collection): BalancesResource
    {
        return new BalancesResource($collection);
    }

    public function asCommand(Command $command)
    {
        tap(InstantiateContact::run($command->argument('mobile')), function ($contact) use ($command) {
            tap($this->handle($contact, $command->argument('wallet')), function ($balance) use ($command) {
                $command->line($balance);
            });
        });
    }
}
