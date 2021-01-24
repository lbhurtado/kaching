<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TransferResource extends JsonResource
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
            'from' => $this->formatMobile($request->from),
            'to' => $this->formatMobile($request->to),
            'action' => $request->action,
            'amount' => $request->amount,
            'wallet' => $this->wallet,
            'balance' => $this->balance
        ];
    }

    public function __construct($resource, $wallet)
    {
        parent::__construct($resource);

        $this->wallet = $wallet;
    }

    protected function formatMobile($mobile)
    {
        return phone($mobile, config('kaching.country'))->formatE164();
    }
}
