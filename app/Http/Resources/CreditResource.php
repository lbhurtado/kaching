<?php

namespace App\Http\Resources;

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
            'action' => $request->action,
            'amount' => $request->amount,
            'wallet' => $this->wallet->slug,
            'balance' => $this->payable->getWallet($this->wallet->slug)->balance,
            'uuid' => $this->uuid,
            'meta' => $this->meta,
            'created_at' => $this->created_at,
            'confirmed' => $this->confirmed
        ];
    }
}
