<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TransferResource extends JsonResource
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
            'from' => $this->formatMobile($request->from),
            'to' => $this->formatMobile($request->to),
            'action' => 'transfer',
            'amount' => $request->amount,
            'wallet' => $this->slug,
            'balance' => $this->balance
        ];
    }

    protected function formatMobile($mobile)
    {
        return phone($mobile, config('kaching.country'))->formatE164();
    }
}
