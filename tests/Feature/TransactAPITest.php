<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Contact;
use Symfony\Component\HttpFoundation\Response;

class TransactAPITest extends TestCase
{
    use \Illuminate\Foundation\Testing\RefreshDatabase;

    /** @test */
    public function transaction_deposit_default()
    {
        /*** arrange ***/
        $mobile = '09171234567';
        $action = 'deposit';
        $amount = $this->faker->numberBetween(100,1000);

        /*** act ***/
        $response = $this->post("/api/transact/$action/$mobile/$amount");

        /*** assert ***/
        $response
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'mobile' => phone($mobile, 'PH')->formatE164(),
                'action' => $action,
                'amount' => $amount,
                'wallet' => 'default'
            ]);
        $this->assertEquals(Contact::bearing($mobile)->balance, $amount);
    }

    /** @test */
    public function transaction_deposit_genx_pcso()
    {
        /*** arrange ***/
        $mobile = '09171234567';
        $action = 'deposit';

        foreach (['genx', 'pcso'] as $wallet) {
            /*** arrange ***/
            $amount = $this->faker->numberBetween(100,1000);

            /*** act ***/
            $response = $this->post("/api/transact/$action/$mobile/$amount/$wallet");

            /*** assert ***/
            $response
                ->assertStatus(Response::HTTP_OK)
                ->assertJson([
                    'mobile' => phone($mobile, 'PH')->formatE164(),
                    'action' => $action,
                    'amount' => $amount,
                    'wallet' => $wallet
                ]);
            $this->assertTrue(($contact = Contact::bearing($mobile))->hasWallet($wallet));
            $this->assertEquals($amount, $contact->getWallet($wallet)->balance);
        }
    }

    /** @test */
    public function transaction_withdraw_default()
    {
        /*** arrange ***/
        $mobile = '09171234567';
        $action = 'withdraw';
        $amount = $this->faker->numberBetween(100,1000);

        /*** act ***/
        $contact = tap(Contact::factory(compact('mobile'))->create())
            ->deposit($initial_amount = $this->faker->numberBetween(1000,10000));
        $response = $this->post("/api/transact/$action/$mobile/$amount");

        /*** assert ***/
        $response
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'mobile' => phone($mobile, 'PH')->formatE164(),
                'action' => $action,
                'amount' => $amount,
                'wallet' => 'default'
            ]);
        $this->assertEquals( $initial_amount - $amount, $contact->balance);
    }

    /** @test */
    public function transaction_withdraw_genx_pcso()
    {
        /*** arrange ***/
        $mobile = '09171234567';
        $action = 'withdraw';
        $contact = Contact::factory(compact('mobile'))->create();

        foreach (['genx', 'pcso'] as $wallet) {
            /*** arrange ***/
            $amount = $this->faker->numberBetween(100,1000);

            /*** act ***/
            $contact->getWallet($wallet)
                ->deposit($initial_amount = $this->faker->numberBetween(1000,10000));
            $response = $this->post("/api/transact/$action/$mobile/$amount/$wallet");

            /*** assert ***/
            $response
                ->assertStatus(Response::HTTP_OK)
                ->assertJson([
                    'mobile' => phone($mobile, 'PH')->formatE164(),
                    'action' => $action,
                    'amount' => $amount,
                    'wallet' => $wallet
                ]);
            $this->assertEquals( $initial_amount - $amount, $contact->getWallet($wallet)->balance);
        }
    }

    /** @test */
    public function transaction_balance_default()
    {
        /*** arrange ***/
        $mobile = '09171234567';
        $action = 'balance';
        $amount = $this->faker->numberBetween(100,1000);

        /*** act ***/
        ($contact = Contact::factory(compact('mobile'))->create())->deposit($amount);
        $response = $this->get("/api/transact/balance/$mobile");

        /*** assert ***/
        $response
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'mobile' => phone($mobile, 'PH')->formatE164(),
                'action' => $action,
                'amount' => $amount,
                'wallet' => 'default'
            ]);
        $this->assertEquals(0, $contact->balance - $amount);
    }

    /** @test */
    public function transaction_balance_genx_pcso()
    {
        /*** arrange ***/
        $mobile = '09171234567';
        $action = 'balance';
        $contact = Contact::factory(compact('mobile'))->create();

        foreach (['genx', 'pcso'] as $wallet) {
            /*** arrange ***/
            $amount = $this->faker->numberBetween(100,1000);

            /*** act ***/
            $contact->getWallet($wallet)->deposit($amount);
            $response = $this->get("/api/transact/balance/$mobile/$wallet");

            /*** assert ***/
            $response
                ->assertStatus(Response::HTTP_OK)
                ->assertJson([
                    'mobile' => phone($mobile, 'PH')->formatE164(),
                    'action' => $action,
                    'amount' => $amount,
                    'wallet' => $wallet
                ]);
            $this->assertEquals(0, $contact->getWallet($wallet)->balance - $amount);
        }
    }

    /** @test */
    public function transaction_transfer_default()
    {
        /*** arrange ***/
        $from = '09171234567';
        $to = '09187654321';
        $action = 'transfer';
        $amount = $this->faker->numberBetween(100,1000);

        /*** act ***/
        $response = $this->post("/api/transact/$action/$from/$to/$amount");

        /*** assert ***/
        $response->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR);
        $this->assertNotNull($origin = Contact::bearing($from));
        $this->assertNotNull($destination = Contact::bearing($to));

        /*** act ***/
        $origin->deposit($amount);
        $response = $this->post("/api/transact/$action/$from/$to/$amount");

        /*** act ***/
        $response
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'action' => $action,
                'from' => $origin->mobile,
                'to' => $destination->mobile,
                'amount' => $amount,
                'wallet' => 'default'
            ]);

        /*** assert ***/
        $this->assertEquals(0, $origin->balance);
        $this->assertEquals($amount, $destination->balance);
    }

    /** @test */
    public function transaction_transfer_genx_pcso()
    {
        $from = '09171234567';
        $to = '09187654321';
        $action = 'transfer';

        foreach (['genx', 'pcso'] as $wallet) {
            /*** arrange ***/
            $amount = $this->faker->numberBetween(100,1000);

            /*** act ***/
            $response = $this->post("/api/transact/$action/$from/$to/$amount/$wallet");

            /*** assert ***/
            $response->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR);
            if (! isset($origin))
                $this->assertNotNull($origin = Contact::bearing($from));
            if (! isset($destination))
                $this->assertNotNull($destination = Contact::bearing($to));

            /*** act ***/
            $origin->getWallet($wallet)->deposit($amount);
            $response = $this->post("/api/transact/$action/$from/$to/$amount/$wallet");

            /*** assert ***/
            $response
                ->assertStatus(Response::HTTP_OK)
                ->assertJson([
                    'action' => $action,
                    'from' => $origin->mobile,
                    'to' => $destination->mobile,
                    'amount' => $amount,
                    'wallet' => $wallet
                ]);
            $this->assertEquals(0, $origin->getWallet($wallet)->balance);
            $this->assertEquals($amount, $destination->getWallet($wallet)->balance);
        }
    }
}
