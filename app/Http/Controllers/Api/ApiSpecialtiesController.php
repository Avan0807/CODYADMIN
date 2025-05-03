<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApiSpecialtiesController extends Controller
{
    public function getSpecialtyData($specialtyId)
    {
        // Kiểm tra chuyên khoa có tồn tại không
        $specialty = DB::table('category_types')->where('id', $specialtyId)->first();
        
        if (!$specialty) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy chuyên khoa',
            ], 404);
        }
        
        // TRUY VẤN 1: Lấy danh sách bác sĩ theo chuyên khoa từ doctor_specializations
        $doctorsQuery = "
            SELECT d.id, d.name, d.photo, d.services, d.experience, 
                   d.working_hours, d.location, d.workplace, d.phone, 
                   d.email, d.status
            FROM doctors d
            JOIN doctor_specializations ds ON d.id = ds.doctor_id
            WHERE ds.specialization_id = ? AND d.status = ?
            GROUP BY d.id, d.name, d.photo, d.services, d.experience, 
                     d.working_hours, d.location, d.workplace, d.phone, 
                     d.email, d.status
        ";
        
        $doctors = DB::select($doctorsQuery, [$specialtyId, 'active']);
        
        // TRUY VẤN 2: Lấy danh sách tin tức từ post_categories
        // Giả sử post_cat_id trong bảng posts là liên kết đến bảng post_categories
        $newsQuery = "
            SELECT p.id, p.title, p.slug, p.summary, p.description, p.photo, p.created_at
            FROM posts p
            WHERE p.post_cat_id = ? AND p.status = ?
            ORDER BY p.created_at DESC
            LIMIT 10
        ";
        
        $news = DB::select($newsQuery, [$specialtyId, 'active']);
        
        // TRUY VẤN 3: Lấy danh sách sản phẩm từ categories theo parent_id
        $productsQuery = "
            SELECT c.id, c.title, c.slug, c.summary, c.photo
            FROM categories c
            WHERE c.parent_id = ? AND c.status = ?
            ORDER BY c.id ASC
            LIMIT 10
        ";
        
        $products = DB::select($productsQuery, [$specialtyId, 'active']);
        
        // Trả về dữ liệu
        return response()->json([
            'success' => true,
            'data' => [
                'specialty' => [
                    'id' => $specialty->id,
                    'name' => $specialty->name,
                    'slug' => $specialty->slug,
                    'icon' => $specialty->icon
                ],
                'doctors' => $doctors,
                'news' => $news,
                'products' => $products
            ]
        ]);
    }
}