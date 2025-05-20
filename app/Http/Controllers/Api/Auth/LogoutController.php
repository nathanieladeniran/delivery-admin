<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LogoutController extends Controller
{
    public function logout(Request $request)
    {
        //$token = $request->bearerToken();
        if ($request->user()) {
            // Revoke the token that was used to authenticate the current request
            $request->user()->currentAccessToken()->delete();

            // Return a JSON response indicating successful logout
            return $this->jsonResponse(HTTP_CREATED, "Successfully logged out");
        }
    }
}
