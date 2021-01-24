<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Bavix\Wallet\Models\Wallet;
use Bavix\Wallet\Models\Transaction;
use App\Http\Resources\CreditResource;
use App\Http\Resources\BalanceResource;
use App\Http\Resources\ConfirmResource;
use App\Http\Resources\TransferResource;
use Symfony\Component\HttpFoundation\Response;

class TransactController extends Controller
{
    const UNCONFIRMED = false;

    /**
     * @param string $mobile
     * @param string $action
     * @param int $amount
     * @param string $wallet
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function credit(string $action, string $mobile, int $amount, string $wallet = 'default')
    {
        $transaction = Contact::bearing($mobile)
            ->credit($amount, $wallet, $action, self::UNCONFIRMED, ['action' => $action]);

        return response(new CreditResource($transaction), Response::HTTP_OK);
    }

    /**
     * @param string $mobile
     * @param string $action
     * @param string $wallet
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function balance(string $action, string $mobile, string $wallet = 'default')
    {
        $contact = Contact::bearing($mobile);

        return response(new BalanceResource($contact, $wallet), Response::HTTP_OK);
    }

    /**
     * @param string $action
     * @param string $from
     * @param string $to
     * @param int $amount
     * @param string $wallet
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function transfer(string $action, string $from, string $to, int $amount, string $wallet = 'default')
    {
        $origin_wallet = $this->wallet_transfer(
            Contact::bearing($from),
            Contact::bearing($to),
            $wallet,
            $amount
        );

        return response(new TransferResource($origin_wallet, $wallet), Response::HTTP_OK);
    }

    /**
     * @param string $action
     * @param string $uuid
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function confirm(string $action, string $uuid)
    {
        $transaction = tap($this->getTransaction($uuid), function ($transaction) {
            $transaction->wallet->confirm($transaction);
        });

        return response(new ConfirmResource($transaction, $action), Response::HTTP_OK);
    }

    /**
     * @param Contact $origin
     * @param Contact $destination
     * @param string $wallet
     * @param int $amount
     * @return Wallet
     */
    protected function wallet_transfer(Contact $origin, Contact $destination, string $wallet, int $amount): Wallet
    {
        $origin_wallet = $origin->getWallet($wallet);
        $destination_wallet = $destination->getWallet($wallet);
        $origin_wallet->transfer($destination_wallet, $amount);

        return $origin_wallet;
    }

    /**
     * @param string $uuid
     * @return mixed
     */
    protected function getTransaction(string $uuid)
    {
        return Transaction::where('uuid', $uuid)->first();
    }
}
