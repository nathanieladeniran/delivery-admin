<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\InvoiceResource;
use App\Models\PaymentPlan;
use Illuminate\Http\Request;

class SubscriptionInvoiceController extends Controller
{
    public function index(Request $request)
    {
        $invoice = PaymentPlan::whereNotNull('profile_id');

        if ($request->has('date')) {
            $invoice->whereDate('created_at', $request->input('date'));
        }

        if ($request->has('interval')) {
            $invoice->whereHas('plan', function ($query) use ($request) {
                $query->where('interval', $request->input('interval'));
            });
        }

        if ($request->has('name')) {
            $invoice->whereHas('plan', function ($query) use ($request) {
                $query->where('name', $request->input('name'));
            });
        }

        if ($request->has('paginate') && $request->paginate) {
            $paginate = $request->paginate;
            $paginate = is_numeric($paginate) && $paginate > 0 ? (int)$paginate : 10;

            $paginatedResults = $invoice->paginate($paginate);
            $data = InvoiceResource::collection($paginatedResults)->response()->getData();
        } else {
            $data = InvoiceResource::collection($invoice->get());
        }

        return $this->jsonResponse(HTTP_SUCCESS, 'Invoices retrieved successfully.', [$data]);
    }

    public function show(PaymentPlan $invoice)
    {
        return $this->jsonResponse(HTTP_SUCCESS, 'Invoices retrieved successfully.', [new InvoiceResource($invoice)]);
    }
}
