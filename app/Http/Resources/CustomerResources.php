<?php

namespace App\Http\Resources;

use App\Models\Delivery;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Enums\DeliveryStatus;

class CustomerResources extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        //get the total delivery for a customer
        $totalCustomerDelivery = Delivery::where('customer_detail_id', $this->id)->count();

        //total completed delvery
        $totalCustomerCompletedDelivery = Delivery::where('customer_detail_id', $this->id)
            ->where('status_label', DeliveryStatus::DELIVERED->value)
            ->count();

        return [
            'id' => $this->id,
            'profile_id' => $this->profile_id,
            'customer_name' => $this->customer_name,
            'email' => $this->email,
            'customer_phone_number' => $this->customer_phone_number,
            'customer_phonecode' => $this->customer_phonecode,
            'customer_address' => $this->customer_address,
            'delivery_booked' => $totalCustomerDelivery,
            'completed_deliveries' => $totalCustomerCompletedDelivery,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at
        ];
    }
}
