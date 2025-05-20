<?php

namespace App\Http\Controllers;

use App\Models\FleetCustomer;
use App\Models\FleetUser;
use App\Models\Rider;
use Illuminate\Http\Request;

class UserManagementKpiController extends Controller
{
    public function userManagementKpi()
    {
        $totalUsers = FleetUser::withTrashed()->where('profile_type', 'profile')->count();

        $totalRider = Rider::withTrashed()->count();

        $totalCustomer = FleetCustomer::count();

        return $this->jsonResponse(
            HTTP_SUCCESS,
            'Kpi data retrieved succesfully',
            [
                'total_customer' => $totalCustomer ?? 0,
                'total_riders' => $totalRider ?? 0,
                'total_business_users' => $totalUsers ?? 0
            ]
        );
    }
}
