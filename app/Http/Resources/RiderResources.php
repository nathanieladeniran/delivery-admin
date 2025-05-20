<?php

namespace App\Http\Resources;

use App\Models\RiderDelivery;
use App\Models\RiderProfile;
use App\Models\FleetRiderOrganization;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Enums\DeliveryStatus;
use App\Models\Delivery;

class RiderResources extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Fetch the profile data from the user database
        $profile = RiderProfile::where('user_id', $this->id)->first();

        $riderDelivery = $profile ? RiderDelivery::where('rider_id', $profile->user_id)->get() : collect();
        $totalRiderDeliveries = $profile ? RiderDelivery::where('rider_id', $profile->user_id)->count() : 0;

        $completedDeliveries = $profile ? RiderDelivery::where('rider_id', $profile->user_id)->where('status', DeliveryStatus::DELIVERED->value)->count() : 0;
        $pendingDeliveries = $profile ? RiderDelivery::where('rider_id', $profile->user_id)->where('status', DeliveryStatus::PENDING->value)->count() : 0;
        $transitDeliveries = $profile ? RiderDelivery::where('rider_id', $profile->user_id)->where('status', DeliveryStatus::IN_TRANSIT->value)->count() : 0;
        $canceledDeliveries = $profile ? RiderDelivery::where('rider_id', $profile->user_id)->where('status', DeliveryStatus::CANCELLED->value)->count() : 0;
        $awaitingDeliveries = $profile ? RiderDelivery::where('rider_id', $profile->user_id)->where('status', DeliveryStatus::AWAITING_PICKUP->value)->count() : 0;

        //get completed deliverires
        $completedRiderDeliveries = $profile ? RiderDelivery::where('rider_id', $profile->user_id)
            ->where('status', DeliveryStatus::DELIVERED->value)
            ->count() : 0;


        //Get total revenue of rider
        //Total Revenue
        $totalRiderRevenue = $profile ? FleetRiderOrganization::where('rider_id', $profile->user_id)->sum('total_revenue') : 0;

        if ($riderDelivery->isEmpty()) {
            $totalManualDelivery = 0;
            $automaticDelivery = 0;
            $totalItemWorth = 0;
        } else {
            foreach ($riderDelivery as $riderDelivery) {
                //dd($riderDelivery->delivery_uuid);


                //  //Get Manual Deliveries 
                $totalManualDelivery = $riderDelivery->delivery_uuid  ? Delivery::where('id', $riderDelivery->delivery_uuid)->where('creation_mode', 'Manual')->count() : 0;

                //  //Get Delivery Via Bookings Count
                $totalBookingDelivery = $riderDelivery->delivery_uuid  ? Delivery::where('id', $riderDelivery->delivery_uuid)->where('creation_mode', 'Via Booking')->count() : 0;

                //Get deliveries via Imported count
                $totalImportedDelivery = $riderDelivery->delivery_uuid  ? Delivery::where('id', $riderDelivery->delivery_uuid)->where('creation_mode', 'Imported')->count() : 0;

                //Automatic deliveries
                $automaticDelivery = $totalBookingDelivery + $totalImportedDelivery;

                 //Total wrth of items delvered
                $totalItemWorth = $riderDelivery->delivery_uuid ? Delivery::with('itemDetails')
                ->where('id', $riderDelivery->delivery_uuid)
                ->get()
                ->flatMap(function ($delivery) {
                    return $delivery->itemDetails;
                })
                ->sum('item_value'): 0;

            }
        }


        return [
            'id' => $this->id,
            'email' => $this->email,
            'profile' => new RiderProfileResources($profile),
            'rider_deliveries' => $riderDelivery,
            'total_deliveries' => $totalRiderDeliveries,
            'total_revenue' => $totalRiderRevenue,
            'completed_deliveries' => $completedRiderDeliveries,
            'pending_deliveries' => $pendingDeliveries,
            'in_transit_deliveries' => $transitDeliveries,
            'cancelled_deliveries' => $canceledDeliveries,
            'pickup_deliveries' => $awaitingDeliveries,
            'manual_delivery' => $totalManualDelivery,
            'automatic_delivery' => $automaticDelivery,
            'total_worth_of_item' => $totalItemWorth,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'is_banned' => is_null($this->deleted_at) ? 'false' : 'true'
        ];
    }
}
