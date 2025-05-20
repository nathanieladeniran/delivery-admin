<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Delivery;
use App\Models\Rider;
use App\Enums\DeliveryStatus;
use App\Http\Resources\DeliveryCollection;
use App\Http\Resources\DeliveryResources;
use App\Models\FleetCustomer;
use App\Models\FleetUser;
use App\Exports\DeliveryDataExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use PhpParser\Node\Stmt\TryCatch;

use function App\Helpers\paginate;
use function App\Helpers\pagination;

class DeliveryController extends Controller
{
    /**Get all deliveries */
    public function getDeliveries(Request $request)
    {
        $paginate = $request->paginate;
        $paginate = is_numeric($paginate) && $paginate > 0 ? (int)$paginate : 10;
        $status = $request->status;
        $search = $request->search;

        /**Delivery KPI */

        //all deliveries
        $totalDeliveries = Delivery::with(['customer', 'sender'])->count();

        //comleted deliveries
        $totalCompletedDeliveries = Delivery::with(['customer', 'sender'])->where('status_label', DeliveryStatus::DELIVERED->value)
            ->count();

        //pending deliveries
        $totalPendingDeliveries = Delivery::where('status_label', DeliveryStatus::PENDING->value)
            ->count();

        //canclelled deliveries
        $totalCancelledDeliveries = Delivery::where('status_label', DeliveryStatus::CANCELLED->value)
            ->count();
        /**Delivery KPI ends */

        //Awaiting deliveries
        $awaitingDeliveries = Delivery::where('status_label', DeliveryStatus::AWAITING_PICKUP->value)
            ->count();

        //Intransit deliveries
        $intransitDeliveries = Delivery::where('status_label', DeliveryStatus::IN_TRANSIT->value)
            ->count();

        //Total number of rider
        $allRiders = Rider::count();

        //Total users/Business Owners
        $allBusinessCustomers = FleetUser::count();

        //Total Customers
        $allCustomers = FleetCustomer::count();


        /**Fetching deliveries by status */

        $query = Delivery::with(['customer', 'sender']);

        // Apply status filter
        if ($status == 'delivered') {
            $query->where('status_label', DeliveryStatus::DELIVERED->value);
        } else if ($status == 'pending') {
            $query->where('status_label', DeliveryStatus::PENDING->value);
        } else if ($status == 'cancelled') {
            $query->where('status_label', DeliveryStatus::CANCELLED->value);
        }


        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'LIKE', "%{$search}%")->orWhere('created_at', 'LIKE', "%{$search}%")->orWhere('order_number', 'LIKE', "%{$search}%");
            });
        }

        // Paginate the results
        $deliveries = $query->paginate($paginate);


        $paginatedResponse = pagination($deliveries, new DeliveryCollection($deliveries));

        return $this->jsonResponse(
            HTTP_SUCCESS,
            "Data Retrieved",

            [
                'delivery_counts' => [
                    'all_user_deliveries' => $totalDeliveries,
                    'user_completed_deliveris' => $totalCompletedDeliveries,
                    'user_pending_deliveries' => $totalPendingDeliveries,
                    'user_cancelled_deliveries' => $totalCancelledDeliveries,
                    'user_awaiting_deliveries' => $awaitingDeliveries,
                    'user_intransit_deliveries' => $intransitDeliveries,
                    'all_customer' => $allCustomers,
                    'all_riders' => $allRiders ?? 0,
                    'all_business_users' => $allBusinessCustomers ?? 0,
                ],

                'all_deliveries' => $paginatedResponse
            ]
        );
    }

    public function getCompletedDeliveries(Request $request)
    {
        $per_page = $request->per_page;
        $per_page = is_numeric($per_page) && $per_page > 0 ? (int)$per_page : 10;

        $completedDeliveries = Delivery::with(['customer', 'sender'])
            ->where('status_label', DeliveryStatus::DELIVERED->value)
            ->paginate($per_page);

        $totalCompletedDeliveries = Delivery::where('status_label', DeliveryStatus::DELIVERED->value)
            ->count();

        $paginatedResponse = pagination($completedDeliveries, new DeliveryCollection($completedDeliveries));

        return $this->jsonResponse(HTTP_SUCCESS, "Data Retrieved", ['total_completed' => $totalCompletedDeliveries, 'completed_deliveries' => $paginatedResponse]);
    }

    public function getPendingDeliveries(Request $request)
    {
        $per_page = $request->per_page;
        $per_page = is_numeric($per_page) && $per_page > 0 ? (int)$per_page : 10;

        $pendingDeliveries = Delivery::with(['customer', 'sender'])
            ->where('status_label', DeliveryStatus::PENDING->value)
            ->paginate($per_page);

        $totalPendingDeliveries = Delivery::where('status_label', DeliveryStatus::PENDING->value)
            ->count();

        $paginatedResponse = pagination($pendingDeliveries, new DeliveryCollection($pendingDeliveries));

        return $this->jsonResponse(HTTP_SUCCESS, "Data Retrieved", ['total_pending' => $totalPendingDeliveries, 'pending_deliveries' => $paginatedResponse]);
    }

    public function getCancelledDeliveries(Request $request)
    {
        $per_page = $request->per_page;
        $per_page = is_numeric($per_page) && $per_page > 0 ? (int)$per_page : 10;

        $cancelledDeliveries = Delivery::with(['customer', 'sender'])
            ->where('status_label', DeliveryStatus::CANCELLED->value)
            ->paginate($per_page);

        $totalCancelledDeliveries = Delivery::where('status_label', DeliveryStatus::CANCELLED->value)
            ->count();


        $paginatedResponse = pagination($cancelledDeliveries, new DeliveryCollection($cancelledDeliveries));

        return $this->jsonResponse(HTTP_SUCCESS, "Data Retrieved", ['total_cancelled' => $totalCancelledDeliveries, 'pending_deliveries' => $paginatedResponse]);
    }

    public function getDeliveryByUuid($uuid)
    {
        // Find the delivery by UUID
        $delivery = Delivery::with(['customer', 'sender'])->where('id', $uuid)->first();

        // Check if the delivery exists
        if (!$delivery) {
            return $this->jsonResponse(HTTP_NOT_FOUND, "Delivery not found");
        }

        return $this->jsonResponse(HTTP_SUCCESS, "Data Retrieved", ['delivery' => new DeliveryResources($delivery)]);
    }

    public function deleteDeliveryByUuid($uuid)
    {
        try {
            // Find the delivery by UUID
            $delivery = Delivery::where('id', $uuid)->firstOrFail();

            // Check if the delivery exists
            if (!$delivery) {
                return $this->jsonResponse(HTTP_NOT_FOUND, "Delivery not found");
            }

            // Delete the delivery
            $delivery->delete();

            return $this->jsonResponse(HTTP_SUCCESS, "Delivery deleted successfully");
        } catch (\Exception $ex) {

            return $this->jsonResponse(HTTP_BAD_REQUEST, "An error occurred while trying to delete this delivery: " . $ex->getMessage());
        }
    }

    //Export data in excel/xlsx
    public function exportExcel()
    {
        $random = mt_rand();

        try {

            return Excel::download(new DeliveryDataExport, 'delivery' . $random . '.xlsx'); // format can be (csv/xlsx)
        } catch (\Exception $ex) {

            return $this->jsonResponse(HTTP_BAD_REQUEST, "Could not download file due to: " . $ex->getMessage());
        }
    }

    public function exportPdfFormat()
    {
        $random = mt_rand();
        //$pdfsFile =  Delivery::with(['customer', 'sender'])->get();
        $deliveries = Delivery::with([
            'customer:id,customer_name,customer_address,email,customer_phone_number',
            'sender:id,sender_name,sender_address,email,sender_phone'
        ])->get();

        // Generate HTML content for the PDF
        $html = view('pdf.deliveries', compact('deliveries'))->render();

        // Create a new PDF instance
        $pdf = Pdf::loadHTML($html);

        // Set paper size and orientation
        $pdf->setPaper('A4', 'portrait');

        // Stream the PDF as a download
        return $pdf->download('deliveries' . $random . '.pdf');
    }

    public function searchFilter(Request $request)
    {
        // Get query parameters for search and filtering
        $search = $request->search;
        $paginate = $request->paginate;
        $paginate = is_numeric($paginate) && $paginate > 0 ? (int)$paginate : 10;

        // Base query with eager loading of related models
        $query = Delivery::with([
            'customer:id,customer_name,customer_address,email,customer_phone_number',
            'sender:id,sender_name,sender_address,email,sender_phone'
        ]);

        // Apply search filter
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('customer', function ($query) use ($search) {
                    $query->where('customer_name', 'LIKE', "%{$search}%")
                        ->orWhere('customer_address', 'LIKE', "%{$search}%")
                        ->orWhere('email', 'LIKE', "%{$search}%")
                        ->orWhere('customer_phone_number', 'LIKE', "%{$search}%");
                })->orWhereHas('sender', function ($query) use ($search) {
                    $query->where('sender_name', 'LIKE', "%{$search}%")
                        ->orWhere('sender_address', 'LIKE', "%{$search}%")
                        ->orWhere('email', 'LIKE', "%{$search}%")
                        ->orWhere('sender_phone', 'LIKE', "%{$search}%");
                })->orWhere('created_at', 'LIKE', "%{$search}%")->orWhere('order_number', 'LIKE', "%{$search}%");
            });
        }

        // Paginate the results
        $deliveries = $query->paginate($paginate);

        $paginatedResponse = pagination($deliveries, new DeliveryCollection($deliveries), $search);

        return $this->jsonResponse(HTTP_SUCCESS, "Data Retrieved", ['result' => $paginatedResponse]);
    }

}
