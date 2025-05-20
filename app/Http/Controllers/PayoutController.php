<?php

namespace App\Http\Controllers;

use App\Http\Resources\PayoutCollection;
use App\Models\Transaction;
use Illuminate\Http\Request;

use function App\Helpers\pagination;

class PayoutController extends Controller
{
    //Get total payout and total next payout
    public function totalPayoutAndNextPayout()
    {
        $totalPayout = Transaction::where('payout_status', Transaction::SUCCESSFUL)->sum('user_received_amount');
        $totalNextPayout = Transaction::where('payout_status', Transaction::PENDING)->sum('user_received_amount');

        return $this->jsonResponse(HTTP_SUCCESS, "Data Retrieved", ['total_payout' => $totalPayout, 'total_next_payout' => $totalNextPayout]);
    }

    //Get total Next Payout
    public function userPayoutList(Request $request)
    {
        $paginate = $request->paginate;
        $paginate = is_numeric($paginate) && $paginate > 0 ? (int)$paginate : 10;

        $customerPayout = Transaction::paginate($paginate);

        $paginatedResponse = pagination($customerPayout, new PayoutCollection($customerPayout));

        return $this->jsonResponse(HTTP_SUCCESS, "Data Retrieved", ['customerpayouts' => $paginatedResponse]);
    }

    public function paymentHistory(Request $request, $profile_id)
    {
        $paginate = $request->paginate;
        $paginate = is_numeric($paginate) && $paginate > 0 ? (int)$paginate : 10;

        $payouts = Transaction::where('profile_id', $profile_id)
                            ->where('payout_status', Transaction::SUCCESSFUL)
                            ->paginate($paginate);

        $paginatedResponse = pagination($payouts, new PayoutCollection($payouts));

        return $this->jsonResponse(HTTP_SUCCESS, "Data Retrieved", ['payouts' => $paginatedResponse]);
    }

    //Payout Search endpoint
    public function searchFilter(Request $request)
    {
        // Get query parameters for search and filtering
        $search = $request->search;
        $paginate = $request->paginate;
        $paginate = is_numeric($paginate) && $paginate > 0 ? (int)$paginate : 10;

        $query = Transaction::query();
        // Apply search filter
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('profile', function ($query) use ($search) {
                    $query->where('business_name', 'LIKE', "%{$search}%");
                });
            });
        }

        // Paginate the results
        $payout = $query->paginate($paginate);

        $paginatedResponse = pagination($payout, new PayoutCollection($payout), $search);

        return $this->jsonResponse(HTTP_SUCCESS, "Data Retrieved", ['result' => $paginatedResponse]);
    }
}
