<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MedicalRecord;
use App\Models\User;
use App\Models\Doctor;

class MedicalRecordController extends Controller
{

    public function apiGetMedicalRecordById($id)
    {
        try {
            // Lấy bệnh án theo ID
            $medicalRecord = MedicalRecord::with(['user', 'doctor'])->findOrFail($id);

            // Lấy thông tin người dùng hiện tại
            $user = auth()->user();

            // Phân quyền: Kiểm tra quyền truy cập
            if (
                $user->id !== $medicalRecord->user_id && // Không phải bệnh nhân
                $user->id !== $medicalRecord->doctor_id && // Không phải bác sĩ điều trị
                $user->role !== 'admin' // Không phải admin
            ) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn không có quyền truy cập bệnh án này!',
                ], 403);
            }

            // Trả về dữ liệu bệnh án
            return response()->json([
                'success' => true,
                'data' => $medicalRecord,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy bệnh án!',
                'error' => $e->getMessage(),
            ], 404);
        }
    }
    public function apiGetAllMedicalRecordsByUser($userId)
    {
        try {
            $medicalRecords = MedicalRecord::select('id', 'user_id', 'doctor_id', 'diagnosis', 'notes')
                ->where('user_id', $userId) // Lọc theo user_id
                ->with(['doctor:id,name']) // Chỉ lấy thông tin bác sĩ cần thiết
                ->get();

            if ($medicalRecords->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy bệnh án cho người dùng này!',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $medicalRecords,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể lấy bệnh án!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function apiCreateMedicalRecord(Request $request)
    {
        // Lấy thông tin bác sĩ từ token đã đăng nhập
        $doctor = $request->user(); // Lấy thông tin bác sĩ từ token đã xác thực

        // Kiểm tra nếu không có thông tin bác sĩ
        if (!$doctor) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền tạo bệnh án! Bạn không phải bác sĩ.',
            ], 403); // Lỗi 403 - Forbidden
        }

        // Xác thực dữ liệu đầu vào từ request
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id', // ID bệnh nhân phải tồn tại trong bảng `users`
            'diagnosis' => 'required|string|max:1000', // Chẩn đoán phải có
            'notes' => 'required|string|max:1000', // Kế hoạch điều trị phải có
        ]);

        try {
            // Tạo bệnh án mới với doctor_id lấy từ thông tin bác sĩ đã đăng nhập
            $medicalRecord = MedicalRecord::create([
                'user_id' => $validated['user_id'], // ID bệnh nhân
                'doctor_id' => $doctor->id, // Lấy doctorID từ thông tin bác sĩ đã đăng nhập
                'diagnosis' => $validated['diagnosis'], // Chẩn đoán
                'notes' => $validated['notes'], // Kế hoạch điều trị
            ]);

            return response()->json([
                'success' => true,
                'data' => $medicalRecord,
                'message' => 'Bệnh án đã được tạo thành công!',
            ], 201); // Mã 201 - Created
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể tạo bệnh án!',
                'error' => $e->getMessage(),
            ], 500); // Lỗi 500 - Server Error
        }
    }

    public function apiDeleteMedicalRecord($id, Request $request)
    {
        $doctor = $request->user();

        if (!$doctor) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền xóa bệnh án! Bạn không phải bác sĩ.',
            ], 403); // Lỗi 403 - Forbidden
        }

        // Tìm bản ghi bệnh án theo ID
        $medicalRecord = MedicalRecord::find($id);

        // Kiểm tra nếu bệnh án không tồn tại
        if (!$medicalRecord) {
            return response()->json([
                'success' => false,
                'message' => 'Bệnh án không tồn tại!',
            ], 404); // Lỗi 404 - Not Found
        }

        // Kiểm tra quyền của bác sĩ (nếu cần) - Ví dụ: chỉ bác sĩ đã tạo bệnh án mới được xóa
        if ($medicalRecord->doctor_id !== $doctor->id) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền xóa bệnh án này!',
            ], 403); // Lỗi 403 - Forbidden
        }

        // Xóa bệnh án
        try {
            $medicalRecord->delete();

            return response()->json([
                'success' => true,
                'message' => 'Bệnh án đã được xóa thành công!',
            ], 200); // Mã 200 - OK
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể xóa bệnh án!',
                'error' => $e->getMessage(),
            ], 500); // Lỗi 500 - Server Error
        }
    }
}
