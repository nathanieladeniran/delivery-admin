<?php

namespace App\Http\Resources\Revenue;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $paymentPlan = $this->when($this->transactionable_type === Transaction::TYPE_SUBSCRIPTION, function () {
            return $this->transactionable;
        });

        return [
            'uuid' => $this->id,
            'invoice_number' => $this->reference,
            'user' => $this->fleetProfile->business_name,
            'plan_name' => $paymentPlan ? $paymentPlan->plan->name : null,
            'billing_cycle' => $paymentPlan ? $paymentPlan->plan->interval : null,
            'amount_paid' => $this->amount,
            'invoice_date' => $this->created_at,
            'expiry_date' => $paymentPlan->next_payment_date ?? null
        ];
    }
}
