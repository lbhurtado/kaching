<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BalanceResource extends JsonResource
{
    protected $wallet;

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'mobile' => $this->mobile,
            'action' => $request->action,
            'amount' => $this->getWallet($this->wallet)->balance,
            'wallet' => $this->wallet
        ];
    }

    public function __construct($resource, $wallet)
    {
        parent::__construct($resource);

        $this->wallet = $wallet;
    }
}
