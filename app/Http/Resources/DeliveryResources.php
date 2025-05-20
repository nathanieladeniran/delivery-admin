<?php

namespace App\Http\Resources;

use App\Models\CustomerDetail;
use App\Models\SenderDetail;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DeliveryResources extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $transactionStatus = Transaction::where('delivery_id', $this->id)->first();
        $payStatus = $transactionStatus && $transactionStatus->payin_status !== null ? $transactionStatus->payin_status : Transaction::PENDING;

        return [
            'uuid' => $this->id,
            'order_number' => $this->order_number,
            'status' => ucfirst($this->status),
            'status_label' => $this->status_label,
            'delivery_type' => $this->delivery_type,
            'sender_longitude' => $this->sender_longitude,
            'sender_latitude' => $this->sender_latitude,
            'customer_longitude' => $this->customer_longitude,
            'customer_latitude' => $this->customer_latitude,
            'delivery_fee' => $this->delivery_fee,
            'service_charge' => $this->service_charge,
            'note' => $this->note,
            'creation_mode' => $this->creation_mode,
            'created_at' => $this->created_at,
            'pickup_at' => $this->picked_up_at,
            'dropoff_at' => $this->dropoff_at,
            'payment_status' => $payStatus,
            'customer_details' => new CustomerResources($this->whenLoaded('customer')),
            'sender_details' => new SenderResources($this->whenLoaded('sender')),
        ];
    }
}
