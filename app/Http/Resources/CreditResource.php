<?php

namespace App\Http\Resources;

use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Resources\Json\JsonResource;

class CreditResource extends JsonResource
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
            'action' => $this->action,
            'amount' => $request->amount,
            'wallet' => $this->wallet->slug,
            'balance' => $this->payable->getWallet($this->wallet->slug)->balance,
            'uuid' => $this->uuid,
            'meta' => $this->meta,
            'created_at' => $this->created_at,
            'confirmed' => $this->confirmed
        ];
    }

    public function withResponse($request, $response)
    {
        parent::withResponse($request, $response);

        $response->setStatusCode(Response::HTTP_CREATED);
    }
}
