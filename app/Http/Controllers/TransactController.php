<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TransactController extends Controller
{
    /**
     * @param string $mobile
     * @param string $action
     * @param int $amount
     * @param string $wallet
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function credit(string $action, string $mobile, int $amount, string $wallet = 'default')
    {
        $contact = Contact::bearing($mobile) ?? Contact::create(compact('mobile'));
        $contact->getWallet($wallet)->{$action}($amount);

        return response(json_encode(compact('mobile', 'action', 'amount', 'wallet')), Response::HTTP_OK)
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
        $origin = Contact::bearing($from) ?? Contact::create(['mobile' => $from]);
        $destination = Contact::bearing($to) ?? Contact::create(['mobile' => $to]);

        $origin->getWallet($wallet)->transfer($destination->getWallet($wallet), $amount);

        return response(json_encode(compact('from', 'to', 'action', 'amount', 'wallet')), Response::HTTP_OK)
            ->header('Content-Type', 'text/json');
    }
}
