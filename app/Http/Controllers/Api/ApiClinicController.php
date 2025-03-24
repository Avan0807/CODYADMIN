<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use Illuminate\Http\JsonResponse;

class ApiClinicController extends Controller
{
    /**
     * Trả về danh sách phòng khám dưới dạng JSON.
     */
    public function index(): JsonResponse
    {
        $clinics = Clinic::latest()->get();
        return response()->json([
            'status' => 'success',
            'data' => $clinics
        ]);
    }
}
