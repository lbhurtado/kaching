<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Contact;
use Symfony\Component\HttpFoundation\Response;

class TransactAPITest extends TestCase
{
    use \Illuminate\Foundation\Testing\RefreshDatabase;

    /** @test */
    public function transaction_deposit()
    {
        /*** arrange ***/
        $mobile = '09171234567';
        $action = 'deposit';
        $amount = $this->faker->numberBetween(100,1000);
        $response = $this->post("/api/transact/$mobile/$action/$amount");

        /*** act ***/
        $response
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'mobile' => phone($mobile, 'PH')->formatE164(),
                'action' => $action,
                'amount' => $amount
            ]);

        /*** assert ***/
        $this->assertEquals(Contact::bearing($mobile)->balance, $amount);
    }

    /** @test */
    public function transaction_withdraw()
    {
        /*** arrange ***/
        $mobile = '09171234567';
        $action = 'withdraw';
        $amount = $this->faker->numberBetween(100,1000);

        /*** act ***/
        $contact = tap(Contact::factory(compact('mobile'))->create())
            ->deposit($initial_amount = $this->faker->numberBetween(1000,10000));
        $response = $this->post("/api/transact/$mobile/$action/$amount");

        /*** assert ***/
        $response
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'mobile' => phone($mobile, 'PH')->formatE164(),
                'action' => $action,
                'amount' => $amount
            ]);
        $this->assertEquals($contact->balance, $initial_amount - $amount);
    }

    /** @test */
    public function transaction_balance()
    {
        /*** arrange ***/
        $mobile = '09171234567';
        $action = 'balance';
        $amount = $this->faker->numberBetween(100,1000);

        /*** act ***/
        $contact = tap(Contact::factory(compact('mobile'))->create())->deposit($amount);
        $response = $this->get("/api/transact/$mobile/balance");

        /*** assert ***/
        $response
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'mobile' => phone($mobile, 'PH')->formatE164(),
                'action' => $action,
                'amount' => $amount
            ]);
        $this->assertEquals(0, $contact->balance - $amount);
    }
}
