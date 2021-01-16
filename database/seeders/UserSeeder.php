<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->persistDefaultTeam($this->persistDefaultUser());
    }

    protected function persistDefaultUser(): int
    {
        $name = decrypt(config('kaching.seed.user.name'));
        $email = decrypt(config('kaching.seed.user.email'));
        $password = Hash::make(decrypt(config('kaching.seed.user.password')));

        return DB::table('users')
            ->insertGetId(compact('name', 'email', 'password'));
    }

    protected function persistDefaultTeam(int $user_id) {
        $name = config('kaching.seed.team.name');
        $personal_team = 1;

        DB::table('teams')
            ->insert(compact('name', 'user_id', 'personal_team'));
    }
}
