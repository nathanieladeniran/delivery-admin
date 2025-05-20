<?php

namespace App\Http\Resources\Revenue;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SmsResource extends JsonResource
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
            'date' => $this->created_at,
            'sms_fee' => $this->total_sms_cost,
            'commission_fee' => $this->service_charge,
            'sms_unit' => $this->total_number_of_receivers,
            'no_of_characters' => $this->no_of_characters
        ];
    }
}
