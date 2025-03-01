<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\User;
use App\Models\Doctor;
use App\Notifications\StatusNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class AppointmentsController extends Controller
{

    public function apiCreateAppointment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'doctor_id' => 'required|exists:doctors,id',
            'user_id' => 'required|exists:users,id',
            'date' => 'required|date|after_or_equal:today',
            'time' => 'required|date_format:H:i',
            'consultation_type' => 'required|in:Online,Offline,At Home',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // Tạo lịch hẹn mới
            $appointment = Appointment::create([
                'doctor_id' => $request->doctor_id,
                'user_id' => $request->user_id,
                'date' => $request->date,
                'time' => $request->time,
                'status' => 'Chờ duyệt',
                'approval_status' => 'Chờ duyệt',
                'notes' => $request->notes,
                'consultation_type' => $request->consultation_type,
            ]);

            // Lấy thông tin bệnh nhân
            $user = User::find($request->user_id);
            // Lấy thông tin bác sĩ
            $doctor = Doctor::find($request->doctor_id);

            // Gửi thông báo cho bác sĩ
            if ($doctor) {
                $doctor->notify(new StatusNotification([
                    'title' => 'Yêu cầu lịch hẹn mới',
                    'message' => "Bạn có một yêu cầu lịch hẹn mới từ bệnh nhân {$user->name} vào ngày {$appointment->date} lúc {$appointment->time}.",
                    'appointment_id' => $appointment->id,
                    'type' => 'appointment_request'
                ]));
            }

            // Gửi thông báo cho bệnh nhân
            if ($user) {
                $user->notify(new StatusNotification([
                    'title' => 'Lịch hẹn của bạn đang chờ duyệt',
                    'message' => "Bạn đã đặt lịch hẹn với bác sĩ {$doctor->name} vào ngày {$appointment->date} lúc {$appointment->time}. Vui lòng chờ xác nhận.",
                    'appointment_id' => $appointment->id,
                    'type' => 'appointment_pending'
                ]));
            }

            return response()->json([
                'success' => true,
                'message' => 'Yêu cầu đặt lịch khám đã được gửi và thông báo đã được gửi.',
                'appointment' => $appointment,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể gửi yêu cầu đặt lịch khám.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Lấy danh sách tất cả các cuộc hẹn.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiGetAllAppointments()
    {
        try {
            // Lấy danh sách tất cả các cuộc hẹn
            $appointments = Appointment::all();

            return response()->json([
                'success' => true,
                'appointments' => $appointments,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể lấy danh sách cuộc hẹn.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function apiGetAppointmentsByUser($userID)
    {
        try {
            $appointments = Appointment::where('user_id', $userID)->get();

            if ($appointments->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy cuộc hẹn nào cho userID này.',
                ]);
            }

            return response()->json([
                'success' => true,
                'appointments' => $appointments,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể lấy danh sách cuộc hẹn.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function apiGetCurrentAppointments($userID)
    {
        try {
            // Lấy 5 lịch khám gần nhất, sắp xếp theo ngày và giờ
            $appointments = Appointment::where('user_id', $userID)
                ->whereDate('date', '<=', now()->format('Y-m-d')) // Lọc lịch khám từ hôm nay
                ->orderBy('date', 'asc') // Sắp xếp theo ngày tăng dần
                ->orderBy('time', 'asc') // Sắp xếp theo giờ tăng dần nếu cùng ngày
                ->limit(5) // Lấy tối đa 5 lịch khám
                ->get();

            if ($appointments->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy lịch khám sắp tới nào cho user này.',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Lấy danh sách lịch khám sắp tới thành công.',
                'appointments' => $appointments,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể lấy danh sách lịch khám sắp tới.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function apiCancelAppointment(Request $request, $userID, $appointmentID)
    {
        try {
            $appointment = Appointment::where('user_id', $userID)
                ->where('id', $appointmentID)
                ->first();

            if (!$appointment) {
                return response()->json(['message' => 'Không tìm thấy lịch khám.'], 404);
            }

            $appointment->status = 'Đã hủy';
            $appointment->save();

            // Gửi thông báo cho bệnh nhân
            $user = User::find($appointment->user_id);
            if ($user) {
                $user->notify(new StatusNotification([
                    'title' => 'Lịch hẹn đã bị hủy',
                    'message' => "Bạn đã hủy lịch hẹn vào ngày {$appointment->date} lúc {$appointment->time}.",
                    'appointment_id' => $appointment->id,
                    'type' => 'appointment_cancelled'
                ]));
            }

            // Gửi thông báo cho bác sĩ
            $doctor = Doctor::find($appointment->doctor_id);
            if ($doctor) {
                $doctor->notify(new StatusNotification([
                    'title' => 'Lịch hẹn đã bị hủy',
                    'message' => "Bệnh nhân đã hủy lịch hẹn vào ngày {$appointment->date} lúc {$appointment->time}.",
                    'appointment_id' => $appointment->id,
                    'type' => 'appointment_cancelled'
                ]));
            }

            return response()->json([
                'message' => 'Lịch khám đã được hủy thành công.',
                'appointment' => $appointment
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Không thể hủy lịch khám.', 'error' => $e->getMessage()], 500);
        }
    }



    // Xác nhận lịch hẹn


    public function apiConfirmAppointment($appointmentID, Request $request)
    {
        try {
            $appointment = Appointment::where('id', $appointmentID)->firstOrFail();

            if ($appointment->status !== 'Chờ duyệt' || $appointment->approval_status !== 'Chờ duyệt') {
                return response()->json(['message' => 'Lịch hẹn không ở trạng thái chờ duyệt.'], 400);
            }

            $appointment->update([
                'status' => 'Sắp tới',
                'approval_status' => 'Chấp nhận'
            ]);

            // ✅ Gửi thông báo cho bệnh nhân
            $user = User::find($appointment->user_id);
            if ($user) {
                $user->notify(new StatusNotification([
                    'title' => 'Lịch hẹn đã được xác nhận',
                    'message' => "Bác sĩ đã xác nhận lịch hẹn của bạn vào {$appointment->date} lúc {$appointment->time}.",
                    'appointment_id' => $appointment->id,
                    'type' => 'appointment_confirmed'
                ]));
            }

            // ✅ Gửi thông báo cho bác sĩ
            $doctor = Doctor::find($appointment->doctor_id);
            if ($doctor) {
                $doctor->notify(new StatusNotification([
                    'title' => 'Bạn đã xác nhận lịch hẹn',
                    'message' => "Bạn đã xác nhận lịch hẹn vào {$appointment->date} lúc {$appointment->time}.",
                    'appointment_id' => $appointment->id,
                    'type' => 'appointment_confirmed'
                ]));
            }

            return response()->json([
                'message' => 'Lịch hẹn đã được xác nhận thành công.',
                'appointment' => $appointment
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Lỗi khi xác nhận lịch hẹn.', 'error' => $e->getMessage()], 500);
        }
    }


    // Hoàn thành lịch hẹn
    public function apiCompleteAppointment($appointmentID, Request $request)
    {
        try {
            $appointment = Appointment::where('id', $appointmentID)->firstOrFail();

            if ($appointment->status !== 'Sắp tới') {
                return response()->json(['message' => 'Lịch hẹn không ở trạng thái sắp tới.'], 400);
            }

            $appointment->update(['status' => 'Hoàn thành']);

            // ✅ Gửi thông báo cho bệnh nhân
            $user = User::find($appointment->user_id);
            if ($user) {
                $user->notify(new StatusNotification([
                    'title' => 'Lịch hẹn đã hoàn thành',
                    'message' => "Lịch khám với bác sĩ vào ngày {$appointment->date} lúc {$appointment->time} đã hoàn thành.",
                    'appointment_id' => $appointment->id,
                    'type' => 'appointment_completed'
                ]));
            }

            // ✅ Gửi thông báo cho bác sĩ
            $doctor = Doctor::find($appointment->doctor_id);
            if ($doctor) {
                $doctor->notify(new StatusNotification([
                    'title' => 'Bạn đã hoàn thành lịch hẹn',
                    'message' => "Lịch khám với bệnh nhân vào ngày {$appointment->date} lúc {$appointment->time} đã hoàn thành.",
                    'appointment_id' => $appointment->id,
                    'type' => 'appointment_completed'
                ]));
            }

            return response()->json([
                'message' => 'Lịch hẹn đã được hoàn thành.',
                'appointment' => $appointment
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Lỗi khi hoàn thành lịch hẹn.', 'error' => $e->getMessage()], 500);
        }
    }


    // Lấy 5 lịch hẹn gần nhất của bác sĩ
    public function apiGetRecentAppointments(Request $request)
    {
        try {
            // Lấy ID của bác sĩ từ token
            $doctorID = Auth::id();

            // Kiểm tra xem bác sĩ có tồn tại không
            $doctor = \DB::table('doctors')->where('id', $doctorID)->first();

            if (!$doctor) {
                return response()->json(['error' => 'Không tìm thấy bác sĩ.'], 404);
            }

            // Lấy 5 lịch hẹn gần nhất của bác sĩ này
            $appointments = \DB::table('appointments')
                ->where('doctor_id', $doctorID)
                ->orderBy('date', 'asc')
                ->orderBy('time', 'asc')
                ->limit(5)
                ->get();

            return response()->json([
                'message' => 'Lấy 5 lịch hẹn gần nhất thành công.',
                'appointments' => $appointments
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Lỗi khi lấy lịch hẹn.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    // Lấy toàn bộ lịch hẹn của bác sĩ


    public function apiGetAllAppointmentsByDoctor(Request $request, $doctor_id)
    {
        // Lấy thông tin bác sĩ đang đăng nhập
        $doctor = auth()->user();

        // Kiểm tra nếu chưa đăng nhập hoặc không phải bác sĩ có ID khớp với $doctor_id
        if (!$doctor || $doctor->id != $doctor_id) {
            return response()->json([
                'error' => 'Bạn không có quyền truy cập lịch hẹn này!'
            ], 403);
        }

        // Lấy danh sách lịch hẹn của bác sĩ
        $appointments = Appointment::where('doctor_id', $doctor_id)
            ->orderBy('date', 'asc')
            ->orderBy('time', 'asc')
            ->get();

        return response()->json([
            'message' => 'Danh sách lịch hẹn của bác sĩ',
            'appointments' => $appointments
        ]);
    }



    // Xóa lịch hẹn


    public function apiDeleteAppointment($appointmentID)
    {
        try {
            // Lấy ID của bác sĩ từ token
            $doctorID = Auth::id();

            // Kiểm tra xem bác sĩ có tồn tại không
            $doctor = DB::table('doctors')->where('id', $doctorID)->first();

            if (!$doctor) {
                return response()->json(['error' => 'Không tìm thấy bác sĩ.'], 404);
            }

            // Kiểm tra xem lịch hẹn có tồn tại và thuộc về bác sĩ đang đăng nhập không
            $appointment = DB::table('appointments')
                ->where('id', $appointmentID)
                ->where('doctor_id', $doctorID) // Chỉ cho phép bác sĩ xóa lịch hẹn của chính họ
                ->first();

            if (!$appointment) {
                return response()->json(['message' => 'Lịch hẹn không tồn tại hoặc bạn không có quyền xóa lịch hẹn này.'], 403);
            }

            // Xóa lịch hẹn
            DB::table('appointments')->where('id', $appointmentID)->delete();

            return response()->json(['message' => 'Lịch hẹn đã được xóa thành công.'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Lỗi khi xóa lịch hẹn.', 'error' => $e->getMessage()], 500);
        }
    }

    // index ra danh sách bệnh nhân
    public function getAllPatientsForDoctor()
    {
        try {
            // Lấy ID của bác sĩ đang đăng nhập
            $doctorID = Auth::id();

            // Kiểm tra xem bác sĩ có tồn tại không
            $doctor = DB::table('doctors')->where('id', $doctorID)->first();

            if (!$doctor) {
                return response()->json(['error' => 'Không tìm thấy bác sĩ.'], 404);
            }

            // Lấy danh sách bệnh nhân từng có lịch hẹn với bác sĩ (loại bỏ trùng lặp)
            $patients = User::whereIn('id', function ($query) use ($doctorID) {
                    $query->select('user_id')
                          ->from('appointments')
                          ->where('doctor_id', $doctorID);
                })
                ->select('id', 'name', 'email', 'phone')
                ->distinct()
                ->paginate(10);

            return response()->json([
                'message' => 'Lấy danh sách bệnh nhân thành công.',
                'patients' => $patients
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Lỗi khi lấy danh sách bệnh nhân.', 'message' => $e->getMessage()], 500);
        }
    }




    public function apiUpdateStatus(Request $request, $id)
    {
        try {
            $appointment = Appointment::findOrFail($id);

            // Chỉ cập nhật approval_status
            $appointment->update([
                'approval_status' => $request->approval_status
            ]);

            return redirect()->back()->with('success', 'Trạng thái phê duyệt đã được cập nhật!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Có lỗi xảy ra khi cập nhật trạng thái.');
        }
    }

    public function apiGetAppointmentInfo($appointmentID, Request $request)
    {

        $user = $request->user();

        $appointment = Appointment::find($appointmentID);
        if (!$appointment) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy lịch khám này.',
            ], 404); // Lỗi 404 - Not Found
        }

        // . Kiểm tra quyền truy cập:
        // - Nếu là bác sĩ: kiểm tra xem bác sĩ này có trong lịch khám không (so sánh doctor_id)
        // - Nếu là bệnh nhân: kiểm tra xem bệnh nhân này có trong lịch khám không (so sánh user_id)
        if ($appointment->doctor_id) {
            // Kiểm tra xem người dùng hiện tại có phải là bác sĩ hay không
            $doctor = Doctor::where('id', $user->id)->first();  // Lấy thông tin bác sĩ từ bảng Doctors

            if ($doctor) {
                // Bác sĩ chỉ có thể xem lịch khám của bệnh nhân nếu doctor_id trong bảng appointments trùng với doctor_id của bác sĩ
                if ($appointment->doctor_id !== $doctor->doctorID) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Bạn không có quyền truy cập thông tin lịch khám này!',
                    ], 403);
                }
            }
        }
        if ($user->id === $appointment->user_id) {
            // Bệnh nhân chỉ có thể xem lịch khám của chính mình
            return response()->json([
                'success' => true,
                'data' => $appointment,
                'message' => 'Thông tin lịch khám đã được lấy thành công!',
            ], 200); // Mã 200 - OK
        }

        return response()->json([
            'success' => false,
            'message' => 'Bạn không có quyền truy cập thông tin lịch khám này!',
        ], 403); // Lỗi 403 - Forbidden
    }
}
