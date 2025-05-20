<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Http\Requests\AdminLoginRequest;
use App\Actions\AdminAuth\AdminLogin;
use App\Http\Resources\AdminResources;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function signInAdmin(AdminLoginRequest $request)
    {
        $admin = (new AdminLogin())->loginAdmin($request);
        return $admin;
    }

    public function loggedInAdmin(Request $request)
    {
        $user = (new AdminLogin())->getLoggedInAdmin($request);
        return $user;
        
    }
}
