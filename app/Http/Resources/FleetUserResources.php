<?php

namespace App\Http\Resources;

use App\Models\Delivery;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\FleetProfile;
use App\Models\RiderOrganization;
use App\Models\PaymentPlan;
use App\Models\BulkSMS;
use App\Models\FleetRiderOrganization;
use App\Enums\DeliveryStatus;
use App\Models\FleetUser;
use App\Models\Transaction;

class FleetUserResources extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Fetch the profile data from the user database
        $profile = FleetProfile::where('id', $this->profile_id)->first();

        //get the total rider for this business
        $totalRiders = $profile ? RiderOrganization::where('fleet_identity_number', $profile->fleet_identity_number)->count() : 0;

        //get deliveries with various status for this business
        $completedDeliveries = $profile ? Delivery::where('profile_id', $this->profile_id)->where('status_label', DeliveryStatus::DELIVERED->value)->count() : 0;
        $pendingDeliveries = $profile ? Delivery::where('profile_id', $this->profile_id)->where('status_label', DeliveryStatus::PENDING->value)->count() : 0;
        $transitDeliveries = $profile ? Delivery::where('profile_id', $this->profile_id)->where('status_label', DeliveryStatus::IN_TRANSIT->value)->count() : 0;
        $canceledDeliveries = $profile ? Delivery::where('profile_id', $this->profile_id)->where('status_label', DeliveryStatus::CANCELLED->value)->count() : 0;
        $awaitingDeliveries = $profile ? Delivery::where('profile_id', $this->profile_id)->where('status_label', DeliveryStatus::AWAITING_PICKUP->value)->count() : 0;

        $totalDeliveries = $completedDeliveries + $pendingDeliveries + $transitDeliveries + $canceledDeliveries + $awaitingDeliveries;

        //Get Manual Deliveries
        $totalManualDeliery = Delivery::where('profile_id', $this->profile_id)->where('creation_mode', 'Manual')->count();

        //Get Delivery Via Bookings Count
        $totalBookingDelivery = Delivery::where('profile_id', $this->profile_id)->where('creation_mode', 'Via Booking')->count();

        //Get deliveries via Imported count
        $totalImportedDelivery = Delivery::where('profile_id', $this->profile_id)->where('creation_mode', 'Imported')->count();

        //Automatic deliveries
        $automaticDelivery = $totalBookingDelivery + $totalImportedDelivery;

        //Total Revenue
        $totalBusinessRevenue = $profile ? Transaction::where('profile_id', $this->profile_id)
                                                    ->where('payout_status', Transaction::SUCCESSFUL)
                                                    ->sum('user_received_amount') : 0;



        //fetch the subscription type of the business
        $paymentPlans = PaymentPlan::with('plan')
            ->where('profile_id', $this->profile_id)
            ->get();

        $plan = $paymentPlans->pluck('plan.name');

        //get total sms
        $totalSMS = $profile ? FleetUser::where('profile_id', $this->profile_id)->sum('sms_balance') : 0;

        //Total wrth of items delvered
        $totalItemWorth = Delivery::with('itemDetails')
            ->where('profile_id', $this->profile_id) // Assuming $this->id is the profile_id
            ->get()
            ->flatMap(function ($delivery) {
                return $delivery->itemDetails;
            })
            ->sum('item_value');

        return [
            'uuid' => $this->id,
            'profile_type' => $this->profile_type,
            'profile_id' => $this->profile_id,
            'email' => $this->email,
            'token' => $this->token,
            'email_verified_at'=> $this->email_verified_at,
            'live_api_key' => $this->live_api_key,
            'sandbox_api_key' => $this->sandbox_api_key,
            'profile' => new ProfileResources($profile),
            'total_riders' => $totalRiders,
            'total_revenue' => $totalBusinessRevenue,
            'total_deliveries' => $totalDeliveries,
            'completed_deliveries' => $completedDeliveries,
            'manual_delivery' => $totalManualDeliery,
            'automatic_delivery' => $automaticDelivery,
            'pending_deliveries' => $pendingDeliveries,
            'in_transit_deliveries' => $transitDeliveries,
            'cancelled_deliveries' => $canceledDeliveries,
            'pickup_deliveries' => $awaitingDeliveries,
            'subscription' => $plan,
            'no_of_sms' => $totalSMS,
            'total_worth_of_item' => $totalItemWorth,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'is_banned' => is_null($this->deleted_at) ? 'false' : 'true'
        ];
    }
}
