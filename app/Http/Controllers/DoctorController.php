<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Doctor;
use Illuminate\Support\Facades\Storage;

class DoctorController extends Controller
{
    public function index()
    {
        $doctors = Doctor::orderBy('id', 'DESC')->get(); // Lấy danh sách với phân trang
        return view('backend.doctor.index', compact('doctors'));
    }

    public function create()
    {
        return view('backend.doctor.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'specialization' => 'required',
            'experience' => 'required|integer',
            'email' => 'required|email|unique:doctors',
            'phone' => 'required',
            'status' => 'required',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'password' => 'required|min:6',
        ]);

        $data = $request->only([
            'name', 'specialization', 'services', 'experience',
            'working_hours', 'location', 'workplace', 'phone',
            'email', 'status', 'rating', 'consultation_fee',
            'bio', 'points'
        ]);

        // Xử lý mật khẩu
        $data['password'] = bcrypt($request->password);

        // Xử lý ảnh nếu có
        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('photos', 'public');
        }

        Doctor::create($data);

        return redirect()->route('doctor.index')->with('success', 'Bác sĩ đã được thêm thành công');
    }

    public function edit(Doctor $doctor)
    {
        return view('backend.doctor.edit', compact('doctor'));
    }

    public function update(Request $request, Doctor $doctor)
    {
        $request->validate([
            'name' => 'required',
            'specialization' => 'required',
            'experience' => 'required|integer',
            'email' => 'required|email|unique:doctors,email,' . $doctor->id,
            'phone' => 'required',
            'status' => 'required',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'password' => 'nullable|min:6',
        ]);

        $data = $request->only([
            'name', 'specialization', 'services', 'experience',
            'working_hours', 'location', 'workplace', 'phone',
            'email', 'status', 'rating', 'consultation_fee',
            'bio', 'points'
        ]);

        // Xử lý mật khẩu nếu có cập nhật
        if ($request->filled('password')) {
            $data['password'] = bcrypt($request->password);
        }

        // Xử lý ảnh nếu có
        if ($request->hasFile('photo')) {
            // Xóa ảnh cũ nếu tồn tại
            if ($doctor->photo && Storage::exists('public/' . $doctor->photo)) {
                Storage::delete('public/' . $doctor->photo);
            }
            // Lưu ảnh mới
            $data['photo'] = $request->file('photo')->store('photos', 'public');
        }

        $doctor->update($data);

        return redirect()->route('doctor.index')->with('success', 'Thông tin bác sĩ đã được cập nhật');
    }

    public function destroy(Doctor $doctor)
    {
        // Xóa ảnh khi xóa bác sĩ
        if ($doctor->photo && Storage::exists('public/' . $doctor->photo)) {
            Storage::delete('public/' . $doctor->photo);
        }

        $doctor->delete();

        return redirect()->route('doctor.index')->with('success', 'Bác sĩ đã được xóa');
    }

}
