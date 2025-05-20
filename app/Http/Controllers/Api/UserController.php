<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CustomerCollection;
use App\Http\Resources\FleetUserCollection;
use App\Http\Resources\FleetUserResources;
use App\Http\Resources\RiderCollection;
use App\Models\FleetCustomer;
use Illuminate\Http\Request;
use App\Models\FleetUser;
use App\Models\Rider;

use function App\Helpers\pagination;

class UserController extends Controller
{
    /**Fetch total number of users */
    public function getTotalUsers(Request $request)
    {
        $paginate = $request->paginate;
        $search = $request->search;
        $paginate = is_numeric($paginate) && $paginate > 0 ? (int)$paginate : 10;

        $allUsers = FleetUser::withTrashed()->where('profile_type', 'profile')->paginate($paginate);

        $totalUsers = FleetUser::withTrashed()->where('profile_type', 'profile')->count();

        if ($search) {

            $query = FleetUser::withTrashed();

            $query->where(function ($q) use ($search) {
                $q->whereHas('profile', function ($query) use ($search) {
                    $query->where('business_name', 'LIKE', "%{$search}%");
                })->orWhere('created_at', 'LIKE', "%{$search}%");
            });
            // Paginate the results
        $allUsers = $query->paginate($paginate);

        }
        
        $paginatedResponse = pagination($allUsers, new FleetUserCollection($allUsers));


        return $this->jsonResponse(HTTP_SUCCESS, "Data Retrieved", ['total_users' => $totalUsers, 'all_user' => $paginatedResponse]);
    }

    public function singleUser($uuid)
    {
        $singleUser = FleetUser::find($uuid);

        abort_if(!$singleUser, HTTP_NOT_FOUND, "User Data not can not be fetched at the moment");

        return $this->jsonResponse(HTTP_SUCCESS, "Data Retrieved", ['user_data' => new FleetUserResources($singleUser)]);
    }

    //ban users
    public function banUser($uuid)
    {
        $user = FleetUser::find($uuid);

        // Check if the user is already banned
        if (!$user) {

            return $this->jsonResponse(HTTP_BAD_REQUEST, "User is already banned");
        }

        // Ban the user
        $user->delete();

        return $this->jsonResponse(HTTP_SUCCESS, "User has been banned successfully.");
    }

    //Unban user
    public function unbanUser($uuid)
    {
        $user = FleetUser::withTrashed()->find($uuid);

        // Check if the user is not banned
        if (!$user->trashed()) {
            return $this->jsonResponse(HTTP_BAD_REQUEST, "User is not banned.");
        }

        // Unban the user by restoring the record
        $user->restore();

        return $this->jsonResponse(HTTP_SUCCESS, "User has been unbanned successfully.");
    }

    //rider serch endpoint
    public function searchFilter(Request $request)
    {
        // Get query parameters for search and filtering
        $search = $request->search;
        $for = $request->for;
        $paginate = $request->paginate;
        $paginate = is_numeric($paginate) && $paginate > 0 ? (int)$paginate : 10;

        //Search for business customer
            $query = FleetUser::withTrashed();

            // Apply search filter
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->whereHas('profile', function ($query) use ($search) {
                        $query->where('business_name', 'LIKE', "%{$search}%");
                    })->orWhere('created_at', 'LIKE', "%{$search}%");
                });
            }
            // Paginate the results
            $businessCustomers = $query->paginate($paginate);

            $paginatedResponse = pagination($businessCustomers, new FleetUserCollection($businessCustomers), $search);

            return $this->jsonResponse(HTTP_SUCCESS, "Data Retrieved", ['result' => $paginatedResponse]);
    }
}
