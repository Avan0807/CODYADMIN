<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use App\Models\User;
use Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\Doctor;
use Laravel\Socialite\Facades\Socialite;


class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = RouteServiceProvider::HOME;

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function redirect($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    public function Callback($provider)
    {
        $userSocial = Socialite::driver($provider)->stateless()->user();
        $users = User::where(['email' => $userSocial->getEmail()])->first();

        if ($users) {
            Auth::login($users);
            return redirect('/')->with('success', 'Bạn đã đăng nhập từ ' . $provider);
        } else {
            $user = User::create([
                'name' => $userSocial->getName(),
                'email' => $userSocial->getEmail(),
                'image' => $userSocial->getAvatar(),
                'provider_id' => $userSocial->getId(),
                'provider' => $provider,
            ]);
            return redirect()->route('home');
        }
    }


    // Define login credentials
    public function credentials(Request $request)
    {
        return [
            'phone' => $request->phone,
            'password' => $request->password,
            'status' => 'active',
            'role' => 'admin',
        ];
    }

    protected function validateLogin(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'password' => 'required|string',
        ]);
    }


    /**
     * Handle user login
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiLogin(Request $request)
    {
        $credentials = $request->only('phone', 'password');

        $user = User::where('phone', $credentials['phone'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Mật khẩu không đúng.',
            ], 401);
        }

        // Tạo token khi đăng nhập thành công
        $token = $user->createToken('authToken')->plainTextToken;

        return response()->json([
            'success' => true,
            'user' => $user,
            'token' => $token,
        ]);
    }

    /**
     * Handle user logout
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiLogout(Request $request)
    {
        try {
            // Delete current access token
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Đăng xuất thành công',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đăng xuất thất bại',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function apiDoctorLogin(Request $request)
    {
        try {
            // Step 1: Validate input
            $validator = Validator::make($request->all(), [
                'phone' => 'required|string',
                'password' => 'required|string',
            ], [
                'phone.required' => 'Số điện thoại không được để trống.',
                'password.required' => 'Mật khẩu không được để trống.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dữ liệu nhập vào không hợp lệ.',
                    'errors' => $validator->errors(),
                ], 422);
            }

            // Step 2: Tìm bác sĩ bằng số điện thoại
            $doctor = Doctor::where('phone', $request->phone)->first();

            if (!$doctor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Số điện thoại không tồn tại.',
                ], 404);
            }

            // Step 3: Kiểm tra mật khẩu
            if (!Hash::check($request->password, $doctor->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mật khẩu không đúng.',
                ], 401);
            }

            // Step 4: Kiểm tra trạng thái tài khoản
            if ($doctor->status !== 'active') {
                return response()->json([
                    'success' => false,
                    'message' => 'Tài khoản của bạn đang bị khóa. Vui lòng liên hệ admin.',
                ], 403);
            }

            // Step 5: Tạo token (Laravel Sanctum)
            $token = $doctor->createToken('authToken')->plainTextToken;

            // Step 6: Trả về tất cả các trường (trừ mật khẩu)
            return response()->json([
                'success' => true,
                'message' => 'Đăng nhập thành công.',
                'doctor' => [
                    'id' => $doctor->id,
                    'name' => $doctor->name,
                    'specialization' => $doctor->specialization,
                    'services' => $doctor->services,
                    'experience' => $doctor->experience,
                    'working_hours' => $doctor->working_hours,
                    'location' => $doctor->location,
                    'workplace' => $doctor->workplace,
                    'phone' => $doctor->phone,
                    'email' => $doctor->email,
                    'photo' => $doctor->photo,
                    'status' => $doctor->status,
                    'rating' => $doctor->rating,
                    'consultation_fee' => $doctor->consultation_fee,
                    'bio' => $doctor->bio,
                    'points' => $doctor->points,
                    'total_commission' => $doctor->total_commission,
                    'created_at' => $doctor->created_at,
                    'updated_at' => $doctor->updated_at,
                ],
                'token' => $token,
                'token_type' => 'Bearer',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đăng nhập thất bại.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
