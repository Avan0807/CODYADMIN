<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Doctor;
use App\Models\Meeting;
use Illuminate\Support\Facades\Auth;

class ApiMeetingController extends Controller
{
    public function apiGetAllMeeting()
    {
        $doctors = Doctor::with('meetings')->get();  // Lấy tất cả bác sĩ cùng các cuộc họp của họ

        return response()->json($doctors);
    }
    

    public function apiGetDoctorMeetings($doctorId)
    {
        // Xác thực người dùng bác sĩ thông qua Sanctum
        $doctor = Auth::user(); // Sử dụng Auth::user() để lấy thông tin người dùng đã xác thực qua Sanctum
    
        \Log::info('Doctor: ', [$doctor]);
    
        // Kiểm tra nếu bác sĩ không đúng hoặc không trùng với doctorId trong route
        if (!$doctor || $doctor->id != $doctorId) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
    
        // Lấy thông tin bác sĩ từ cơ sở dữ liệu
        $doctor = Doctor::find($doctorId);
    
        // Kiểm tra nếu bác sĩ không tồn tại
        if (!$doctor) {
            return response()->json(['error' => 'Doctor not found'], 404);
        }
    
        // Lấy tất cả các cuộc họp (meetings) của bác sĩ
        $meetings = $doctor->meetings;
    
        \Log::info('Meetings: ', [$meetings]);
    
        // Trả về danh sách cuộc họp của bác sĩ dưới dạng JSON
        return response()->json($meetings);
    }
    
    public function apiCreateMeeting(Request $request)
    {
        // Xác thực bác sĩ đang đăng nhập với guard 'doctor'
        $doctor = Auth::user();

        // Kiểm tra xem người dùng có phải là bác sĩ không
        if (!$doctor) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Xác thực dữ liệu đầu vào
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'meet_link' => 'required|string',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
        ]);

        // Tạo cuộc họp mới
        $meeting = new Meeting([
            'title' => $request->title,
            'description' => $request->description,
            'meet_link' => $request->meet_link,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'created_by_id' => $doctor->id,
            'created_by_type' => 'Doctor',
        ]);

        // Lưu cuộc họp vào cơ sở dữ liệu
        $meeting->save();

        // Trả về thông tin cuộc họp vừa được tạo
        return response()->json([
            'message' => 'Meeting created successfully',
            'meeting' => $meeting,
        ], 201);
    }

}