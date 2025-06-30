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
            $userId = Auth::id();

            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể xác thực người dùng.',
                ], 401);
            }

            // ✅ Kiểm tra bác sĩ có thuộc chuyên khoa được chọn không
            $doctor = Doctor::with('specializations')->findOrFail($request->doctor_id);
            $isValidSpecialization = $doctor->specializations->contains('id', $request->specialization_id);

            if (!$isValidSpecialization) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bác sĩ không thuộc chuyên khoa được chọn.',
                ], 422);
            }

            // Tạo lịch hẹn mới
            $appointment = Appointment::create([
                'doctor_id' => $request->doctor_id,
                'specialization_id' => $request->specialization_id,
                'user_id' => $userId,
                'date' => $request->date,
                'time' => $request->time,
                'status' => 'Chờ duyệt',
                'approval_status' => 'Chờ duyệt',
                'notes' => $request->notes,
                'consultation_type' => $request->consultation_type,
            ]);

            $user = Auth::user();

            // Gửi thông báo cho bác sĩ
            $doctor->notify(new StatusNotification([
                'title' => 'Yêu cầu lịch hẹn mới',
                'message' => "Bạn có một yêu cầu lịch hẹn mới từ bệnh nhân {$user->name} vào ngày {$appointment->date} lúc {$appointment->time}.",
                'appointment_id' => $appointment->id,
                'type' => 'appointment_request',
            ]));

            // Gửi thông báo cho bệnh nhân
            $user->notify(new StatusNotification([
                'title' => 'Lịch hẹn của bạn đang chờ duyệt',
                'message' => "Bạn đã đặt lịch hẹn với bác sĩ {$doctor->name} vào ngày {$appointment->date} lúc {$appointment->time}. Vui lòng chờ xác nhận.",
                'appointment_id' => $appointment->id,
                'type' => 'appointment_pending',
            ]));

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

    public function apiRescheduleAppointment(Request $request, $appointmentID)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date|after_or_equal:today',
            'time' => 'required|date_format:H:i',
            'consultation_type' => 'required|in:Online,Offline,At Home',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $user = Auth::user();
            $appointment = Appointment::findOrFail($appointmentID);

            // ✅ Chỉ người tạo lịch mới được chỉnh sửa
            if ($appointment->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn không có quyền sửa lịch hẹn này.',
                ], 403);
            }

            // ✅ Chỉ cho sửa nếu trạng thái là "Chờ duyệt"
            if ($appointment->status !== 'Chờ duyệt') {
                return response()->json([
                    'success' => false,
                    'message' => 'Chỉ được chỉnh sửa lịch hẹn ở trạng thái Chờ duyệt.',
                ], 400);
            }

            // ✅ Cập nhật các trường được phép
            $appointment->update([
                'date' => $request->date,
                'time' => $request->time,
                'consultation_type' => $request->consultation_type,
                'notes' => $request->notes,
            ]);

            // Gửi thông báo cho bác sĩ
            $doctor = Doctor::find($appointment->doctor_id);
            if ($doctor) {
                $doctor->notify(new StatusNotification([
                    'title' => 'Lịch hẹn đã được cập nhật',
                    'message' => "Bệnh nhân {$user->name} đã cập nhật lịch hẹn: {$appointment->date} lúc {$appointment->time}.",
                    'appointment_id' => $appointment->id,
                    'type' => 'appointment_rescheduled',
                ]));
            }

            return response()->json([
                'success' => true,
                'message' => 'Lịch hẹn đã được cập nhật thành công.',
                'appointment' => $appointment,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể cập nhật lịch hẹn.',
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

    public function apiGetAppointmentsByUser()
    {
        try {
            $user = Auth::user(); // lấy user đang đăng nhập

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Chưa đăng nhập.',
                ], 401);
            }

            // Eager load thông tin người dùng và bác sĩ
            $appointments = Appointment::with(['user', 'doctor'])
                ->where('user_id', $user->id)
                ->get();

            if ($appointments->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy cuộc hẹn nào cho tài khoản này.',
                ]);
            }

            // Thêm thông tin tên vào từng cuộc hẹn
            $appointments->transform(function ($appointment) {
                $appointment->user_name = $appointment->user->name; // Thêm tên người dùng
                $appointment->doctor_name = $appointment->doctor->name; // Thêm tên bác sĩ
                return $appointment;
            });

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

    public function apiCancelAppointment(Request $request, $appointmentID)
    {
        $currentUserId = Auth::id();

        if (!$currentUserId) {
            return response()->json([
                'message' => 'Không thể xác thực người dùng.',
            ], 401);
        }

        $appointment = Appointment::find($appointmentID);

        if (!$appointment || ($appointment->user_id !== $currentUserId && $appointment->doctor_id !== $currentUserId)) {
            return response()->json([
                'message' => 'Bạn không có quyền hủy lịch hẹn này hoặc lịch hẹn không tồn tại.',
            ], 403);
        }

        if (in_array($appointment->status, ['Đã hủy', 'Hoàn thành'])) {
            return response()->json([
                'message' => 'Lịch hẹn đã bị hủy hoặc hoàn thành, không thể hủy thêm.',
            ], 400);
        }

        // Chỉ cập nhật status, không đụng approval_status
        $appointment->update([
            'status' => 'Đã hủy',
        ]);

        // Gửi thông báo nếu cần...

        return response()->json([
            'message' => 'Lịch khám đã được hủy thành công.',
            'appointment' => $appointment
        ], 200);
    }



    // Xác nhận lịch hẹn
    public function apiConfirmAppointment($appointmentID, Request $request)
    {
        try {
            $doctorId = Auth::id();

            if (!$doctorId) {
                return response()->json([
                    'message' => 'Không thể xác thực người dùng.',
                ], 401);
            }

            // Lấy lịch hẹn
            $appointment = Appointment::findOrFail($appointmentID);

            // Kiểm tra quyền sở hữu
            if ($appointment->doctor_id != $doctorId) {
                return response()->json([
                    'message' => 'Bạn không có quyền xác nhận lịch hẹn này.',
                ], 403);
            }

            // Kiểm tra trạng thái lịch hẹn có thể xác nhận không
            if (!($appointment->status === 'Chờ duyệt' && $appointment->approval_status === 'Chờ duyệt')) {
                return response()->json([
                    'message' => 'Lịch hẹn hiện không ở trạng thái có thể xác nhận.',
                ], 400);
            }

            // Cập nhật trạng thái
            $appointment->update([
                'status' => 'Sắp tới',
                'approval_status' => 'Chấp nhận'
            ]);

            // Gửi thông báo cho bệnh nhân
            if ($user = User::find($appointment->user_id)) {
                $user->notify(new StatusNotification([
                    'title' => 'Lịch hẹn đã được xác nhận',
                    'message' => "Bác sĩ đã xác nhận lịch hẹn của bạn vào ngày {$appointment->date} lúc {$appointment->time}.",
                    'appointment_id' => $appointment->id,
                    'type' => 'appointment_confirmed'
                ]));
            }

            // Gửi thông báo cho bác sĩ
            $doctor = Auth::user(); // đã login
            $doctor->notify(new StatusNotification([
                'title' => 'Xác nhận lịch hẹn thành công',
                'message' => "Bạn đã xác nhận lịch hẹn với bệnh nhân vào ngày {$appointment->date} lúc {$appointment->time}.",
                'appointment_id' => $appointment->id,
                'type' => 'appointment_confirmed'
            ]));

            return response()->json([
                'message' => 'Lịch hẹn đã được xác nhận thành công.',
                'appointment' => $appointment
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Lỗi khi xác nhận lịch hẹn.',
                'error' => $e->getMessage()
            ], 500);
        }
    }



    // Hoàn thành lịch hẹn
    public function apiCompleteAppointment($appointmentID, Request $request)
    {
        try {
            // Xác thực bác sĩ đang đăng nhập
            $doctorId = Auth::id();

            if (!$doctorId) {
                return response()->json([
                    'message' => 'Không thể xác thực người dùng.',
                ], 401);
            }

            // Lấy thông tin lịch hẹn
            $appointment = Appointment::findOrFail($appointmentID);

            // Kiểm tra quyền sở hữu lịch hẹn
            if ($appointment->doctor_id != $doctorId) {
                return response()->json([
                    'message' => 'Bạn không có quyền hoàn thành lịch hẹn này.',
                ], 403);
            }

            // Lịch hẹn phải ở trạng thái "Sắp tới" mới được hoàn thành
            if ($appointment->status !== 'Sắp tới') {
                return response()->json([
                    'message' => 'Lịch hẹn không ở trạng thái sắp tới.',
                ], 400);
            }

            // Cập nhật trạng thái sang "Hoàn thành"
            $appointment->update(['status' => 'Hoàn thành']);

            // Gửi thông báo cho bệnh nhân
            if ($user = User::find($appointment->user_id)) {
                $user->notify(new StatusNotification([
                    'title' => 'Lịch hẹn đã hoàn thành',
                    'message' => "Lịch khám với bác sĩ vào ngày {$appointment->date} lúc {$appointment->time} đã hoàn thành.",
                    'appointment_id' => $appointment->id,
                    'type' => 'appointment_completed'
                ]));
            }

            // Gửi thông báo cho bác sĩ
            $doctor = Auth::user();
            $doctor->notify(new StatusNotification([
                'title' => 'Bạn đã hoàn thành lịch hẹn',
                'message' => "Lịch khám với bệnh nhân vào ngày {$appointment->date} lúc {$appointment->time} đã hoàn thành.",
                'appointment_id' => $appointment->id,
                'type' => 'appointment_completed'
            ]));

            return response()->json([
                'message' => 'Lịch hẹn đã được hoàn thành.',
                'appointment' => $appointment
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Lỗi khi hoàn thành lịch hẹn.',
                'error' => $e->getMessage()
            ], 500);
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

        // Lấy danh sách lịch hẹn của bác sĩ kèm thông tin user
        $appointments = Appointment::where('doctor_id', $doctor_id)
            ->with('user:id,name') // Chỉ lấy id và name của user
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
            $doctorID = Auth::id();

            if (!$doctorID) {
                return response()->json(['message' => 'Không thể xác thực người dùng.'], 401);
            }

            // Tìm lịch hẹn thuộc bác sĩ hiện tại
            $appointment = Appointment::where('id', $appointmentID)
                ->where('doctor_id', $doctorID)
                ->first();

            if (!$appointment) {
                return response()->json([
                    'message' => 'Lịch hẹn không tồn tại hoặc bạn không có quyền xóa lịch hẹn này.'
                ], 403);
            }

            // Gửi thông báo cho bệnh nhân nếu cần
            $user = User::find($appointment->user_id);
            if ($user) {
                $user->notify(new StatusNotification([
                    'title' => 'Lịch hẹn đã bị từ chối',
                    'message' => "Lịch khám vào ngày {$appointment->date} lúc {$appointment->time} đã bị từ chối bởi bác sĩ.",
                    'appointment_id' => $appointment->id,
                    'type' => 'appointment_rejected',
                ]));
            }

            // Xóa lịch hẹn
            $appointment->delete();

            return response()->json([
                'message' => 'Lịch hẹn đã được xóa thành công.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Lỗi khi xóa lịch hẹn.',
                'error' => $e->getMessage()
            ], 500);
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
            ], 404);
        }

        // Nếu là bệnh nhân chính chủ
        if ($user->id === $appointment->user_id) {
            return response()->json([
                'success' => true,
                'data' => $appointment,
                'message' => 'Thông tin lịch khám đã được lấy thành công!',
            ], 200);
        }

        // Nếu là bác sĩ liên quan
        $isDoctor = Doctor::where('id', $user->id)->exists(); // giả định doctor.id = users.id

        if ($isDoctor && $appointment->doctor_id === $user->id) {
            return response()->json([
                'success' => true,
                'data' => $appointment,
                'message' => 'Thông tin lịch khám đã được lấy thành công!',
            ], 200);
        }

        // Không phải bệnh nhân, cũng không phải bác sĩ liên quan
        return response()->json([
            'success' => false,
            'message' => 'Bạn không có quyền truy cập thông tin lịch khám này!',
        ], 403);
    }


}
