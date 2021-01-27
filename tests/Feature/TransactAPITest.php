<?php

namespace Tests\Feature;

use OTPHP\Factory;
use OTPHP\TOTPInterface;

use Bavix\Wallet\Models\Transaction;
use Tests\TestCase;
use App\Models\User;
use App\Models\Contact;
use Symfony\Component\HttpFoundation\Response;

class TransactAPITest extends TestCase
{
    use \Illuminate\Foundation\Testing\RefreshDatabase;

    /**
     * @var User
     */
    protected $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->user->createToken('developer-access');
    }

    /** @test */
    public function transaction_deposit_default()
    {
        /*** arrange ***/
        $mobile = '09171234567';
        $action = 'deposit';
        $contact = Contact::bearing($mobile);
        $amount = $this->faker->numberBetween(100,1000);

        /*** act ***/
        $response = $this->actingAs($this->user)->post("/api/transact/$action/$mobile/$amount");

        /*** assert ***/
        $response
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'mobile' => $contact->mobile,
                'action' => $action,
                'amount' => $amount,
                'wallet' => 'default',
                'balance' => $contact->balance,
                'confirmed' => false,
            ]);
        $this->assertEquals(0, $contact->balance);

        /*** arrange ***/
        $action = 'confirm';
        $uuid = $response->json('uuid');
        $transaction = Transaction::where('uuid', $uuid)->first();;
        $otp = $this->getTOTP($transaction)->now();

        /*** act ***/
        $response = $this->actingAs($this->user)->post("/api/transact/$action/$uuid/$otp");

        /*** assert ***/
        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'mobile' => $contact->mobile,
                'action' => $action,
                'amount' => $amount,
                'wallet' => 'default',
                'balance' => $contact->balance,
                'confirmed' => true
            ]);
        $this->assertEquals($amount, $contact->balance);
    }

    protected function getTOTP($transaction): TOTPInterface
    {
        return Factory::loadFromProvisioningUri($transaction->meta['otp_uri']);
    }

    /** @test */
    public function transaction_deposit_genx_pcso()
    {
        /*** arrange ***/
        $mobile = '09171234567';
        $contact = Contact::bearing($mobile);

        foreach (['genx', 'pcso'] as $wallet) {
            /*** arrange ***/
            $action = 'deposit';
            $amount = $this->faker->numberBetween(100,1000);

            /*** act ***/
            $response = $this->actingAs($this->user)->post("/api/transact/$action/$mobile/$amount/$wallet");

            /*** assert ***/
            $response
                ->assertStatus(Response::HTTP_OK)
                ->assertJson([
                    'mobile' => $contact->mobile,
                    'action' => $action,
                    'amount' => $amount,
                    'wallet' => $wallet,
                    'balance' => ($digital_wallet = $contact->getWallet($wallet))->balance,
                    'confirmed' => false
                ]);
            $this->assertTrue($contact->hasWallet($wallet));
            $this->assertEquals(0, $digital_wallet->balance);

            /*** arrange ***/
            $action = 'confirm';
            $uuid = $response->json('uuid');
            $transaction = Transaction::where('uuid', $uuid)->first();;
            $otp = $this->getTOTP($transaction)->now();

            /*** act ***/
            $response = $this->actingAs($this->user)->post("/api/transact/$action/$uuid/$otp");

            /*** assert ***/
            $response->assertStatus(Response::HTTP_OK)
                ->assertJson([
                    'mobile' => $contact->mobile,
                    'action' => $action,
                    'amount' => $amount,
                    'wallet' => $wallet,
                    'balance' => $digital_wallet->balance,
                    'confirmed' => true
                ]);
            $this->assertEquals($amount, $digital_wallet->balance);
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
            ->deposit($initial_amount = $this->faker->numberBetween(1000,10000), [], true);
        $response = $this->actingAs($this->user)->post("/api/transact/$action/$mobile/$amount");

        /*** assert ***/
        $response
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'mobile' => $contact->mobile,
                'action' => $action,
                'amount' => $amount,
                'wallet' => 'default',
                'balance' => $contact->balance,
                'confirmed' => false
            ]);
        $this->assertEquals( $initial_amount, $contact->balance);

        /*** arrange ***/
        $explicitTransaction = $this->getExplicitTransaction($response->json('uuid'));

        /*** act ***/
        $contact->getWallet('default')->confirm($explicitTransaction);

        /*** assert ***/
        $this->assertEquals($initial_amount - $amount, $contact->balance);
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
            $response = $this->actingAs($this->user)->post("/api/transact/$action/$mobile/$amount/$wallet");

            /*** arrange ***/
            $explicitTransaction = $this->getExplicitTransaction($response->json('uuid'));

            /*** assert ***/
            $response
                ->assertStatus(Response::HTTP_OK)
                ->assertJson([
                    'mobile' => $contact->mobile,
                    'action' => $action,
                    'amount' => $amount,
                    'wallet' => $wallet,
                    'balance' => ($digital_wallet = $contact->getWallet($wallet))->balance,
                    'confirmed' => false
                ]);
            $this->assertEquals( $initial_amount, $digital_wallet->balance);

            /*** arrange ***/
            $explicitTransaction = $this->getExplicitTransaction($response->json('uuid'));

            /*** act ***/
            $contact->getWallet($wallet)->confirm($explicitTransaction);

            /*** assert ***/
            $this->assertEquals($initial_amount - $amount, $digital_wallet->balance);
        }
    }

