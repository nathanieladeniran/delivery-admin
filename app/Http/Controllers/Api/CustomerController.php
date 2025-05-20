<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CustomerCollection;
use App\Http\Resources\CustomerResources;
use App\Http\Resources\FleetUserCollection;
use App\Models\FleetCustomer;
use Illuminate\Http\Request;

use function App\Helpers\pagination;

class CustomerController extends Controller
{
    /**Fetch total number of users */
    public function getTotalCustomers(Request $request)
    {
        $paginate = $request->paginate;
        $search = $request->search;
        $paginate = is_numeric($paginate) && $paginate > 0 ? (int)$paginate : 10;

        $allCustomers = FleetCustomer::paginate($paginate);

        $totalCustomer = FleetCustomer::count();

        if ($search) {

            $query = FleetCustomer::withTrashed();

            $query->where(function ($q) use ($search) {
                $q->where('customer_name', 'LIKE', "%{$search}%") 
                  ->orWhere('email', 'LIKE', "%{$search}%") 
                  ->orWhere('customer_phone_number', 'LIKE', "%{$search}%")
                  ->orWhere('created_at', 'LIKE', "%{$search}%"); 
            });
            // Paginate the results
        $allCustomers = $query->paginate($paginate);
        }       

        $paginatedResponse = pagination($allCustomers, new CustomerCollection($allCustomers));

        return $this->jsonResponse(HTTP_SUCCESS, "Data Retrieved", ['total_customer' => $totalCustomer,'customer_detail' => $paginatedResponse]);
    }

    public function singleCustomer($uuid)
    {
        $singleCustomer = FleetCustomer::find($uuid);

        abort_if(!$singleCustomer, HTTP_NOT_FOUND, "Customer Data not can not be fetched at the moment");

        return $this->jsonResponse(HTTP_SUCCESS, "Data Retrieved", ['customer_data' => new CustomerResources($singleCustomer)]);
    }

    //customer serch endpoint
    public function searchFilter(Request $request)
    {
        // Get query parameters for search and filtering
        $search = $request->search;
        $paginate = $request->paginate;
        $paginate = is_numeric($paginate) && $paginate > 0 ? (int)$paginate : 10;

        $query = FleetCustomer::withTrashed();

        // Apply search filter
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('customer_name', 'LIKE', "%{$search}%") 
                  ->orWhere('email', 'LIKE', "%{$search}%") 
                  ->orWhere('customer_phone_number', 'LIKE', "%{$search}%")
                  ->orWhere('created_at', 'LIKE', "%{$search}%"); 
            });
        }
        
        // Paginate the results
        $customers = $query->paginate($paginate);

        $paginatedResponse = pagination($customers, new CustomerCollection($customers), $search);

        return $this->jsonResponse(HTTP_SUCCESS, "Data Retrieved", ['result' => $paginatedResponse]);
    }
}
