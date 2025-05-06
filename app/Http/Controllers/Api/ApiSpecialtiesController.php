<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApiSpecialtiesController extends Controller
{
    public function getSpecialtyData($specialtyId)
    {
        // 1. Kiểm tra chuyên khoa có tồn tại không (từ bảng categories, type = specialist)
        $specialty = DB::table('categories')
            ->where('id', $specialtyId)
            ->where('status', 'active') // tuỳ chọn
            ->first();


        if (!$specialty) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy chuyên khoa',
            ], 404);
        }

        // 2. Lấy bác sĩ thuộc chuyên khoa từ bảng trung gian doctor_specializations
        $doctors = DB::table('doctors')
            ->join('doctor_specializations', 'doctors.id', '=', 'doctor_specializations.doctor_id')
            ->where('doctor_specializations.specialization_id', $specialtyId)
            ->where('doctors.status', 'active')
            ->select('doctors.*')
            ->get();


        // 3. Lấy bài viết có danh mục là chuyên khoa này (post_cat_id trỏ về id của categories)
        $news = DB::table('posts')
            ->where('post_cat_id', $specialtyId)
            ->where('status', 'active')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get(['id', 'title', 'slug', 'summary', 'description', 'photo', 'created_at']);

        // 4. Lấy sản phẩm có parent_id là ID chuyên khoa này (dùng bảng categories với type = product)
        $products = DB::table('products')
            ->where('cat_id', $specialtyId)
            ->where('status', 'active')
            ->orderBy('id')
            ->limit(10)
            ->get(['id', 'title', 'slug', 'summary', 'photo']);


        return response()->json([
            'success' => true,
            'data' => [
                'specialty' => [
                    'id'    => $specialty->id,
                    'name'  => $specialty->name,
                    'slug'  => $specialty->slug,
                    'icon'  => $specialty->icon,
                ],
                'doctors'  => $doctors,
                'news'     => $news,
                'products' => $products,
            ]
        ]);
    }

}