//    /** @test */
//    public function transaction_balance_default()
//    {
//        /*** arrange ***/
//        $mobile = '09171234567';
//        $action = 'balance';
//        $amount = $this->faker->numberBetween(100,1000);
//
//        /*** act ***/
//        ($contact = Contact::factory(compact('mobile'))->create())->deposit($amount);
//        $response = $this->actingAs($this->user)->get("/api/transact/balance/$mobile");
//
//        /*** assert ***/
//        $response
//            ->assertStatus(Response::HTTP_OK)
//            ->assertJson([
//                'mobile' => $contact->mobile,
//                'action' => $action,
//                'amount' => $amount,
//                'wallet' => 'default'
//            ]);
//        $this->assertEquals(0, $contact->balance - $amount);
//    }

    /** @test */
    public function transaction_balance_default()
    {
        /*** arrange ***/
        $mobile = '09171234567';
        $action = 'balance';
        $amount = $this->faker->numberBetween(100,1000);

        /*** act ***/
        ($contact = Contact::factory($data = compact('mobile'))->create())
            ->deposit($amount);

        $response = $this->actingAs($this->user)->call(
            'GET',
            '/api/transact/balance',
            $data,
            [],
            [],
            $this->transformHeadersToServerVars([
                'CONTENT_LENGTH' => mb_strlen(json_encode($data), '8bit'),
                'CONTENT_TYPE' => 'application/json',
                'Accept' => 'application/json',
            ])
        );

        /*** assert ***/
        $response
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'mobile' => $contact->mobile,
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
            $response = $this->actingAs($this->user)->call(
                'GET',
                '/api/transact/balance',
                $data = compact('mobile', 'wallet'),
                [],
                [],
                $this->transformHeadersToServerVars([
                    'CONTENT_LENGTH' => mb_strlen(json_encode($data), '8bit'),
                    'CONTENT_TYPE' => 'application/json',
                    'Accept' => 'application/json',
                ])
            );

            /*** assert ***/
            $response
                ->assertStatus(Response::HTTP_OK)
                ->assertJson([
                    'mobile' => $contact->mobile,
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
        $response = $this->actingAs($this->user)->post("/api/transact/$action/$from/$to/$amount");

        /*** assert ***/
        $response->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR);
        $this->assertNotNull($origin = Contact::bearing($from));
        $this->assertNotNull($destination = Contact::bearing($to));

        /*** act ***/
        $origin->deposit($amount);
        $response = $this->actingAs($this->user)->post("/api/transact/$action/$from/$to/$amount");

        /*** act ***/
        $response
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'action' => $action,
                'from' => $origin->mobile,
                'to' => $destination->mobile,
                'amount' => $amount,
                'wallet' => 'default',
                'balance' => $origin->balance
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
            $response = $this->actingAs($this->user)->post("/api/transact/$action/$from/$to/$amount/$wallet");

            /*** assert ***/
            $response->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR);
            if (! isset($origin))
                $this->assertNotNull($origin = Contact::bearing($from));
            if (! isset($destination))
                $this->assertNotNull($destination = Contact::bearing($to));

            /*** act ***/
            $origin->getWallet($wallet)->deposit($amount + 1000);
            $response = $this->actingAs($this->user)->post("/api/transact/$action/$from/$to/$amount/$wallet");

            /*** assert ***/
            $response
                ->assertStatus(Response::HTTP_OK)
                ->assertJson([
                    'action' => $action,
                    'from' => $origin->mobile,
                    'to' => $destination->mobile,
                    'amount' => $amount,
                    'wallet' => $wallet,
                    'balance' => $origin->getWallet($wallet)->balance
                ]);
            $this->assertEquals(1000, $origin->getWallet($wallet)->balance);
            $this->assertEquals($amount, $destination->getWallet($wallet)->balance);
        }
    }

    protected function getImplicitTransaction(Contact $contact, string $wallet, string $type, string $amount, $created_at)
    {
        $wallet_id = $contact->getWallet($wallet)->id;

        return $contact->transactions()->where(compact('wallet_id', 'type', 'amount', 'created_at'))->first();
    }

    protected function getExplicitTransaction(string $uuid)
    {
        return Transaction::where('uuid', $uuid)->first();
    }
}
