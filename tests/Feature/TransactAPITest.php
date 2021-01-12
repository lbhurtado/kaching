<?php

namespace Tests\Feature;

use App\Models\Contact;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Symfony\Component\HttpFoundation\Response;

class TransactAPITest extends TestCase
{
    use \Illuminate\Foundation\Testing\RefreshDatabase;

    /** @test */
    public function transaction_deposit()
    {
        $mobile = '09171234567';
        $action = 'deposit';
        $amount = $this->faker->numberBetween(100,1000);
        $response = $this->post("/api/transact/$mobile/$action/$amount");

        $response
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'mobile' => phone($mobile, 'PH')->formatE164(),
                'action' => $action,
                'amount' => $amount
            ]);

        $this->assertEquals(Contact::bearing($mobile)->balance, $amount);
    }

    /** @test */
    public function transaction_withdraw()
    {
        $mobile = '09171234567';
        $action = 'withdraw';
        $amount = $this->faker->numberBetween(100,1000);

        $contact = tap(Contact::factory(compact('mobile'))->create())->deposit($initial_amount = 1000);
        $response = $this->post("/api/transact/$mobile/$action/$amount");

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
        $mobile = '09171234567';
        $action = 'balance';
        $amount = $this->faker->numberBetween(100,1000);

        $contact = tap(Contact::factory(compact('mobile'))->create())->deposit($amount);
        $response = $this->get("/api/transact/$mobile/balance");

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
