<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MedicineLog;
use Illuminate\Http\Request;

class ApiMedicineLogController extends Controller
{
    // Lấy danh sách tất cả các log lịch uống thuốc
    public function index()
    {
        $logs = MedicineLog::with(['user', 'product'])->get();
        return response()->json($logs);
    }

    // Tạo mới một bản ghi medicine log
    public function store(Request $request)
    {
        // Validate dữ liệu
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'product_id' => 'required|exists:products,id',
            'dose' => 'required|string|max:191',
            'schedule_time' => 'required|date',
            'status' => 'required|in:pending,completed',
        ]);

        // Tạo bản ghi mới
        $medicineLog = MedicineLog::create($validated);

        // Trả về bản ghi vừa tạo
        return response()->json($medicineLog, 201);
    }

    // Lấy chi tiết một medicine log theo ID
    public function show($id)
    {
        $medicineLog = MedicineLog::with(['user', 'product'])->findOrFail($id);
        return response()->json($medicineLog);
    }

    // Cập nhật thông tin medicine log
    public function update(Request $request, $id)
    {
        // Validate dữ liệu
        $validated = $request->validate([
            'dose' => 'required|string|max:191',
            'schedule_time' => 'required|date',
            'status' => 'required|in:pending,completed',
        ]);

        $medicineLog = MedicineLog::findOrFail($id);
        $medicineLog->update($validated);

        return response()->json($medicineLog);
    }

    // Xóa một bản ghi medicine log
    public function destroy($id)
    {
        $medicineLog = MedicineLog::findOrFail($id);
        $medicineLog->delete();

        return response()->json(['message' => 'Deleted successfully']);
    }
}
