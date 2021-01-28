<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Http\Resources\CreditResource;
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
            ->credit($amount, $wallet, $action, self::UNCONFIRMED);

        return response(new CreditResource($transaction), Response::HTTP_OK);
    }
}
