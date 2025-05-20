<?php

namespace App\Actions;

use App\Traits\HasJsonResponse;
use App\Http\Requests\AdminCreateRequest;
use App\Models\PasswordResetToken;
use App\Models\TempUser;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use App\Notifications\SendOtpNotification;
use App\Notifications\VerificationSucessNotification;
use Illuminate\Http\Request;
use App\Models\Profile;
use App\Notifications\PasswordChangedNotification;
use App\Notifications\ResetPasswordLink;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AddNewAdmin
{
    use HasJsonResponse;

    /**Create New admin account */
    public function createAdmin(AdminCreateRequest $request)
    {
        $adminUser = User::where('email', $request->email)->first();

        abort_if($adminUser, HTTP_BAD_REQUEST, "An admin acoount with this email already exist");

        $otp = random_int(10000, 99999);
        $saveData = TempUser::updateOrCreate(
            ['email' => $request->email],
            [
                'id' => Str::uuid()->toString(),
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'otp' => $otp,
                'password' => Hash::make($request->password)

            ]
        );

        if ($saveData) {
            $saveData->notify(new SendOtpNotification($otp, $saveData->first_name));
            return $this->jsonResponse(HTTP_SUCCESS, "Data Saved, and OTP send to the registered email for account validation", ['uuid' => $saveData->id]);
        }
    }

    public function verifyOtp(Request $request, $email)
    {
        // Validate the request data
        $request->validate([
            'otp' => 'required',
        ]);

        // Retrieve the OTP record
        $otpData = TempUser::where('email', $email)->first();

        if (!$otpData)
            return $this->jsonResponse(HTTP_BAD_REQUEST, "Invalid or expired OTP.");

        if ($request->otp != $otpData->otp)
            return $this->jsonResponse(HTTP_BAD_REQUEST, "Invalid or expired OTP.");


        $profile = Profile::create([
            'first_name' => $otpData->first_name,
            'last_name' => $otpData->last_name,
        ]);

        $password = $otpData->password;

        //Create User Instance
        $user = $profile->user()->create([
            'email' => strtolower($otpData->email),
            'password' => $password,
            'email_verified_at' => now(),
        ]);

        $user->refresh();
        $user->notify(new VerificationSucessNotification());

        $otpData->delete();
        return $this->jsonResponse(HTTP_SUCCESS, "Your admin account activated succesfully.", [$user]);
    }

    //Send Password reset link
    public function forgetPasswordLink(Request $request)
    {
        try {
            $user = User::where('email', $request->email)->first();
            abort_if(!$user, HTTP_VALIDATION_ERROR, 'Failed to send password link');

            //check if the email exist in the password reset table
            $check_record = PasswordResetToken::where('email', $request->email);
            if ($check_record->exists()) {
                $check_record->delete();
            }
            
            $token = random_int(10000, 99999);

            DB::table('password_reset_tokens')->insert([
                'email' => $request->email,
                'token' => $token,
                'created_at' => Carbon::now(),
            ]);

            $user->notify(new ResetPasswordLink($token, $request->email));
            return $this->jsonResponse(HTTP_SUCCESS, "A reset code has been sent to the registered email succesfully.", ['reset_token' => $token]);
        } catch (\Exception $e) {
            return $this->jsonResponse(HTTP_BAD_REQUEST, "Failed to reset password.");
        }
    }

    //verify password reset code
    public function verifyResetOtp(Request $request, string $email)
    {
        // Validate the request data
        $request->validate([
            'otp' => 'required',
        ]);

        // Retrieve the OTP record
        $otpData = PasswordResetToken::where('email', $email)->first();

        if (!$otpData)
            return $this->jsonResponse(HTTP_BAD_REQUEST, "Invalid OTP.");

        if ($request->otp != $otpData->token)
            return $this->jsonResponse(HTTP_BAD_REQUEST, "Invalid or expired OTP.");

        return $this->jsonResponse(HTTP_SUCCESS, "The OTP is valid.", ["email" => $email]);
    }

    //Reset Password
    public function resetPassword(string $email, string $reset_token, string $new_password)
    {
        try {
            //get user who request for a password reset from the password reset table
            $getUserRequest = PasswordResetToken::where([
                ['email', '=', $email],
                ['token', '=', $reset_token],
            ])->first();

            if (!$getUserRequest) {
                return $this->jsonResponse(HTTP_BAD_REQUEST, "Your record cannot be found, therefore password reste failed.");
            }

            $user = User::where('email', $email)->first();

            if ($user && ($reset_token == $getUserRequest->token)) {
                $user->password = Hash::make($new_password);
                $user->save();
            }

            $user->notify(new PasswordChangedNotification());
            return $this->jsonResponse(HTTP_SUCCESS, "Your password has been reset succesfully.");
        } catch (\Exception $e) {
            return $this->jsonResponse(HTTP_BAD_REQUEST, "Failed to reset password.");
        }
    }
}
