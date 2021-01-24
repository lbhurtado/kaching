<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ConfirmResource extends JsonResource
{
    protected $action;

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
            'amount' => $this->amount,
            'wallet' => $this->wallet->slug,
            'balance' => $this->wallet->balance,
            'confirmed' => $this->confirmed,
        ];
    }

    public function __construct($resource, $action)
    {
        parent::__construct($resource);

        $this->action = $action;
    }
}
