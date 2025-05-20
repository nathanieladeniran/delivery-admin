<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Revenue\ServiceResource;
use App\Http\Resources\Revenue\SmsResource;
use App\Http\Resources\Revenue\SubscriptionResource;
use App\Models\BulkSms;
use App\Models\Delivery;
use App\Models\DeliveryBooking;
use App\Models\DeliverySmsMessage;
use App\Models\ItemDetail;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class RevenueController extends Controller
{
    public function index()
    {
        $total_revenue = Transaction::whereNotNull('completed_at')->sum('amount');
        $total_sms = Transaction::whereNotNull('completed_at')->ofType(Transaction::TYPE_SMS_TOPUP)->sum('amount');
        $items_value = ItemDetail::whereHas('delivery', function ($query) {
            $query->where('status', Delivery::DELIVERED);
        })->sum('item_value');

        $data = [
            'total_revenue' => $total_revenue,
            'total_sms' => $total_sms,
            'items_value' => $items_value
        ];

        return $this->jsonResponse(HTTP_SUCCESS, 'Revenue metrics retrieved successfully.', [$data]);
    }

    public function service(Request $request)
    {
        $deliveryBookings = DeliveryBooking::whereHas('transaction', function ($query) {
            $query->whereNotNull('completed_at');
        });

        if ($request->has('date')) {
            $deliveryBookings->whereDate('created_at', $request->input('date'));
        }

        if ($request->has('order_number')) {
            $deliveryBookings->where('order_number', $request->input('order_number'));
        }

        if ($request->has('paginate') && $request->paginate) {
            $paginate = $request->paginate;
            $paginate = is_numeric($paginate) && $paginate > 0 ? (int)$paginate : 10;

            $paginatedResults = $deliveryBookings->paginate($paginate);
            $data = ServiceResource::collection($paginatedResults)->response()->getData();
        } else {
            $data = ServiceResource::collection($deliveryBookings->get());
        }

        return $this->jsonResponse(HTTP_SUCCESS, 'Service fees returned successfully.', [$data]);
    }

    public function sms(Request $request)
    {

        $bulkSmsQuery = BulkSms::where('status', BulkSms::SUCCESS_STATUS);

        if ($request->has('date')) {
            $bulkSmsQuery->whereDate('created_at', $request->input('date'));
        }

        $bulkSms = $bulkSmsQuery->get()->map(function ($sms) {
            return [
                'uuid' => $sms->id,
                'date' => $sms->created_at,
                'sms_fee' => $sms->total_sms_cost,
                'commission_fee' => $sms->service_charge,
                'sms_unit' => $sms->total_number_of_receivers,
                'no_of_characters' => strlen($sms->message),
            ];
        })->toArray();

        $deliverySmsQuery = DeliverySmsMessage::where('status', DeliverySmsMessage::DELIVERED_STATUS);

        if ($request->has('date')) {
            $deliverySmsQuery->whereDate('created_at', $request->input('date'));
        }

        $deliverySmsMessages = $deliverySmsQuery->get()->map(function ($sms) {
            return [
                'uuid' => $sms->id,
                'date' => $sms->created_at,
                'sms_fee' => $sms->sms_charge * $sms->number_of_pages,
                'commission_fee' => $sms->commission,
                'sms_unit' => 1,
                'no_of_characters' => strlen($sms->message),
            ];
        })->toArray();

        $merged = array_merge($bulkSms, $deliverySmsMessages);

        if ($request->has('paginate') && $request->paginate) {
            $paginate = $request->paginate;
            $paginate = is_numeric($paginate) && $paginate > 0 ? (int)$paginate : 10;

            $currentPage = LengthAwarePaginator::resolveCurrentPage();
            $collection = collect($merged);
            $perPage = $paginate;
            $currentItems = $collection->slice(($currentPage - 1) * $perPage, $perPage)->values();
            $paginatedResults = new LengthAwarePaginator($currentItems, $collection->count(), $perPage, $currentPage, [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
            ]);

            $data = $paginatedResults->toArray();
        } else {
            $data = $merged;
        }

        return $this->jsonResponse(HTTP_SUCCESS, 'SMS fees retrieved successfully.', [$data]);
    }


    public function subscription(Request $request)
    {
        $subscription = Transaction::whereNotNull('completed_at')
            ->ofType(Transaction::TYPE_SUBSCRIPTION);

        if ($request->has('date')) {
            $subscription->whereDate('created_at', $request->input('date'));
        }

        if ($request->has('invoice_number')) {
            $subscription->where('invoice_number', $request->input('invoice_number'));
        }

        if ($request->has('paginate') && $request->paginate) {
            $paginate = $request->paginate;
            $paginate = is_numeric($paginate) && $paginate > 0 ? (int)$paginate : 10;

            $paginatedResults = $subscription->paginate($paginate);
            $data = SubscriptionResource::collection($paginatedResults)->response()->getData();
        } else {
            $data = SubscriptionResource::collection($subscription->get());
        }

        return $this->jsonResponse(HTTP_SUCCESS, 'Subscription retrieved successully.', [$data]);
    }
}
