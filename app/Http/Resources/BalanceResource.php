<?php

namespace App\Http\Resources;

use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Resources\Json\JsonResource;

class BalanceResource extends JsonResource
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
            'mobile' => $this->holder->mobile,
            'action' => $request->action,
            'amount' => $this->balance,
            'wallet' => $this->slug
        ];
    }

    public function withResponse($request, $response)
    {
        parent::withResponse($request, $response);

        $response->setStatusCode(Response::HTTP_OK);
    }
}
