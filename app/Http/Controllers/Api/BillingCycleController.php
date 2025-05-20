<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BillingCycleController extends Controller
{
    public function index()
    {
        $billing_cycles = ['hourly', 'monthly', 'yearly', 'weekly', 'daily', 'quarterly', 'biannually'];

        return $this->jsonResponse(HTTP_SUCCESS, 'Billing cycle returned successfully.', [$billing_cycles]);
    }
}
