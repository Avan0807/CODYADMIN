<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TreatmentLog;
use App\Models\MedicalRecord;
use App\Models\Doctor;


class TreatmentLogController extends Controller
{
    public function apiGetTreatmentLogsByMedicalRecord($medical_record_id, Request $request)
    {
        // Kiểm tra nếu người dùng đã đăng nhập và lấy thông tin người dùng
        $user = $request->user(); // Lấy thông tin người dùng từ token (cả doctor và user)

        // Kiểm tra xem người dùng có phải bác sĩ hay bệnh nhân
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn phải đăng nhập để xem thông tin bệnh án!',
            ], 401); // Lỗi 401 - Unauthorized
        }

        // Tìm tất cả bản ghi điều trị cho medical_record_id
        $treatmentLogs = TreatmentLog::where('medical_record_id', $medical_record_id)
            ->orderBy('treatment_date', 'desc')
            ->get();

        // Kiểm tra nếu không tìm thấy bản ghi điều trị
        if ($treatmentLogs->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy bản ghi điều trị cho bệnh án này.',
            ], 404); // Lỗi 404 - Not Found
        }

        // Trả về danh sách điều trị
        return response()->json([
            'success' => true,
            'data' => $treatmentLogs,
            'message' => 'Danh sách điều trị được lấy thành công!',
        ], 200); // Mã 200 - OK
    }

    public function apiGetTreatmentLogById($id)
    {

        $treatmentLog = TreatmentLog::find($id);

        // Kiểm tra nếu không tìm thấy bản ghi điều trị
        if (!$treatmentLog) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy bản ghi điều trị này.',
            ], 404); // Lỗi 404 - Not Found
        }

        // Trả về thông tin chi tiết bản ghi điều trị
        return response()->json([
            'success' => true,
            'data' => $treatmentLog,
            'message' => 'Thông tin bản ghi điều trị được lấy thành công!',
        ], 200); // Mã 200 - OK
    }


    public function apiCreateTreatmentLog(Request $request, $medical_record_id)
    {
        $doctor = $request->user();

        if (!$doctor) {
            return response()->json([
                'success' => false,
                'message' => 'Chỉ bác sĩ mới có quyền thêm log điều trị!',
            ], 403);
        }

        // Kiểm tra bệnh án
        $medicalRecord = MedicalRecord::find($medical_record_id);
        if (!$medicalRecord) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy bệnh án này!',
            ], 404);
        }

        $validated = $request->validate([
            'description' => 'required|string|max:1000',
            'treatment_date' => 'required|date',
            'next_appointment_date' => 'nullable|date'
        ]);

        try {
            $treatmentLog = TreatmentLog::create([
                'medical_record_id' => $medical_record_id,
                'description' => $validated['description'],
                'treatment_date' => $validated['treatment_date'],
                'next_appointment_date' => $validated['next_appointment_date'] ?? null
            ]);

            return response()->json([
                'success' => true,
                'data' => $treatmentLog,
                'message' => 'Log điều trị đã được tạo thành công!',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể tạo log điều trị!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }



    public function apiDeleteTreatmentLog($id, Request $request)
    {
        $doctor = $request->user();

        if (!$doctor) {
            return response()->json([
                'success' => false,
                'message' => 'Chỉ bác sĩ mới có quyền xóa log điều trị!',
            ], 403);
        }

        $treatmentLog = TreatmentLog::find($id);

        if (!$treatmentLog) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy bản ghi điều trị này.',
            ], 404);
        }

        $medicalRecord = MedicalRecord::find($treatmentLog->medical_record_id);
        if (!$medicalRecord || $medicalRecord->doctor_id != $doctor->id) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền xóa log điều trị này!',
            ], 403);
        }

        try {
            $treatmentLog->delete();
            return response()->json([
                'success' => true,
                'message' => 'Log điều trị đã được xóa thành công!',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể xóa log điều trị!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

}
