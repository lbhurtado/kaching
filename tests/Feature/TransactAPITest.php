<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Contact;
use Bavix\Wallet\Models\Transaction;
use App\Actions\OTP\InstantiateOTPObject;
use App\Actions\Wallet\InstantiateTransaction;
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
        $response = $this->actingAs($this->user)
            ->postJson(
                "/api/transact/$action",
                $data = compact('mobile', 'amount'),
                $this->getHeaderData($data)
            );

        /*** assert ***/
        $response
            ->assertStatus(Response::HTTP_CREATED)
            ->assertJson([
                'data' => [
                    'mobile' => $contact->mobile,
                    'action' => $action,
                    'amount' => $amount,
                    'wallet' => 'default',
                    'balance' => $contact->balance,
                    'confirmed' => false,
                ],
            ]);
        $this->assertEquals(0, $contact->balance);

        /*** arrange ***/
        $action = 'confirm';
        $uuid = $response->json('data.uuid');
        $transaction = InstantiateTransaction::run($uuid);
        $otp = InstantiateOTPObject::run($transaction)->now();

        /*** act ***/
        $response = $this->actingAs($this->user)
            ->postJson(
                "/api/transact/$action",
                $data = compact('uuid', 'otp'),
                $this->getHeaderData($data)
            );

        /*** assert ***/
        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'data' => [
                    'mobile' => $contact->mobile,
                    'action' => $action,
                    'amount' => $amount,
                    'wallet' => 'default',
                    'balance' => $contact->balance,
                    'confirmed' => true
                ]
            ]);
        $this->assertEquals($amount, $contact->balance);
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
            $response = $this->actingAs($this->user)
                ->postJson(
                    "/api/transact/$action",
                    $data = compact('mobile', 'amount', 'wallet'),
                    $this->getHeaderData($data)
                );

            /*** assert ***/
            $response
                ->assertStatus(Response::HTTP_CREATED)
                ->assertJson([
                    'data' => [
                        'mobile' => $contact->mobile,
                        'action' => $action,
                        'amount' => $amount,
                        'wallet' => $wallet,
                        'balance' => ($digital_wallet = $contact->getWallet($wallet))->balance,
                        'confirmed' => false
                    ],
                ]);
            $this->assertTrue($contact->hasWallet($wallet));
            $this->assertEquals(0, $digital_wallet->balance);

            /*** arrange ***/
            $action = 'confirm';
            $uuid = $response->json('data.uuid');
            $transaction = InstantiateTransaction::run($uuid);
            $otp = InstantiateOTPObject::run($transaction)->now();

            /*** act ***/
            $response = $this->actingAs($this->user)
                ->postJson(
                    "/api/transact/$action",
                    $data = compact('uuid', 'otp'),
                    $this->getHeaderData($data)
                );

            /*** assert ***/
            $response->assertStatus(Response::HTTP_OK)
                ->assertJson([
                    'data' => [
                        'mobile' => $contact->mobile,
                        'action' => $action,
                        'amount' => $amount,
                        'wallet' => $wallet,
                        'balance' => $digital_wallet->balance,
                        'confirmed' => true
                    ]
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
        $response = $this->actingAs($this->user)
            ->postJson(
                "/api/transact/$action",
                $data = compact('mobile', 'amount'),
                $this->getHeaderData($data)
            );

        /*** assert ***/
        $response
            ->assertStatus(Response::HTTP_CREATED)
            ->assertJson([
                'data' => [
                    'mobile' => $contact->mobile,
                    'action' => $action,
                    'amount' => $amount,
                    'wallet' => 'default',
                    'balance' => $contact->balance,
                    'confirmed' => false
                ],
            ]);
        $this->assertEquals( $initial_amount, $contact->balance);

        /*** arrange ***/
        $explicitTransaction = $this->getExplicitTransaction($response->json('data.uuid'));

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
//            $response = $this->actingAs($this->user)->post("/api/transact/$action/$mobile/$amount/$wallet");
            $response = $this->actingAs($this->user)
                ->postJson(
                    "/api/transact/$action",
                    $data = compact('mobile', 'amount', 'wallet'),
                    $this->getHeaderData($data)
                );

            /*** arrange ***/
            $explicitTransaction = $this->getExplicitTransaction($response->json('data.uuid'));

            /*** assert ***/
            $response
                ->assertStatus(Response::HTTP_CREATED)
                ->assertJson([
                    'data' => [
                        'mobile' => $contact->mobile,
                        'action' => $action,
                        'amount' => $amount,
                        'wallet' => $wallet,
                        'balance' => ($digital_wallet = $contact->getWallet($wallet))->balance,
                        'confirmed' => false
                    ],
                ]);
            $this->assertEquals( $initial_amount, $digital_wallet->balance);

            /*** arrange ***/
            $explicitTransaction = $this->getExplicitTransaction($response->json('data.uuid'));

            /*** act ***/
            $contact->getWallet($wallet)->confirm($explicitTransaction);

            /*** assert ***/
            $this->assertEquals($initial_amount - $amount, $digital_wallet->balance);
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
                'data' => [
                    'mobile' => $contact->mobile,
                    'action' => $action,
                    'amount' => $amount,
                    'wallet' => 'default'
                ]
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
                    'data' => [
                        'mobile' => $contact->mobile,
                        'action' => $action,
                        'amount' => $amount,
                        'wallet' => $wallet
                    ]
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
        $response = $this->actingAs($this->user)
            ->postJson(
                "/api/transact/$action",
                $data = compact('from', 'to', 'amount'),
                $this->getHeaderData($data)
            );

        /*** assert ***/
        $response->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR);
        $this->assertNotNull($origin = Contact::bearing($from));
        $this->assertNotNull($destination = Contact::bearing($to));

        /*** act ***/
        $origin->deposit($amount);
        $response = $this->actingAs($this->user)
            ->postJson(
                "/api/transact/$action",
                $data,
                $this->getHeaderData($data)
            );

        /*** act ***/
        $response
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'data' => [
                    'action' => $action,
                    'from' => $origin->mobile,
                    'to' => $destination->mobile,
                    'amount' => $amount,
                    'wallet' => 'default',
                    'balance' => $origin->balance
                ]
            ]);

        /*** assert ***/
        $this->assertEquals(0, $origin->balance);
        $this->assertEquals($amount, $destination->balance);
    }

    /** @test */
    public function transaction_transfer_genx_pcso()//TODO: Yo, this is next. Just repeat the transfer default above
    {
        $from = '09171234567';
        $to = '09187654321';
        $action = 'transfer';

        foreach (['genx', 'pcso'] as $wallet) {
            /*** arrange ***/
            $amount = $this->faker->numberBetween(100,1000);

            /*** act ***/
            $response = $this->actingAs($this->user)
                ->postJson(
                    "/api/transact/$action",
                    $data = compact('from', 'to', 'amount', 'wallet'),
                    $this->getHeaderData($data)
                );

            /*** assert ***/
            $response->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR);
            if (! isset($origin))
                $this->assertNotNull($origin = Contact::bearing($from));
            if (! isset($destination))
                $this->assertNotNull($destination = Contact::bearing($to));

            /*** act ***/
            $origin->getWallet($wallet)->deposit($amount + 1000);
            $response = $this->actingAs($this->user)
                ->postJson(
                    "/api/transact/$action",
                    $data,
                    $this->getHeaderData($data)
                );

            /*** assert ***/
            $response
                ->assertStatus(Response::HTTP_OK)
                ->assertJson([
                    'data' => [
                        'action' => $action,
                        'from' => $origin->mobile,
                        'to' => $destination->mobile,
                        'amount' => $amount,
                        'wallet' => $wallet,
                        'balance' => $origin->getWallet($wallet)->balance
                    ],
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

    protected function getHeaderData($data): array
    {
        return [
            'CONTENT_LENGTH' => mb_strlen(json_encode($data), '8bit'),
            'CONTENT_TYPE' => 'application/json',
            'Accept' => 'application/json'
        ];
    }
}
