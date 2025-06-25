<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Doctor;
use App\Models\Category;
use Illuminate\Support\Facades\Storage;

class DoctorController extends Controller
{
    /**
     * Danh sách bác sĩ
     */
    public function index()
    {
        $doctors = Doctor::orderBy('id', 'DESC')->get();
        return view('backend.doctor.index', compact('doctors'));
    }

    /**
     * Form thêm bác sĩ
     */
    public function create()
    {
        $categories = Category::where('status', 'active')->get();
        return view('backend.doctor.create', compact('categories'));
    }

    /**
     * Lưu bác sĩ mới
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'               => 'required|string|max:255',
            'services'           => 'nullable|string',
            'experience'         => 'required|integer|min:0',
            'working_hours'      => 'nullable|string',
            'location'           => 'nullable|string',
            'workplace'          => 'nullable|string',
            'phone'              => 'required|string|max:20',
            'email'              => 'required|email|unique:doctors',
            'photo'              => 'nullable|string',
            'status'             => 'required|in:active,inactive',
            'rating'             => 'nullable|numeric|min:0|max:5',
            'consultation_fee'   => 'nullable|numeric|min:0',
            'bio'                => 'nullable|string',
            'short_bio'          => 'nullable|string|max:255',
            'points'             => 'nullable|integer',
            'total_commission'   => 'nullable|numeric',
            'password'           => 'required|min:6',
            'specialization'     => 'required|exists:categories,id',
        ]);

        $data = $request->only([
            'name', 'services', 'experience', 'working_hours',
            'location', 'workplace', 'phone', 'email', 'photo', 'status',
            'rating', 'consultation_fee', 'bio', 'points',
            'short_bio', 'total_commission'
        ]);

        $data['password'] = bcrypt($request->password);

        $doctor = Doctor::create($data);
        $doctor->specializations()->sync([$request->specialization]);

        return redirect()->route('doctor.index')->with('success', 'Bác sĩ đã được thêm thành công');
    }

    /**
     * Form chỉnh sửa bác sĩ
     */
    public function edit(Doctor $doctor)
    {
        $categories = Category::where('status', 'active')->get();
        return view('backend.doctor.edit', compact('doctor', 'categories'));
    }

    /**
     * Cập nhật bác sĩ
     */
    public function update(Request $request, Doctor $doctor)
    {
        $request->validate([
            'name'               => 'required|string|max:255',
            'services'           => 'nullable|string',
            'experience'         => 'required|integer|min:0',
            'working_hours'      => 'nullable|string',
            'location'           => 'nullable|string',
            'workplace'          => 'nullable|string',
            'phone'              => 'required|string|max:20',
            'email'              => 'required|email|unique:doctors,email,' . $doctor->id,
            'photo'              => 'nullable|string',
            'status'             => 'required|in:active,inactive',
            'rating'             => 'nullable|numeric|min:0|max:5',
            'consultation_fee'   => 'nullable|numeric|min:0',
            'bio'                => 'nullable|string',
            'short_bio'          => 'nullable|string|max:255',
            'points'             => 'nullable|integer',
            'total_commission'   => 'nullable|numeric',
            'password'           => 'nullable|min:6',
            'specialization'     => 'required|exists:categories,id',
        ]);

        $data = $request->only([
            'name', 'services', 'experience', 'working_hours',
            'location', 'workplace', 'phone', 'email', 'photo', 'status',
            'rating', 'consultation_fee', 'bio', 'points',
            'short_bio', 'total_commission'
        ]);

        if ($request->filled('password')) {
            $data['password'] = bcrypt($request->password);
        }

        $doctor->update($data);
        $doctor->specializations()->sync([$request->specialization]);

        return redirect()->route('doctor.index')->with('success', 'Cập nhật bác sĩ thành công');
    }

    /**
     * Xóa bác sĩ
     */
    public function destroy(Doctor $doctor)
    {
        if ($doctor->photo && Storage::exists('public/' . $doctor->photo)) {
            Storage::delete('public/' . $doctor->photo);
        }

        $doctor->delete();

        return redirect()->route('doctor.index')->with('success', 'Đã xóa bác sĩ thành công');
    }
}
