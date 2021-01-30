<?php

namespace App\Http\Resources;

use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Resources\Json\JsonResource;

class BalancesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'mobile' => $request->mobile,
            'date' => now(),
            'balances' => $this->resource->toArray()
        ];
    }

    public function withResponse($request, $response)
    {
        parent::withResponse($request, $response);

        $response->setStatusCode(Response::HTTP_OK);
    }

    protected function getAction()
    {
        return config('kaching.keywords.transactions.balance');
    }
}
