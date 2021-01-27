<?php

namespace App\Actions\Wallet;

use App\Models\User;
use Illuminate\Console\Command;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateUserToken
{
    use AsAction;

    public $commandSignature = 'user:token {email} {password} {device_name}';

    public $commandDescription = 'Retrieves token from user of given email.';

    public function handle(User $user, string $deviceName): string
    {
        return $user->createToken($deviceName)->plainTextToken;
    }

    public function asController(ActionRequest $request): string
    {
        $user = $request->user();

        return $this->handle($user, $request->device_name);
    }

    public function asCommand(Command $command)
    {
        tap($this->getUserFromEmail($command->argument('email')), function ($user) use ($command) {
           tap($this->handle($user, $command->argument('password'), $command->argument('device_name')), function ($token) use ($command) {
               $command->line($token);
           });
        });
    }

    public function rules(): array
    {
        return [
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'required',
        ];
    }

    public function authorize(ActionRequest $request): bool
    {
        $user = $this->getUserFromEmail($request->email);
        $request->merge(['user' => $user ]);
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        return in_array($user->email, [decrypt(config('kaching.seed.user.email'))]);
    }

    protected function getUserFromEmail(string $email): User
    {
        return User::where('email', $email)->firstorFail();
    }
}
