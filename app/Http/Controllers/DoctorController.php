<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Doctor;

class DoctorController extends Controller
{
    public function index()
    {
        $doctors = Doctor::orderBy('id', 'DESC')->paginate(10); // Lấy danh sách với phân trang
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
        ]);

        Doctor::create($request->all());

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
        ]);

        $doctor->update($request->all());

        return redirect()->route('doctor.index')->with('success', 'Thông tin bác sĩ đã được cập nhật');
    }

    public function destroy(Doctor $doctor)
    {
        $doctor->delete();
        return redirect()->route('doctor.index')->with('success', 'Bác sĩ đã được xóa');
    }
}
