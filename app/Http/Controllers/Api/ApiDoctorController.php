<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Doctor;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Order;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ApiDoctorController extends Controller
{
    use HasApiTokens;

    public function __construct()
    {
        $this->middleware('auth')->except(['index', 'show', 'doctorLogin']);
    }

    // Lấy danh sách bác sĩ
    public function index()
    {
        $doctors = Doctor::all();
        return response()->json(['data' => $doctors], Response::HTTP_OK);
    }

    // Admin tạo tài khoản bác sĩ (Cập nhật đầy đủ các trường)

    public function createDoctor(Request $request)
    {
        // 1. Kiểm tra quyền admin
        if (!auth()->user() || auth()->user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền tạo tài khoản bác sĩ.',
            ], 403);
        }

        // 2. Xác thực dữ liệu đầu vào
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'specialization' => 'required|string',
            'services' => 'nullable|string',
            'experience' => 'required|integer',
            'working_hours' => 'nullable|string',
            'location' => 'nullable|string',
            'workplace' => 'nullable|string',
            'phone' => 'required|string|unique:doctors',
            'email' => 'required|email|unique:doctors',
            'photo' => 'nullable|file|mimes:jpg,jpeg,png,gif|max:2048',
            'status' => 'required|in:active,inactive',
            'rating' => 'nullable|numeric|min:0|max:5',
            'consultation_fee' => 'nullable|numeric|min:0',
            'bio' => 'nullable|string',
            'password' => 'required|string|min:6',
            'points' => 'nullable|integer|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // 3. Chuẩn bị dữ liệu lưu
            $doctorData = $request->except(['photo']);
            $doctorData['password'] = bcrypt($request->password);

            // 4. Upload ảnh lên S3 nếu có
            if ($request->hasFile('photo')) {
                $fileName = 'doctors/' . Str::slug(pathinfo($request->photo->getClientOriginalName(), PATHINFO_FILENAME))
                    . '-' . uniqid() . '.' . $request->photo->getClientOriginalExtension();

                // Lưu file vào S3
                $photoPath = $request->file('photo')->storeAs('doctors', $fileName, 's3');

                if (!$photoPath) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Upload ảnh thất bại!',
                    ], 500);
                }

                // Lưu đường dẫn URL public của ảnh
                $doctorData['photo'] = Storage::disk('s3')->url($photoPath);
            }

            // 5. Tạo tài khoản bác sĩ
            $doctor = Doctor::create($doctorData);

            return response()->json([
                'success' => true,
                'message' => 'Tài khoản bác sĩ đã được tạo thành công.',
                'doctor' => $doctor,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể tạo tài khoản bác sĩ!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    // Xem thông tin bác sĩ theo ID
    public function show($id)
    {
        $doctor = Doctor::find($id);
        if (!$doctor) {
            return response()->json(['message' => 'Không tìm thấy bác sĩ'], Response::HTTP_NOT_FOUND);
        }
        return response()->json(['data' => $doctor], Response::HTTP_OK);
    }

    // Cập nhật thông tin bác sĩ (Cập nhật đầy đủ các trường)
    public function update(Request $request, $id)
    {
        // 1. Tìm kiếm bác sĩ
        $doctor = Doctor::find($id);
        if (!$doctor) {
            return response()->json(['message' => 'Không tìm thấy bác sĩ'], Response::HTTP_NOT_FOUND);
        }

        // 2. Xác thực dữ liệu đầu vào
        $request->validate([
            'name' => 'required|string|max:255',
            'specialization' => 'required|string',
            'services' => 'nullable|string',
            'experience' => 'required|integer',
            'working_hours' => 'nullable|string',
            'location' => 'nullable|string',
            'workplace' => 'nullable|string',
            'phone' => 'required|string|unique:doctors,phone,' . $id,
            'email' => 'required|email|unique:doctors,email,' . $id,
            'photo' => 'nullable|file|mimes:jpg,jpeg,png|max:2048', // Sửa để nhận file upload
            'status' => 'required|in:active,inactive',
            'rating' => 'nullable|numeric|min:0|max:5',
            'consultation_fee' => 'nullable|numeric|min:0',
            'bio' => 'nullable|string',
            'password' => 'nullable|string|min:8',
            'points' => 'nullable|integer|min:0'
        ]);

        // 3. Chuẩn bị dữ liệu cập nhật (loại bỏ các trường đặc biệt)
        $updateData = $request->except(['photo', 'password']);

        // 4. Xử lý cập nhật mật khẩu nếu có
        if ($request->filled('password')) {
            $updateData['password'] = bcrypt($request->password);
        }

        // 5. Xử lý ảnh mới nếu có upload
        if ($request->hasFile('photo')) {
            // Xóa ảnh cũ trên S3 nếu tồn tại
            if ($doctor->photo && Storage::disk('s3')->exists($doctor->photo)) {
                Storage::disk('s3')->delete($doctor->photo);
            }

            // Upload ảnh mới lên S3
            $photoPath = $request->file('photo')->store('doctors', 's3');
            $updateData['photo'] = $photoPath;
        }

        // 6. Cập nhật thông tin bác sĩ
        $doctor->update($updateData);

        // 7. Trả về URL ảnh đầy đủ từ S3
        if ($doctor->photo) {
            $doctor->photo = Storage::disk('s3')->url($doctor->photo);
        }

        return response()->json([
            'message' => 'Thông tin bác sĩ đã được cập nhật',
            'data' => $doctor
        ], 200);
    }


    // Xóa bác sĩ (Admin)
    public function deleteDoctor($id)
    {
        if (!auth()->user() || auth()->user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền xóa bác sĩ.',
            ], 403);
        }

        $doctor = Doctor::find($id);
        if (!$doctor) {
            return response()->json(['message' => 'Không tìm thấy bác sĩ'], Response::HTTP_NOT_FOUND);
        }

        $doctor->delete();
        return response()->json(['message' => 'Tài khoản bác sĩ đã bị xóa.'], Response::HTTP_OK);
    }

    // Đăng nhập bác sĩ bằng số điện thoại và mật khẩu
    public function doctorLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $doctor = Doctor::where('phone', $request->phone)->first();

        if (!$doctor) {
            return response()->json([
                'success' => false,
                'message' => 'Số điện thoại không tồn tại.',
            ], 404);
        }

        if ($doctor->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Tài khoản chưa được kích hoạt.',
            ], 403);
        }

        if (!Hash::check($request->password, $doctor->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Mật khẩu không đúng.',
            ], 401);
        }

        $token = $doctor->createToken('doctorAuthToken')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Đăng nhập thành công.',
            'doctor' => $doctor,
            'token' => $token,
            'token_type' => 'Bearer',
        ], 200);
    }

    // Đăng xuất bác sĩ
    public function doctorLogout(Request $request)
    {
        try {
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

    public function orders(Request $request, $doctor_id)
    {
        // Kiểm tra xem bác sĩ đang đăng nhập có ID trùng với ID được truyền vào không
        $doctor = auth()->user();

        if (!$doctor || $doctor->id != $doctor_id) {
            return response()->json([
                'error' => 'Bạn không có quyền truy cập đơn hàng này!'
            ], 403);
        }

        // Lấy danh sách đơn hàng của bác sĩ
        $orders = Order::where('doctor_id', $doctor_id)->get();

        return response()->json([
            'message' => 'Danh sách đơn hàng của bác sĩ',
            'orders' => $orders
        ]);
    }


    public function requestPayout(Request $request) {
        $doctorID = Auth::id();
        $doctor = \DB::table('doctors')->where('id', $doctorID)->first();

        if (!$doctor) {
            return response()->json(['error' => 'Không tìm thấy bác sĩ.'], 404);
        }

        if ($doctor->total_commission < 500000) {
            return response()->json(['error' => 'Bạn cần ít nhất 500,000đ để rút tiền.'], 400);
        }

        // Tạo yêu cầu rút tiền với số tiền bằng total_commission hiện có
        \DB::table('doctor_payouts')->insert([
            'doctor_id' => $doctorID,
            'amount' => $doctor->total_commission,
            'status' => 'pending',
            'created_at' => now()
        ]);

        return response()->json([
            'message' => 'Yêu cầu rút tiền của bạn đã được gửi.',
            'amount' => $doctor->total_commission
        ], 200);
    }

}
