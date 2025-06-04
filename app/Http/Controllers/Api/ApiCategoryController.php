<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ApiCategoryController extends Controller
{
    /**
     * Lấy 6 danh mục cụ thể, mỗi danh mục có 3 bài viết mới nhất
     */
    public function getCategoriesWithLatestPosts()
    {
        try {
            // Danh sách ID categories cần lấy
            $categoryIds = [76, 79, 83, 86, 115, 116];
            
            // Lấy các categories với 3 bài viết mới nhất của mỗi category
            $categories = Category::whereIn('id', $categoryIds)
                ->where('status', 'active')
                ->with(['posts' => function($query) {
                    $query->where('status', 'active')
                          ->orderBy('created_at', 'desc')
                          ->limit(3)
                          ->select('id', 'title', 'slug', 'summary', 'description', 'photo', 'post_cat_id', 'created_at', 'updated_at');
                }])
                ->orderByRaw("FIELD(id, " . implode(',', $categoryIds) . ")") // Giữ thứ tự theo array
                ->get();

            // Format dữ liệu trả về
            $formattedData = $categories->map(function($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'icon' => $category->icon,
                    'summary' => $category->summary,
                    'photo' => $category->photo,
                    'display_order' => $category->display_order,
                    'posts' => $category->posts->map(function($post) {
                        return [
                            'id' => $post->id,
                            'title' => $post->title,
                            'slug' => $post->slug,
                            'summary' => $post->summary,
                            'content' => $post->description,
                            'photo' => $post->photo,
                            'created_at' => $post->created_at->format('Y-m-d H:i:s'),
                            'updated_at' => $post->updated_at->format('Y-m-d H:i:s'),
                        ];
                    })
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Lấy danh sách danh mục và bài viết thành công',
                'data' => $formattedData,
                'total_categories' => $categories->count()
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi lấy dữ liệu',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lấy Dịch vụ y tế (Phương pháp chữa bệnh) - 6 categories cụ thể
     */
    public function getMedicalServices()
    {
        try {
            // Danh sách ID categories cần lấy (Dịch vụ y tế)
            $medicalServiceIds = [88, 92, 96, 100, 115, 116];
            
            // Lấy các categories với 3 bài viết mới nhất của mỗi category
            $categories = Category::whereIn('id', $medicalServiceIds)
                ->where('status', 'active')
                ->with(['posts' => function($query) {
                    $query->where('status', 'active')
                          ->orderBy('created_at', 'desc')
                          ->limit(3)
                          ->select('id', 'title', 'slug', 'summary', 'description', 'photo', 'post_cat_id', 'created_at', 'updated_at');
                }])
                ->orderByRaw("FIELD(id, " . implode(',', $medicalServiceIds) . ")") // Giữ thứ tự theo array
                ->get();

            // Format dữ liệu trả về
            $formattedData = $categories->map(function($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'icon' => $category->icon,
                    'summary' => $category->summary,
                    'photo' => $category->photo,
                    'display_order' => $category->display_order,
                    'parent_id' => $category->parent_id,
                    'posts' => $category->posts->map(function($post) {
                        return [
                            'id' => $post->id,
                            'title' => $post->title,
                            'slug' => $post->slug,
                            'summary' => $post->summary,
                            'content' => $post->description,
                            'photo' => $post->photo,
                            'created_at' => $post->created_at->format('Y-m-d H:i:s'),
                            'updated_at' => $post->updated_at->format('Y-m-d H:i:s'),
                        ];
                    }),
                    'posts_count' => $category->posts->count()
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Lấy danh sách dịch vụ y tế thành công',
                'data' => $formattedData,
                'total_categories' => $categories->count()
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi lấy dữ liệu',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}