<?php

namespace App\Http\Controllers;

use App\Enums\DeliveryStatus;
use App\Models\Delivery;
use App\Models\FleetCustomer;
use App\Models\FleetUser;
use App\Models\Rider;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardKpiController extends Controller
{
    public function showDeliveryKpi()
    {
        //all deliveries
        $totalDeliveries = Delivery::with(['customer', 'sender'])->count();

        //comleted deliveries
        $completedDeliveries = Delivery::with(['customer', 'sender'])->where('status_label', DeliveryStatus::DELIVERED->value)
            ->count();

        //pending deliveries
        $pendingDeliveries = Delivery::where('status_label', DeliveryStatus::PENDING->value)
            ->count();

        //canclelled deliveries
        $cancelledDeliveries = Delivery::where('status_label', DeliveryStatus::CANCELLED->value)
            ->count();
        
        //Awaiting deliveries
        $awaitingDeliveries = Delivery::where('status_label', DeliveryStatus::AWAITING_PICKUP->value)
            ->count();

        //Intransit deliveries
        $intransitDeliveries = Delivery::where('status_label', DeliveryStatus::IN_TRANSIT->value)
            ->count();

        //Total number of rider
        $allRiders = Rider::withTrashed()->count();

        //Total users/Business Owners
        $allBusinessCustomers = FleetUser::withTrashed()->count();

        //Total Customers
        $allCustomers = FleetCustomer::count();

        //Perccentage increase
        $currentMonth = Carbon::now()->startOfMonth();
        $previousMonth = Carbon::now()->subMonth()->startOfMonth();

        /**Percentage increase section */

        //Monthly Delivery Increament percentage 
        $currentMonthDeliveries = Delivery::with(['customer', 'sender'])->where('created_at', '>=', $currentMonth)->count();
        $previousMonthDeliveries = Delivery::with(['customer', 'sender'])->where('created_at', '>=', $previousMonth)->where('created_at', '<', $currentMonth)->count();
        $monthDeliveriesIncrease = $this->getPercentageIncrease($previousMonthDeliveries, $currentMonthDeliveries);

        //Monthly User Increament percentage 
        $currentMonthUser = FleetCustomer::where('created_at', '>=', $currentMonth)->count();
        $previousMonthUser = FleetCustomer::where('created_at', '>=', $previousMonth)->where('created_at', '<', $currentMonth)->count();
        $monthUserIncrease = $this->getPercentageIncrease($previousMonthUser, $currentMonthUser);

        //Monthly Rider Increament percentage 
        $currentMonthRider = Rider::where('created_at', '>=', $currentMonth)->count();
        $previousMonthRider = Rider::where('created_at', '>=', $previousMonth)->where('created_at', '<', $currentMonth)->count();
        $monthRiderIncrease = $this->getPercentageIncrease($previousMonthRider, $currentMonthRider);

        //Monthly Completed Deliveries Increament percentage 
        $currentMonthCompletedDeliveries = Delivery::with(['customer', 'sender'])->where('status_label', DeliveryStatus::DELIVERED->value)
            ->where('created_at', '>=', $currentMonth)->count();
        $previousMonthCompletedDeliveries = Delivery::with(['customer', 'sender'])->where('status_label', DeliveryStatus::DELIVERED->value)
            ->where('created_at', '>=', $previousMonth)->where('created_at', '<', $currentMonth)->count();
        $monthCompletedDeliveriesIncrease = $this->getPercentageIncrease($previousMonthCompletedDeliveries, $currentMonthCompletedDeliveries);

        /**percentage increase ends */


        return $this->jsonResponse(
            HTTP_SUCCESS,
            'Kpi data retrieved succesfully',
            [
                'all_deliveries' => $totalDeliveries ?? 0,
                'total_completed_deliveries' => $completedDeliveries ?? 0,
                'total_pending_deliveries' => $pendingDeliveries ?? 0,
                'total_cancelled_delivery' => $cancelledDeliveries ?? 0,
                'total_awaiting_delivery' => $awaitingDeliveries ?? 0,
                'total_intransit_delivery' => $intransitDeliveries ?? 0,
                'all_customer' => $allCustomers,
                'all_riders' => $allRiders ?? 0,
                'all_business_users' => $allBusinessCustomers ?? 0,
                'percentage_delivery_increase' => $monthDeliveriesIncrease,
                'percentage_user_increase' => $monthUserIncrease,
                'percentage_rider_increase' => $monthRiderIncrease,
                'percentage_completed_deliveries_increase' => $monthCompletedDeliveriesIncrease,
            ]
        );
    }

    private function getPercentageIncrease($old, $new)
    {
        if ($old == 0) {
            return $new > 0 ? 100 : 0;
        }

        return (($new - $old) / $old) * 100;
    }
}
