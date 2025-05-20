<?php

namespace App\Actions\AdminAuth;

use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Http\Requests\AdminLoginRequest;
use App\Traits\HasJsonResponse;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\AdminResources;
use Illuminate\Http\Request;

class AdminLogin
{
    use HasJsonResponse;
    /**
     * Create a new class instance.
     */
    public function loginAdmin(AdminLoginRequest $request)
    {
        $adminUser = User::where('email', $request->email)->first();
        
        if(!$adminUser || !Hash::check($request->password, $adminUser->password))
        {
            return $this->jsonResponse(HTTP_VALIDATION_ERROR, "Invalid Details Supplied.");
        }

            $token = $adminUser->createToken('adminAuthToken')->plainTextToken;
            $adminUser->token = $token;
            $adminUser->save();
            return $this->jsonResponse(HTTP_CREATED, "Login Successful.", [new AdminResources($adminUser)]);
    }

    public function getLoggedInAdmin(Request $request)
    {
        $loggedinAdmin = $request->user();
        return $this->jsonResponse(HTTP_CREATED, "Data retrieved.", [new AdminResources($loggedinAdmin)]);
        
    }
}
