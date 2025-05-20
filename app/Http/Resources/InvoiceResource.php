<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->id,
            'plan_info' => $this->plan,
            'profile' => $this->profile,
            'amount_paid' => $this->transactionable()->first()->amount,
            'date' => $this->created_at,
            'expired_at' => $this->next_payment_date,
        ];
    }
}
