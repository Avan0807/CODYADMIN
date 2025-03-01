<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Appointment;
use Illuminate\Support\Facades\Auth;
use App\Models\Doctor;

class AppointmentController extends Controller
{
    public function index()
    {
        $appointments = Appointment::where('id', Auth::id())->get();
        return view('doctor.appointment.index', compact('appointments'));
    }

    
    /**
     * Xử lý đặt lịch hẹn và lưu vào bảng appointments
     */
    public function store(Request $request)
    {
        // Kiểm tra nếu bác sĩ tồn tại
        $doctor = Doctor::where('id', $request->doctorID)->first();
        if (!$doctor) {
            return redirect()->back()->with('error', 'Bác sĩ không tồn tại.');
        }
        
        try {
            $request->validate([
                'id' => 'required|exists:doctors,id',
                'date' => 'required|date|after_or_equal:today',
                'time' => 'required',
                'consultation_type' => 'required|in:Online,Offline,Home',
                'note' => 'nullable|string|max:255',
            ]);
            
            
    
            \Log::info('Dữ liệu nhận được:', $request->all());

            $appointment = Appointment::create([
                'userID' => Auth::id(),
                'id' => $request->doctorID,
                'date' => $request->date,
                'time' => $request->time,
                'consultation_type' => $request->consultation_type,
                'note' => $request->note,
                'status' => 'Chờ duyệt',
                'approval_status' => 'Chờ duyệt',
                'workflow_stage' => 'initial_review',
            ]);

            \Log::info('Đặt lịch thành công:', $appointment->toArray());
            return redirect()->route('home')->with('success', 'Đặt lịch thành công!');
        } catch (\Exception $e) {
            \Log::error('Lỗi đặt lịch: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Đã xảy ra lỗi trong quá trình đặt lịch.');
        }
    }
    
    
    public function create($id)
    {
        $doctor = Doctor::where('id', $id)->firstOrFail();
        return view('frontend.pages.appointment_form', compact('doctor'));
    }
    
}
