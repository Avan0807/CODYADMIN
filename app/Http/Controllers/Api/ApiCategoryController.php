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

    /**
     * Lấy category theo slug và các posts liên quan
     */
    public function getCategoryBySlug($slug, Request $request)
    {
        try {
            // Tìm category theo slug
            $category = Category::where('slug', $slug)
                ->where('status', 'active')
                ->with(['parent:id,name,slug', 'children:id,name,slug,parent_id'])
                ->first();

            if (!$category) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy danh mục'
                ], 404);
            }

            // Query posts thuộc category này
            $postsQuery = Post::where('post_cat_id', $category->id)
                ->where('status', 'active');

            // Filter theo featured nếu có
            if ($request->has('featured') && $request->featured == 1) {
                $postsQuery->where('is_featured', 1);
            }

            // Search trong posts nếu có
            if ($request->has('search')) {
                $search = $request->search;
                $postsQuery->where(function($q) use ($search) {
                    $q->where('title', 'LIKE', "%{$search}%")
                      ->orWhere('summary', 'LIKE', "%{$search}%")
                      ->orWhere('description', 'LIKE', "%{$search}%");
                });
            }

            // Sắp xếp
            $sortBy = $request->get('sort_by', 'latest'); // latest, oldest, featured
            switch ($sortBy) {
                case 'oldest':
                    $postsQuery->orderBy('created_at', 'asc');
                    break;
                case 'featured':
                    $postsQuery->orderBy('is_featured', 'desc')->orderBy('created_at', 'desc');
                    break;
                default: // latest
                    $postsQuery->orderBy('created_at', 'desc');
                    break;
            }

            // Phân trang
            $perPage = min($request->get('per_page', 12), 50); // Tối đa 50 posts
            $posts = $postsQuery->paginate($perPage);

            // Format posts data
            $formattedPosts = $posts->getCollection()->map(function($post) {
                return [
                    'id' => $post->id,
                    'title' => $post->title,
                    'slug' => $post->slug,
                    'summary' => $post->summary,
                    'short_desc' => $post->short_desc,
                    'photo' => $post->photo,
                    'is_featured' => $post->is_featured,
                    'tags' => $post->tags,
                    'author_type' => $post->author_type,
                    'created_at' => $post->created_at->format('Y-m-d H:i:s'),
                    'updated_at' => $post->updated_at->format('Y-m-d H:i:s'),
                ];
            });

            // Thông tin category
            $categoryData = [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'type' => $category->type,
                'icon' => $category->icon,
                'summary' => $category->summary,
                'photo' => $category->photo,
                'display_order' => $category->display_order,
                'parent_id' => $category->parent_id,
                'parent' => $category->parent ? [
                    'id' => $category->parent->id,
                    'name' => $category->parent->name,
                    'slug' => $category->parent->slug,
                ] : null,
                'children' => $category->children->map(function($child) {
                    return [
                        'id' => $child->id,
                        'name' => $child->name,
                        'slug' => $child->slug,
                    ];
                }),
                'total_posts' => Post::where('post_cat_id', $category->id)->where('status', 'active')->count()
            ];

            return response()->json([
                'success' => true,
                'message' => 'Lấy thông tin danh mục và bài viết thành công',
                'category' => $categoryData,
                'posts' => $formattedPosts,
                'pagination' => [
                    'current_page' => $posts->currentPage(),
                    'per_page' => $posts->perPage(),
                    'total' => $posts->total(),
                    'last_page' => $posts->lastPage(),
                    'from' => $posts->firstItem(),
                    'to' => $posts->lastItem()
                ]
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