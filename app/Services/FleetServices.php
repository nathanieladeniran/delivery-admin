<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class FleetServices
{
    /**
     * Create a new class instance.
     */

    protected $baseUrl;
    protected $token;

    public function __construct()
    {
        $this->baseUrl = config('services.user_app.base_url');
        $this->token = config('services.user_app.token');
    }

}
