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
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function transfer(string $mobile, string $action, int $amount)
    {

        $contact = Contact::bearing($mobile) ?? Contact::create(compact('mobile'));
        $contact->{$action}($amount);

        return response(json_encode(compact('mobile', 'action', 'amount')), Response::HTTP_OK)
            ->header('Content-Type', 'text/json');
    }

    /**
     * @param string $mobile
     * @param string $action
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function balance(string $mobile, string $action)
    {

        $contact = Contact::bearing($mobile) ?? Contact::create(compact('mobile'));
        $amount = $contact->balance;

        return response(json_encode(compact('mobile', 'action', 'amount')), Response::HTTP_OK)
            ->header('Content-Type', 'text/json');
    }
}
