<?php

namespace App\Services\Paystack;

use App\Services\BaseService;
use Illuminate\Support\Facades\Http;

class Plan extends BaseService
{
    public function __construct()
    {
        parent::__construct();
        $this->setHeaders([
            'Authorization' => 'Bearer ' . config('paystack.api_key'),
            'Accept' => 'application/json',
        ]);
    }

    protected function baseUri()
    {
        return config('paystack.base_url');
    }

    public function create($request)
    {
        $data = [
            "name" => $request->title,
            "interval" => $request->billing_cycle,
            "amount" => $request->price * 100,
            "description" => $request->description ?? null,
            "send_invoices" => $request->send_invoices ?? true,
            "send_sms" => $request->send_sms ?? true
        ];

        return $this->makeRequest('POST', "/plan", [
            'data' => $data
        ]);
    }

    public function update($data, $plan_code)
    {
        $data = [
            "name" => $data->name,
            "interval" => $data->interval,
            "amount" => $data->amount * 100,
            "description" => $data->description ?? null,
            "send_invoices" => $data->send_invoices ?? true,
            "send_sms" => $data->send_sms ?? true
        ];

        return $this->makeRequest('PUT', "/plan/{$plan_code}", [
            'data' => $data
        ]);
    }
}
