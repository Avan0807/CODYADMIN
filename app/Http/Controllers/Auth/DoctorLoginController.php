<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DoctorLoginController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest:doctor')->except('logout');
    }

    public function showLoginForm()
    {
        return view('auth.doctor-login');
    }

    public function login(Request $request)
    {
        $this->validate($request, [
            'phone' => 'required',
            'password' => 'required|min:6',
        ]);

        if (Auth::guard('doctor')->attempt(['phone' => $request->phone, 'password' => $request->password])) {
            return redirect()->route('doctor.dashboard');
        }

        return redirect()->back()->with('error', 'Số điện thoại hoặc mật khẩu không đúng.');
    }

    public function logout()
    {
        Auth::guard('doctor')->logout();
        return redirect('/doctor/login');
    }
}

