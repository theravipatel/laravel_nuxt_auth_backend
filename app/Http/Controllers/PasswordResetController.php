<?php

namespace App\Http\Controllers;

use App\Mail\SendPasswordResetCode;
use App\Models\PasswordResetCode;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class PasswordResetController extends Controller
{
    public function generateCode(Request $request) {
        $data = $request->validate([
            'email' => 'required|email|exists:users',
        ]);

        // Delete all old code that user send before.
        PasswordResetCode::where('email', $request->email)->delete();

        // Generate random code
        $data['code'] = mt_rand(100000, 999999);

        // Create a new code
        $codeData = PasswordResetCode::create($data);

        // Send email to user
        Mail::to($request->email)->send(new SendPasswordResetCode($codeData->code));

        return response([
            'message' => trans('passwords.sent')
        ], 200);
    }

    public function codeCheck(Request $request) {
        $request->validate([
            'code' => 'required|string|exists:password_reset_codes',
        ]);

        // find the code
        $passwordReset = PasswordResetCode::firstWhere('code', $request->code);

        // check if it does not expired: the time is one hour
        if ($passwordReset->created_at > now()->addHour()) {
            $passwordReset->delete();
            return response([
                'message' => trans('passwords.code_is_expire')
            ], 422);
        }

        return response([
            'code' => $passwordReset->code,
            'message' => trans('passwords.code_is_valid')
        ], 200);
    }

    public function resetPassword(Request $request) {
        $request->validate([
            'code' => 'required|string|exists:password_reset_codes',
            'password' => 'required|string|min:6|confirmed',
        ]);

        // find the code
        $passwordReset = PasswordResetCode::firstWhere('code', $request->code);

        // check if it does not expired: the time is one hour
        if ($passwordReset->created_at > now()->addHour()) {
            $passwordReset->delete();
            return response([
                'message' => trans('passwords.code_is_expire')], 422);
        }

        // find user's email 
        $user = User::firstWhere('email', $passwordReset->email);

        // update user password
        $user->password = bcrypt($request->password);
        $user->save();

        // delete current code 
        $passwordReset->delete();

        return response([
            'message' =>'password has been successfully reset'
        ], 200);
    }
}
