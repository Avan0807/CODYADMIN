<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Agent;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('agent.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'phone'    => 'required|regex:/^0[0-9]{9}$/',
            'password' => 'required',
        ]);

        $credentials = [
            'phone' => $request->phone,
            'password' => $request->password,
        ];

        // Tự viết custom guard login vì không phải field email
        $agent = Agent::where('phone', $request->phone)->first();

        if (!$agent || !\Hash::check($request->password, $agent->password)) {
            return back()->with('error', 'Số điện thoại hoặc mật khẩu không đúng.');
        }

        if ($agent->status !== 'active') {
            return back()->with('error', 'Tài khoản đã bị khóa.');
        }

        Auth::guard('agent')->login($agent);
        return redirect()->route('agent.dashboard');
    }

    public function logout(Request $request)
    {
        Auth::guard('agent')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('agent.login.form')->with('error', 'Đã đăng xuất.');
    }
}
