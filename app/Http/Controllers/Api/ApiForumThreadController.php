<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ForumThread;
use App\Models\Category;
use App\Models\ForumPost;
use App\Models\ForumStats;
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


    public function apiStoreThread(Request $request)
    {
        try {
            // Lấy user từ token
            $user = $request->user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể xác thực người dùng.',
                ], 401);
            }

            // Validation
            $validated = $request->validate([
                'title' => 'required|string|min:5|max:255',
                'content' => 'required|string|min:10',
                'category_id' => 'required|exists:categories,id'
            ]);

            // Kiểm tra danh mục có hợp lệ không
            $category = Category::where('id', $validated['category_id'])
                ->where('type', 'forum')
                ->where('status', 'active')
                ->first();

            if (!$category) {
                return response()->json([
                    'success' => false,
                    'message' => 'Danh mục không hợp lệ hoặc không tồn tại'
                ], 400);
            }

            // Tạo slug unique
            $slug = ForumThread::createSlug($validated['title']);

            // Tạo thread mới
            $thread = ForumThread::create([
                'title' => $validated['title'],
                'slug' => $slug,
                'content' => $validated['content'],
                'category_id' => $category->id,
                'user_id' => $user->id,
                'last_posted_at' => now(),
                'last_posted_by' => $user->id
            ]);

            // Cập nhật hoặc tạo forum stats
            ForumStats::updateOrCreate(
                ['category_id' => $category->id],
                [
                    'thread_count' => \DB::raw('thread_count + 1'),
                    'last_thread_id' => $thread->id,
                    'last_posted_at' => now(),
                    'last_posted_by' => $user->id
                ]
            );

            // Load relationships
            $thread->load(['user', 'category']);

            return response()->json([
                'success' => true,
                'message' => 'Tạo chủ đề thành công!',
                'data' => [
                    'thread' => [
                        'id' => $thread->id,
                        'title' => $thread->title,
                        'slug' => $thread->slug,
                        'content' => $thread->content,
                        'category_id' => $thread->category_id,
                        'user_id' => $thread->user_id,
                        'view_count' => $thread->view_count,
                        'reply_count' => $thread->reply_count,
                        'is_sticky' => $thread->is_sticky,
                        'is_locked' => $thread->is_locked,
                        'created_at' => $thread->created_at->format('Y-m-d H:i:s'),
                        'last_posted_at' => $thread->last_posted_at->format('Y-m-d H:i:s'),
                    ],
                    'category' => [
                        'id' => $category->id,
                        'name' => $category->name,
                        'slug' => $category->slug
                    ],
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'photo' => $user->photo ?? asset('images/avatar-placeholder.png')
                    ]
                ]
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tạo chủ đề',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Comment 

    public function getThreadComments($threadId, Request $request)
    {
        try {
            // Tìm thread theo ID
            $thread = ForumThread::find($threadId);

            if (!$thread) {
                return response()->json([
                    'success' => false,
                    'message' => 'Thread không tồn tại'
                ], 404);
            }

            // Lấy tất cả comments và replies
            $allComments = ForumPost::where('thread_id', $threadId)
                ->orderBy('created_at', 'asc')
                ->get();

            // Format comments với error handling cho relationships
            $formattedComments = $allComments->map(function ($comment) {
                // Lấy thông tin user an toàn
                $user = null;
                try {
                    $userModel = \App\Models\User::find($comment->user_id);
                    if ($userModel) {
                        $user = [
                            'id' => $userModel->id,
                            'name' => $userModel->name,
                            'phone' => $userModel->phone ?? '',
                            'photo' => $userModel->photo ?? asset('images/avatar-placeholder.png')
                        ];
                    }
                } catch (\Exception $e) {
                    // Nếu không tìm thấy user, tạo user mặc định
                    $user = [
                        'id' => $comment->user_id,
                        'name' => 'Người dùng ẩn danh',
                        'phone' => '',
                        'photo' => asset('images/avatar-placeholder.png')
                    ];
                }

                $data = [
                    'id' => $comment->id,
                    'content' => $comment->content,
                    'thread_id' => $comment->thread_id,
                    'user_id' => $comment->user_id,
                    'parent_id' => $comment->parent_id,
                    'like_count' => $comment->like_count ?? 0,
                    'created_at' => $comment->created_at->format('d/m/Y H:i'),
                    'created_at_human' => $comment->created_at->diffForHumans(),
                    'created_at_timestamp' => $comment->created_at->timestamp,
                    'is_reply' => !is_null($comment->parent_id),
                    'user' => $user
                ];

                // Thêm thông tin parent comment nếu có
                if ($comment->parent_id) {
                    try {
                        $parentComment = ForumPost::find($comment->parent_id);
                        if ($parentComment) {
                            $data['parent_comment'] = [
                                'id' => $parentComment->id,
                                'content' => \Illuminate\Support\Str::limit($parentComment->content, 50),
                                'user_id' => $parentComment->user_id
];
                        }
                    } catch (\Exception $e) {
                        // Nếu không tìm thấy parent comment
                        $data['parent_comment'] = null;
                    }
                }

                return $data;
            });

            // Lấy thông tin thread user an toàn
            $threadUser = null;
            try {
                $threadUserModel = \App\Models\User::find($thread->user_id);
                if ($threadUserModel) {
                    $threadUser = [
                        'id' => $threadUserModel->id,
                        'name' => $threadUserModel->name,
                        'photo' => $threadUserModel->photo ?? asset('images/avatar-placeholder.png')
                    ];
                }
            } catch (\Exception $e) {
                $threadUser = [
                    'id' => $thread->user_id,
                    'name' => 'Người dùng ẩn danh',
                    'photo' => asset('images/avatar-placeholder.png')
                ];
            }

            // Thống kê
            $parentComments = $allComments->whereNull('parent_id');
            $replies = $allComments->whereNotNull('parent_id');

            return response()->json([
                'success' => true,
                'data' => [
                    'thread' => [
                        'id' => $thread->id,
                        'title' => $thread->title,
                        'slug' => $thread->slug,
                        'content' => $thread->content,
                        'category_id' => $thread->category_id,
                        'user_id' => $thread->user_id,
                        'reply_count' => $thread->reply_count ?? 0,
                        'view_count' => $thread->view_count ?? 0,
                        'is_sticky' => $thread->is_sticky ?? false,
                        'is_locked' => $thread->is_locked ?? false,
                        'created_at' => $thread->created_at->format('d/m/Y H:i'),
                        'user' => $threadUser
                    ],
                    'comments' => $formattedComments,
                    'statistics' => [
                        'total_all' => $allComments->count(),
                        'total_comments' => $parentComments->count(),
                        'total_replies' => $replies->count()
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('API GetThreadComments Error: ' . $e->getMessage() . ' - Line: ' . $e->getLine());

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi lấy comments',
                'error' => $e->getMessage(),
                'line' => $e->getLine()
            ], 500);
        }
    }

    public function apiAddComment($threadId, Request $request)
    {
        try {
            // Xác thực người dùng
            $user = $request->user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn cần đăng nhập để bình luận.',
                ], 401);
            }

            // Tìm thread
            $thread = ForumThread::find($threadId);
            if (!$thread) {
                return response()->json([
                    'success' => false,
                    'message' => 'Thread không tồn tại.'
                ], 404);
            }

            // Tìm category
            $category = Category::find($thread->category_id);
            if (!$category) {
                return response()->json([
                    'success' => false,
                    'message' => 'Danh mục không tồn tại.'
                ], 404);
            }

            // Nếu thread bị khóa và user không phải admin
            if ($thread->is_locked && !$user->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Chủ đề đã bị khóa. Bạn không thể bình luận.'
                ], 403);
            }

            // Validate input
            $validated = $request->validate([
                'content' => 'required|string|min:2',
                'parent_id' => 'nullable|exists:forum_posts,id'
            ]);

            // Tạo bình luận mới
            $post = ForumPost::create([
                'content' => $validated['content'],
                'thread_id' => $thread->id,
                'user_id' => $user->id,
                'parent_id' => $request->input('parent_id'),
                'like_count' => 0
            ]);

            // Cập nhật thread
            $thread->increment('reply_count');
            $thread->update([
                'last_posted_at' => now(),
                'last_posted_by' => $user->id
            ]);

            // Cập nhật thống kê forum
            $stats = ForumStats::where('category_id', $category->id)->first();
            if ($stats) {
                $stats->increment('post_count');
                $stats->update([
                    'last_post_id' => $post->id,
                    'last_posted_at' => now(),
                    'last_posted_by' => $user->id
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Bình luận đã được đăng thành công!',
                'data' => [
                    'comment' => [
                        'id' => $post->id,
                        'content' => $post->content,
                        'thread_id' => $post->thread_id,
                        'user_id' => $post->user_id,
                        'parent_id' => $post->parent_id,
                        'like_count' => 0,
                        'created_at' => $post->created_at->format('d/m/Y H:i'),
                        'created_at_human' => $post->created_at->diffForHumans(),
                        'is_reply' => !is_null($post->parent_id),
                        'user' => [
                            'id' => $user->id,
                            'name' => $user->name,
                            'phone' => $user->phone ?? '',
                            'photo' => $user->photo ?? asset('images/avatar-placeholder.png')
                        ]
                    ],
                    'thread_stats' => [
                        'reply_count' => $thread->reply_count,
                        'last_posted_at' => $thread->last_posted_at->format('d/m/Y H:i')
                    ]
                ]
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('API AddComment Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi thêm bình luận.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

}