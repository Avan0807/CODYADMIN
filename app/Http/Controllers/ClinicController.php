<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Clinic;

class ClinicController extends Controller
{
    /**
     * Hiển thị danh sách phòng khám.
     */
    public function index()
    {
        $clinics = Clinic::latest()->get();
        return view('backend.clinics.index', compact('clinics'));
    }

    /**
     * Hiển thị form tạo mới phòng khám.
     */
    public function create()
    {
        return view('backend.clinics.create');
    }

    /**
     * Lưu phòng khám mới vào cơ sở dữ liệu.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
        ]);
    
        // Kiểm tra xem email đã tồn tại chưa
        $clinic = Clinic::where('email', $request->email)->first();
        
        if ($clinic) {
            // Nếu tồn tại, cập nhật thông tin
            $clinic->update($request->all());
            return redirect()->route('clinics.index')->with('success', 'Thông tin phòng khám đã được cập nhật.');
        } else {
            // Nếu không tồn tại, tạo mới
            Clinic::create($request->all());
            return redirect()->route('clinics.index')->with('success', 'Phòng khám đã được tạo thành công.');
        }
    }
    

    /**
     * Hiển thị chi tiết một phòng khám.
     */
    public function show($id)
    {
        $clinic = Clinic::findOrFail($id);
        return view('backend.clinics.show', compact('clinic'));
    }

    /**
     * Hiển thị form chỉnh sửa phòng khám.
     */
    public function edit($id)
    {
        $clinic = Clinic::findOrFail($id);
        return view('backend.clinics.edit', compact('clinic'));
    }

    /**
     * Cập nhật phòng khám trong cơ sở dữ liệu.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
        ]);

        $clinic = Clinic::findOrFail($id);
        $clinic->update($request->all());

        return redirect()->route('clinics.index')->with('success', 'Phòng khám đã được cập nhật.');
    }

    /**
     * Xóa phòng khám khỏi cơ sở dữ liệu.
     */
    public function destroy($id)
    {
        $clinic = Clinic::findOrFail($id);
        $clinic->delete();

        return redirect()->route('clinics.index')->with('success', 'Phòng khám đã bị xóa.');
    }
}
