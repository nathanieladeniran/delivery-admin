<?php

namespace App\Http\Resources;

use App\Models\FleetProfile;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Transaction;
use Carbon\Carbon;

class PayoutResources extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $profile = FleetProfile::where('id', $this->profile_id)->first();

        $totalPayout = $profile ? Transaction::where('profile_id', $this->profile_id)
                                    ->where('payout_status', Transaction::SUCCESSFUL)
                                    ->sum('user_received_amount') : 0;
        $totalNextPayout = $profile ? Transaction::where('profile_id', $this->profile_id)
                            ->where('payout_status', Transaction::PENDING)
                            ->sum('user_received_amount') : 0;

        return [
            "id" => $this->id,
            "profile_id" => $this->profile_id,
            "payment_gateway_id" => $this->payment_gateway_id,
            "reference" => $this->reference,
            "metas" => $this->metas,
            "amount"=> $this->amount,
            "user_gets" => $this->user_received_amount,
            "total_payout" => $totalPayout,
            "total_next_payout" => $totalNextPayout,
            "next_payout_date" => Carbon::now()->addDay()->format('Y-m-d'),
            "profile" => new ProfileResources($profile),
            "completed_at" => $this->completed_at,
            "transaction_type" => $this->transaction_type,
            "payin_status" => $this->payin_status,
            "payin_at" => $this->payin_at,
            "payin_name" => $this->payin_name,
            "note" => $this->note,
            "delivery_id" => $this->delivery_id,
            "payout_at" => $this->payout_at,
            "payout_status" => $this->payout_status,
            "payment_detail_id" => $this->payment_detail_id,
            "transactionable_type" => $this->transactionable_type,
            "transactionable_id"=> $this->transactionable_id,
            "card_type"=> $this->card_type,
            "last4" => $this->last4,
            "card_expiry_year" => $this->card_expire_year,
            "card_expiry_month" => $this->card_expiry_month,
            "user_received_amount"=> $this->user_received_amount,
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at,
            "deleted_at" => $this->deleted_At,
        ];
    }
}
