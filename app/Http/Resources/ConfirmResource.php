<?php

namespace App\Http\Resources;

use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Resources\Json\JsonResource;

class ConfirmResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'mobile' => $this->payable->mobile,
            'action' => config('kaching.keywords.transactions.confirm'),
            'amount' => $this->amount,
            'wallet' => $this->wallet->slug,
            'balance' => $this->wallet->balance,
            'confirmed' => $this->confirmed,
        ];
    }

    public function withResponse($request, $response)
    {
        parent::withResponse($request, $response);

        $response->setStatusCode(Response::HTTP_OK);
    }
}
