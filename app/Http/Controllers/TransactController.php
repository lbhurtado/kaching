<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Bavix\Wallet\Models\Wallet;
use Illuminate\Http\Request;
use Bavix\Wallet\Models\Transaction;
use Symfony\Component\HttpFoundation\Response;

class TransactController extends Controller
{
    const CONFIRMED = true;
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
        $contact = Contact::bearing($mobile);
        $digital_wallet = $contact->getWallet($wallet);

        $transaction = $digital_wallet->{$action}($amount, ['owner' => 'LBH'], self::UNCONFIRMED);
        $uuid = $transaction->uuid;
        $balance = $digital_wallet->balance;
        $meta = $transaction->meta;
        $created_at = $transaction->created_at;
        $confirmed = false;

        return response(json_encode(compact('mobile', 'action', 'amount', 'wallet', 'balance', 'uuid', 'meta', 'created_at', 'confirmed')), Response::HTTP_OK)
            ->header('Content-Type', 'text/json');
    }

    /**
     * @param string $mobile
     * @param string $action
     * @param string $wallet
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function balance(string $action, string $mobile, string $wallet = 'default')
    {
        $contact = Contact::bearing($mobile) ?? Contact::create(compact('mobile'));
        $amount = $contact->getWallet($wallet)->balance;

        return response(json_encode(compact('mobile', 'action', 'amount', 'wallet')), Response::HTTP_OK)
            ->header('Content-Type', 'text/json');
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
        $origin = Contact::bearing($from);
        $destination = Contact::bearing($to);
        $origin_wallet = $this->wallet_transfer($origin, $destination, $wallet, $amount);
        $balance = $origin_wallet->balance;

        return response(json_encode(compact('from', 'to', 'action', 'amount', 'wallet', 'balance')), Response::HTTP_OK)
            ->header('Content-Type', 'text/json');
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
}
