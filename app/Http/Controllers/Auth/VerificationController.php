<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class VerificationController extends Controller
{
    /**
     * Where to redirect users after verification.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('throttle:6,1')->only('verify', 'resend');
    }

    /**
     * Verify the phone number with the provided OTP.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function verify(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'verification_code' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        if ($user->verification_code == $request->verification_code) {
            $user->phone_verified_at = now();
            $user->verification_code = null; // Clear the verification code after successful verification
            $user->save();

            return redirect($this->redirectTo)->with('status', 'Số điện thoại của bạn đã được xác minh thành công.');
        }

        return back()->withErrors(['verification_code' => 'Mã xác minh không đúng.']);
    }

    /**
     * Resend the verification code.
     *
     * @return \Illuminate\Http\Response
     */
    public function resend()
    {
        $user = Auth::user();

        // Tạo mã xác minh mới
        $verificationCode = rand(100000, 999999);
        $user->verification_code = $verificationCode;
        $user->save();

        // Gửi mã xác minh qua SMS (sử dụng dịch vụ SMS như Twilio, Nexmo, etc.)
        SMSService::send($user->phone, "Mã xác minh của bạn là: $verificationCode");

        return back()->with('resent', true);
    }
}
