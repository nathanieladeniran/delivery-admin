<?php

namespace App\Http\Resources\Revenue;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $transactionable = $this->transaction;

        return [
            'uuid' => $this->id,
            'order_number' => $this->order_number,
            'order_date' => $this->created_at,
            'delivery_fee' => $this->delivery_fee,
            'commission' => $this->service_charge,
            'status' => $transactionable ? ($transactionable->payin_status == 'successful' ? 'paid' : 'unpaid' ) : null,
        ];
    }
}
