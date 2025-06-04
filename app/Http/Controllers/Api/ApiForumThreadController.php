<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ForumThread;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ApiForumThreadController extends Controller
{
    /**
     * Lấy danh sách threads với phân trang và filter
     */
    public function getThreads(Request $request)
    {
        try {
            $query = ForumThread::with([
                'category:id,name,slug',
                'user:id,name,email',
                'lastPoster:id,name'
            ]);

            // Filter theo category
            if ($request->has('category_id')) {
                $query->byCategory($request->category_id);
            }

            // Filter theo sticky
            if ($request->has('sticky') && $request->sticky == 1) {
                $query->sticky();
            }

            // Filter loại bỏ thread bị khóa
            if ($request->has('exclude_locked') && $request->exclude_locked == 1) {
                $query->notLocked();
            }

            // Search theo title hoặc content
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('title', 'LIKE', "%{$search}%")
                      ->orWhere('content', 'LIKE', "%{$search}%");
                });
            }

            // Sắp xếp
            $sortBy = $request->get('sort_by', 'hot'); // hot, latest, most_viewed, most_replied
            switch ($sortBy) {
                case 'latest':
                    $query->orderBy('created_at', 'desc');
                    break;
                case 'most_viewed':
                    $query->orderBy('view_count', 'desc');
                    break;
                case 'most_replied':
                    $query->orderBy('reply_count', 'desc');
                    break;
                default: // hot
                    $query->hot();
                    break;
            }

            // Phân trang
            $perPage = min($request->get('per_page', 20), 50); // Tối đa 50 items
            $threads = $query->paginate($perPage);

            // Format dữ liệu
            $formattedData = $threads->getCollection()->map(function($thread) {
                return [
                    'id' => $thread->id,
                    'title' => $thread->title,
                    'slug' => $thread->slug,
                    'excerpt' => $thread->excerpt,
                    'is_sticky' => $thread->is_sticky,
                    'is_locked' => $thread->is_locked,
                    'view_count' => $thread->view_count,
                    'reply_count' => $thread->reply_count,
                    'created_at' => $thread->created_at->format('Y-m-d H:i:s'),
                    'last_posted_at' => $thread->last_posted_at ? $thread->last_posted_at->format('Y-m-d H:i:s') : null,
                    'last_posted_time' => $thread->last_posted_time,
                    'category' => $thread->category ? [
                        'id' => $thread->category->id,
                        'name' => $thread->category->name,
                        'slug' => $thread->category->slug,
                    ] : null,
                    'user' => $thread->user ? [
                        'id' => $thread->user->id,
                        'name' => $thread->user->name,
                    ] : null,
                    'last_poster' => $thread->lastPoster ? [
                        'id' => $thread->lastPoster->id,
                        'name' => $thread->lastPoster->name,
                    ] : null
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Lấy danh sách threads thành công',
                'data' => $formattedData,
                'pagination' => [
                    'current_page' => $threads->currentPage(),
                    'per_page' => $threads->perPage(),
                    'total' => $threads->total(),
                    'last_page' => $threads->lastPage(),
                    'from' => $threads->firstItem(),
                    'to' => $threads->lastItem()
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

    /**
     * Lấy chi tiết thread theo slug
     */
    public function getThreadBySlug($slug)
    {
        try {
            $thread = ForumThread::where('slug', $slug)
                ->with([
                    'category:id,name,slug',
                    'user:id,name,email',
                    'lastPoster:id,name'
                ])
                ->first();

            if (!$thread) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy thread'
                ], 404);
            }

            // Tăng view count
            $thread->incrementViews();

            $data = [
                'id' => $thread->id,
                'title' => $thread->title,
                'slug' => $thread->slug,
                'content' => $thread->content,
                'is_sticky' => $thread->is_sticky,
                'is_locked' => $thread->is_locked,
                'view_count' => $thread->view_count,
                'reply_count' => $thread->reply_count,
                'created_at' => $thread->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $thread->updated_at->format('Y-m-d H:i:s'),
                'last_posted_at' => $thread->last_posted_at ? $thread->last_posted_at->format('Y-m-d H:i:s') : null,
                'category' => $thread->category ? [
                    'id' => $thread->category->id,
                    'name' => $thread->category->name,
                    'slug' => $thread->category->slug,
                ] : null,
                'user' => $thread->user ? [
                    'id' => $thread->user->id,
                    'name' => $thread->user->name,
                    'email' => $thread->user->email,
                ] : null,
                'last_poster' => $thread->lastPoster ? [
                    'id' => $thread->lastPoster->id,
                    'name' => $thread->lastPoster->name,
                ] : null
            ];

            return response()->json([
                'success' => true,
                'message' => 'Lấy chi tiết thread thành công',
                'data' => $data
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
     * Lấy threads theo category
     */
    public function getThreadsByCategory($categoryId)
    {
        try {
            $category = Category::find($categoryId);
            if (!$category) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy category'
                ], 404);
            }

            $threads = ForumThread::byCategory($categoryId)
                ->with(['user:id,name', 'lastPoster:id,name'])
                ->hot()
                ->paginate(20);

            $formattedData = $threads->getCollection()->map(function($thread) {
                return [
                    'id' => $thread->id,
                    'title' => $thread->title,
                    'slug' => $thread->slug,
                    'excerpt' => $thread->excerpt,
                    'is_sticky' => $thread->is_sticky,
                    'is_locked' => $thread->is_locked,
                    'view_count' => $thread->view_count,
                    'reply_count' => $thread->reply_count,
                    'created_at' => $thread->created_at->format('Y-m-d H:i:s'),
                    'last_posted_at' => $thread->last_posted_at ? $thread->last_posted_at->format('Y-m-d H:i:s') : null,
                    'user' => $thread->user ? [
                        'id' => $thread->user->id,
                        'name' => $thread->user->name,
                    ] : null,
                    'last_poster' => $thread->lastPoster ? [
                        'id' => $thread->lastPoster->id,
                        'name' => $thread->lastPoster->name,
                    ] : null
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Lấy threads theo category thành công',
                'data' => $formattedData,
                'category' => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug
                ],
                'pagination' => [
                    'current_page' => $threads->currentPage(),
                    'per_page' => $threads->perPage(),
                    'total' => $threads->total(),
                    'last_page' => $threads->lastPage()
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

    /**
     * Lấy threads hot nhất
     */
    public function getHotThreads(Request $request)
    {
        try {
            $limit = min($request->get('limit', 10), 20);
            
            $threads = ForumThread::with(['category:id,name,slug', 'user:id,name'])
                ->notLocked()
                ->orderBy('view_count', 'desc')
                ->orderBy('reply_count', 'desc')
                ->limit($limit)
                ->get();

            $formattedData = $threads->map(function($thread) {
                return [
                    'id' => $thread->id,
                    'title' => $thread->title,
                    'slug' => $thread->slug,
                    'view_count' => $thread->view_count,
                    'reply_count' => $thread->reply_count,
                    'is_sticky' => $thread->is_sticky,
                    'category' => $thread->category ? [
                        'id' => $thread->category->id,
                        'name' => $thread->category->name,
                        'slug' => $thread->category->slug,
                    ] : null,
                    'user' => $thread->user ? [
                        'id' => $thread->user->id,
                        'name' => $thread->user->name,
                    ] : null
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Lấy danh sách threads hot thành công',
                'data' => $formattedData
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
     * Tìm kiếm threads
     */
    public function searchThreads(Request $request)
    {
        try {
            $keyword = $request->get('q');
            if (!$keyword) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vui lòng nhập từ khóa tìm kiếm'
                ], 400);
            }

            $threads = ForumThread::where('title', 'LIKE', "%{$keyword}%")
                ->orWhere('content', 'LIKE', "%{$keyword}%")
                ->with(['category:id,name,slug', 'user:id,name'])
                ->hot()
                ->paginate(20);

            $formattedData = $threads->getCollection()->map(function($thread) {
                return [
                    'id' => $thread->id,
                    'title' => $thread->title,
                    'slug' => $thread->slug,
                    'excerpt' => $thread->excerpt,
                    'view_count' => $thread->view_count,
                    'reply_count' => $thread->reply_count,
                    'created_at' => $thread->created_at->format('Y-m-d H:i:s'),
                    'category' => $thread->category ? [
                        'id' => $thread->category->id,
                        'name' => $thread->category->name,
                        'slug' => $thread->category->slug,
                    ] : null,
                    'user' => $thread->user ? [
                        'id' => $thread->user->id,
                        'name' => $thread->user->name,
                    ] : null
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Tìm kiếm thành công',
                'data' => $formattedData,
                'keyword' => $keyword,
                'pagination' => [
                    'current_page' => $threads->currentPage(),
                    'per_page' => $threads->perPage(),
                    'total' => $threads->total(),
                    'last_page' => $threads->lastPage()
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tìm kiếm',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}