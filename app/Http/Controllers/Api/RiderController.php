<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Rider;
use App\Http\Resources\RiderCollection;
use App\Http\Resources\RiderResources;
use App\Models\RiderProfile;

use function App\Helpers\pagination;

class RiderController extends Controller
{
    /**Fetch all Rider */
    public function getTotalRiders(Request $request)
    {
        $paginate = $request->paginate;
        $search = $request->search;
        $paginate = is_numeric($paginate) && $paginate > 0 ? (int)$paginate : 10;

        $riders = Rider::withTrashed()->paginate($paginate);
        $totalRider = Rider::withTrashed()->count();

        // Apply search filter
        if ($search) {
            $query = Rider::withTrashed();

            $query->where(function ($q) use ($search) {
                $q->whereHas('profile', function ($query) use ($search) {
                    $query->where('name', 'LIKE', "%{$search}%");
                })->orWhere('created_at', 'LIKE', "%{$search}%");
            });
            $riders = $query->paginate($paginate);
        }

        $paginatedResponse = pagination($riders, new RiderCollection($riders));

        return $this->jsonResponse(HTTP_SUCCESS, "Data Retrieved", ['total_rider' => $totalRider, 'riders_data' => $paginatedResponse]);
    }

    /**get single  */
    public function singleRider($uuid)
    {
        $singleRider = Rider::find($uuid);

        abort_if(!$singleRider, HTTP_NOT_FOUND, "Rider Data not can not be fetched at the moment");

        return $this->jsonResponse(HTTP_SUCCESS, "Data Retrieved", ['rider_data' => new RiderResources($singleRider)]);
    }

    //ban users
    public function banRider($uuid)
    {
        $rider = Rider::find($uuid);

        // Check if the user is already banned
        if (!$rider) {

            return $this->jsonResponse(HTTP_BAD_REQUEST, "Rider is already banned");
        }

        // Ban the user
        $rider->delete();

        return $this->jsonResponse(HTTP_SUCCESS, "Rider has been banned successfully.");
    }

    //Unban user
    public function unbanRider($uuid)
    {
        $rider = Rider::withTrashed()->find($uuid);

        // Check if the user is not banned
        if (!$rider->trashed()) {
            return $this->jsonResponse(HTTP_BAD_REQUEST, "Rider is not banned.");
        }

        // Unban the user by restoring the record
        $rider->restore();

        return $this->jsonResponse(HTTP_SUCCESS, "Rider has been unbanned successfully.");
    }

    //rider serch endpoint
    public function searchFilter(Request $request)
    {
        // Get query parameters for search and filtering
        $search = $request->search;
        $paginate = $request->paginate;
        $paginate = is_numeric($paginate) && $paginate > 0 ? (int)$paginate : 10;

        $query = Rider::withTrashed();

        // Apply search filter
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('profile', function ($query) use ($search) {
                    $query->where('name', 'LIKE', "%{$search}%");
                })->orWhere('created_at', 'LIKE', "%{$search}%");
            });
        }

        // Paginate the results
        $riders = $query->paginate($paginate);

        $paginatedResponse = pagination($riders, new RiderCollection($riders), $search);

        return $this->jsonResponse(HTTP_SUCCESS, "Data Retrieved", ['result' => $paginatedResponse]);
    }
}
