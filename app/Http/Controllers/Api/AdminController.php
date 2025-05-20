<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Actions\AddNewAdmin;
use App\Http\Requests\AdminCreateRequest;
use App\Http\Resources\AdminResources;

use function App\Helpers\pagination;

class AdminController extends Controller
{
    /**Add new Admin */
    public function addNewAdmin(AdminCreateRequest $request)
    {
        $newAdmin = (new AddNewAdmin())->createAdmin($request);
        return $newAdmin;
    }

    /**Otp verification */
    public function verifyAdmin(Request $request, $uuid)
    {
        $verify = (new AddNewAdmin())->verifyOtp($request, $uuid);
        return $verify;
    }

    /**Password Reset Otp verification */
    public function verifyPasswordResetOtp(Request $request, $email)
    {
        $verifyMe = (new AddNewAdmin())->verifyResetOtp($request, $email);
        return $verifyMe;
    }

    /**Fetch total number of users */
    public function getTotalAdminUsers(Request $request)
    {
        $paginate = $request->paginate;
        $paginate = is_numeric($paginate) && $paginate > 0 ? (int)$paginate : 10;

        $allAdmins = User::where('profile_type', 'aprofile')->paginate($paginate);
        $totalAdmins = User::where('profile_type', 'aprofile')->count();

        $paginatedResponse = pagination($allAdmins, AdminResources::collection($allAdmins));

        return $this->jsonResponse(HTTP_SUCCESS, "Data Retrieved", ['total_admins' => $totalAdmins, 'all_admins' => $paginatedResponse]);
    }

    /**Password reset link */
    public function sendResetLink(Request $request)
    {
        $sendLink = (new AddNewAdmin())->forgetPasswordLink($request);
        return $sendLink;
    }

    /**Password Change */
    public function completePasswordReset(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users',
            'new_password' => 'required|string|min:8',
            'reset_token' => 'required'
        ]);
        $resetPassword = (new AddNewAdmin())->resetPassword($request->email, $request->reset_token, $request->new_password);
        return $resetPassword;
    }
}
