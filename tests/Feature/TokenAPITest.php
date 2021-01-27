<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Symfony\Component\HttpFoundation\Response;

class TokenAPITest extends TestCase
{
    use \Illuminate\Foundation\Testing\RefreshDatabase;

    /** @test */
    public function token_from_default_email_is_ok()
    {
        /*** arrange ***/
        $email = decrypt(config('kaching.seed.user.email'));
        $user = User::factory(compact('email'))->create();

        $params = [
            'email' => $user->email,
            'password' => 'password',
            'device_name' => 'Android'
        ];

        /*** act ***/
        $response = $this->post("/api/token", $params);

        /*** arrange ***/
        $token = $response->content();

        /*** assert ***/
        $response->assertStatus(Response::HTTP_OK);
        $this->assertTrue($user->is($this->getUserFromToken($token)));
    }

    /** @test */
    public function token_from_non_default_email_is_not_ok()
    {
        /*** arrange ***/
        $user = User::factory()->create();

        $params = [
            'email' => $user->email,
            'password' => 'password',
            'device_name' => 'Android'
        ];

        /*** act ***/
        $response = $this->post("/api/token", $params);


        /*** assert ***/
        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }


    /**
     * @param string $token
     * @return User
     */
    protected function getUserFromToken(string $token): User
    {
        $model = Sanctum::$personalAccessTokenModel;
        $accessToken = $model::findToken($token);

        return $accessToken->tokenable;
    }
}
