<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Symfony\Component\HttpFoundation\Response;
use Laravel\Sanctum\Sanctum;

class TokenAPITest extends TestCase
{
    use \Illuminate\Foundation\Testing\RefreshDatabase;

    protected $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    /** @test */
    public function token_default()
    {
        /*** arrange ***/
        $params = [
            'email' => $this->user->email,
            'password' => 'password',
            'device_name' => 'Android'
        ];

        /*** act ***/
        $response = $this->post("/api/token", $params);

        /*** arrange ***/
        $token = $response->content();

        /*** assert ***/
        $response->assertStatus(Response::HTTP_OK);
        $this->assertTrue($this->user->is($this->getUserFromToken($token)));
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
