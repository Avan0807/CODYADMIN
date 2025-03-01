<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use App\Notifications\StatusNotification;
use Illuminate\Support\Facades\Notification;

class ApiAppointmentController extends Controller
{
    // =================== ĐẶT LỊCH HẸN ===================
    public function createAppointment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'doctor_id' => 'required|exists:doctors,id',
            'user_id' => 'required|exists:users,id',
            'date' => 'required|date|after_or_equal:today',
            'time' => 'required|date_format:H:i:s',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $appointment = Appointment::create([
                'doctor_id' => $request->doctor_id,
                'user_id' => $request->user_id,
                'date' => $request->date,
                'time' => $request->time,
                'status' => 'Chờ duyệt',
                'approval_status' => 'Chờ duyệt',
                'notes' => $request->notes,
            ]);
//dictir1
            /* ✅ Lấy danh sách admin
            $admins = User::where('role', 'admin')->get();

            if ($admins->count() > 0) {
                $details = [
                    'title' => 'Yêu cầu đặt lịch khám mới!',
                    'actionURL' => route('admin.appointments', $appointment->id),
                    'fas' => 'fa-calendar-check',
                    'message' => 'Người dùng ID: ' . $request->user_id . ' đã đặt lịch khám vào ngày ' . $request->date . ' lúc ' . $request->time,
                ];

                // ✅ Gửi thông báo đến tất cả Admin
                Notification::send($admins, new StatusNotification($details));
            }*/

            return response()->json([
                'success' => true,
                'message' => 'Lịch hẹn đã được tạo thành công!',
                'appointment' => $appointment,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể đặt lịch hẹn.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // =================== LẤY DANH SÁCH TẤT CẢ LỊCH HẸN ===================
    public function getAllAppointments()
    {
        try {
            $appointments = Appointment::with(['doctor', 'user'])->get();

            return response()->json([
                'success' => true,
                'appointments' => $appointments,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể lấy danh sách lịch hẹn.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // =================== LẤY LỊCH HẸN THEO USER ===================
    public function getAppointmentsByUser($user_id)
    {
        try {
            $appointments = Appointment::where('user_id', $user_id)->with('doctor')->get();

            return response()->json([
                'success' => true,
                'appointments' => $appointments,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể lấy danh sách lịch hẹn.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // =================== LẤY 5 LỊCH HẸN GẦN NHẤT ===================
    public function getRecentAppointments($user_id)
    {
        try {
            $appointments = Appointment::where('user_id', $user_id)
                ->whereDate('date', '>=', now()->toDateString())
                ->orderBy('date', 'asc')
                ->orderBy('time', 'asc')
                ->limit(5)
                ->get();

            return response()->json([
                'success' => true,
                'appointments' => $appointments,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể lấy lịch hẹn gần nhất.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // =================== HỦY LỊCH HẸN ===================
    public function cancelAppointment($id)
    {
        try {
            $appointment = Appointment::findOrFail($id);
            $appointment->update(['status' => 'cancelled']);

            return response()->json([
                'success' => true,
                'message' => 'Lịch hẹn đã được hủy.',
                'appointment' => $appointment,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể hủy lịch hẹn.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // =================== XÁC NHẬN LỊCH HẸN ===================
    public function confirmAppointment($id)
    {
        try {
            $appointment = Appointment::findOrFail($id);

            if ($appointment->approval_status !== 'Chờ duyệt') {
                return response()->json(['message' => 'Lịch hẹn đã được duyệt hoặc bị từ chối.'], 400);
            }

            $appointment->update([
                'status' => 'confirmed',
                'approval_status' => 'approved',
            ]);

            return response()->json([
                'message' => 'Lịch hẹn đã được xác nhận.',
                'appointment' => $appointment,
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Lỗi khi xác nhận lịch hẹn.', 'error' => $e->getMessage()], 500);
        }
    }

    // =================== HOÀN THÀNH LỊCH HẸN ===================
    public function completeAppointment($id)
    {
        try {
            $appointment = Appointment::findOrFail($id);

            if ($appointment->status !== 'confirmed') {
                return response()->json(['message' => 'Lịch hẹn chưa được xác nhận.'], 400);
            }

            $appointment->update(['status' => 'completed']);

            return response()->json([
                'message' => 'Lịch hẹn đã hoàn thành.',
                'appointment' => $appointment,
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Lỗi khi hoàn thành lịch hẹn.', 'error' => $e->getMessage()], 500);
        }
    }

    // =================== XÓA LỊCH HẸN ===================
    public function deleteAppointment($id)
    {
        try {
            $appointment = Appointment::findOrFail($id);
            $appointment->delete();

            return response()->json(['message' => 'Lịch hẹn đã được xóa.'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Lỗi khi xóa lịch hẹn.', 'error' => $e->getMessage()], 500);
        }
    }
}
