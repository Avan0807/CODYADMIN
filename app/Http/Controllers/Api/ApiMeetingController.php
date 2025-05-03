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
    
    public function apiGetUserMeetings($userId)
    {
        // Xác thực người dùng thông qua Sanctum
        $user = Auth::user();
        
        // Kiểm tra nếu người dùng không tồn tại hoặc không trùng với userId trong route
        if (!$user || $user->id != $userId) {
            return response()->json(['error' => 'Không có quyền truy cập'], 403);
        }
        
        // Lấy thông tin người dùng từ cơ sở dữ liệu
        $user = \App\Models\User::find($userId);
        
        // Kiểm tra nếu người dùng không tồn tại
        if (!$user) {
            return response()->json(['error' => 'Không tìm thấy người dùng'], 404);
        }
        
        // Lấy tất cả cuộc họp của người dùng
        $meetings = $user->meetings;
        
        // Trả về danh sách cuộc họp của người dùng dưới dạng JSON
        return response()->json($meetings);
    }
    
    public function apiCreateMeeting(Request $request)
    {
        // Kiểm tra xác thực cho cả bác sĩ và người dùng thông thường
        $creator = null;
        $creatorType = null;
        
        if (Auth::guard('sanctum')->check()) {
            // Lấy thông tin người dùng đã xác thực
            $creator = Auth::guard('sanctum')->user();
            
            // Xác định loại người dùng đã xác thực
            if ($creator instanceof \App\Models\Doctor) {
                $creatorType = 'Doctor';
            } elseif ($creator instanceof \App\Models\User) {
                $creatorType = 'User';
            }
        }
        
        // Nếu không xác thực được dưới bất kỳ loại nào, trả về lỗi
        if (!$creator || !$creatorType) {
            return response()->json(['error' => 'Không có quyền truy cập. Bạn phải đăng nhập với tư cách bác sĩ hoặc người dùng.'], 403);
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
            'created_by_id' => $creator->id,
            'created_by_type' => $creatorType,
        ]);
        
        // Lưu cuộc họp vào cơ sở dữ liệu
        $meeting->save();
        
        // Trả về thông tin cuộc họp vừa được tạo
        return response()->json([
            'message' => 'Tạo cuộc họp thành công',
            'meeting' => $meeting,
            'created_by' => [
                'id' => $creator->id,
                'type' => $creatorType
            ]
        ], 201);
    }

}