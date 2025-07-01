<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Contracts\View\View as ViewContract;

class CategoryController extends Controller
{
    /**
     * Hiển thị danh sách danh mục.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $category = Category::getAllCategory();
        return view('backend.category.index')->with('categories', $category);
    }

    /**
     * Trang thêm mới danh mục.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $parent_cats = Category::whereNull('parent_id')->where('status', 'active')->orderBy('name')->get();

        return view('backend.category.create')->with('parent_cats', $parent_cats);
    }

    /**
     * Lưu danh mục mới vào CSDL.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name'       => 'string|required', // đổi từ 'title' => 'name'
            'summary'    => 'string|nullable',
            'photo'      => 'string|nullable',
            'status'     => 'required|in:active,inactive',
            'is_parent'  => 'sometimes|in:1',
            'parent_id'  => 'nullable|exists:categories,id',
        ]);

        $data = $request->all();
        $slug = Str::slug($request->name); // đổi từ $request->title
        $count = Category::where('slug', $slug)->count();
        if ($count > 0) {
            $slug = $slug . '-' . date('ymdis') . '-' . rand(0, 999);
        }
        $data['slug'] = $slug;
        $data['is_parent'] = $request->input('is_parent', 0);

        $status = Category::create($data);
        if ($status) {
            request()->session()->flash('success', 'Thêm danh mục thành công');
        } else {
            request()->session()->flash('error', 'Có lỗi xảy ra, vui lòng thử lại!');
        }

        return redirect()->route('category.index');
    }

    /**
     * Hiển thị thông tin chi tiết của danh mục (nếu cần).
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // Chưa sử dụng
    }

    /**
     * Trang chỉnh sửa danh mục.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id): ViewContract
    {
        $category    = Category::findOrFail($id);

        $parent_cats = Category::whereNull('parent_id')
                ->where('status', 'active')
                ->where('id', '!=', $category->id) // tránh chọn chính nó làm cha
                ->orderBy('name')
                ->get();

        return view('backend.category.edit')
            ->with('category', $category)
            ->with('parent_cats', $parent_cats);
    }

    /**
     * Cập nhật danh mục trong CSDL.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $this->validate($request, [
            'name'       => 'string|required', // đổi từ 'title'
            'summary'    => 'string|nullable',
            'photo'      => 'string|nullable',
            'status'     => 'required|in:active,inactive',
            'is_parent'  => 'sometimes|in:1',
            'parent_id'  => 'nullable|exists:categories,id',
        ]);

        $data = $request->all();
        $data['is_parent'] = $request->input('is_parent', 0);

        $status = $category->fill($data)->save();
        if ($status) {
            request()->session()->flash('success', 'Cập nhật danh mục thành công');
        } else {
            request()->session()->flash('error', 'Có lỗi xảy ra, vui lòng thử lại!');
        }

        return redirect()->route('category.index');
    }

    /**
     * Xóa danh mục khỏi CSDL.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        $child_cat_id = Category::where('parent_id', $id)->pluck('id');

        $status = $category->delete();
        if ($status) {
            if (count($child_cat_id) > 0) {
                Category::shiftChild($child_cat_id);
            }
            request()->session()->flash('success', 'Xóa danh mục thành công');
        } else {
            request()->session()->flash('error', 'Có lỗi xảy ra khi xóa danh mục');
        }

        return redirect()->route('category.index');
    }

    /**
     * Lấy danh sách danh mục con dựa trên danh mục cha (AJAX).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getChildByParent(Request $request)
    {
        $category = Category::findOrFail($request->id);

        $child_cat = Category::where('parent_id', $request->id)->pluck('name', 'id');

        if ($child_cat->isEmpty()) {
            return response()->json(['status' => false, 'msg' => '', 'data' => null]);
        } else {
            return response()->json(['status' => true, 'msg' => '', 'data' => $child_cat]);
        }
    }

    public function getTreatmentMethodDropdown()
        {
            try {

                $categoryIds = [92, 115, 116, 117];

                $categories = Category::with([
                    'children' => function ($query) {
                        $query->where('status', 'active')
                            ->orderBy('display_order', 'asc')
                            ->orderBy('name', 'asc')
                            ->with(['posts' => function ($postQuery) {
                                $postQuery->where('status', 'active')
                                    ->select('id', 'title', 'slug', 'photo', 'summary', 'description', 'post_cat_id', 'created_at')
                                    ->orderBy('created_at', 'desc')
                                    ->limit(5); // Giới hạn 5 bài viết mới nhất cho mỗi subcategory
                            }]);
                    },
                    'posts' => function ($query) {
                        $query->where('status', 'active')
                            ->select('id', 'title', 'slug', 'photo', 'summary', 'description', 'post_cat_id', 'created_at')
                            ->orderBy('created_at', 'desc')
                            ->limit(5); // 5 bài viết mới nhất cho parent category
                    }
                ])
                    ->where('status', 'active')
                    ->whereIn('id', $categoryIds)
                    ->orderByRaw("FIELD(id, " . implode(',', $categoryIds) . ")")
                    ->get();

                // Format data cho frontend
                $formattedCategories = $categories->map(function ($category) {
                    return [
                        'id' => $category->id,
                        'name' => $category->name,
                        'slug' => $category->slug,
                        'icon' => $category->icon ?: 'fas fa-stethoscope',
                        'summary' => $category->summary,
                        'photo' => $category->photo,
                        'url' => "/specialties/{$category->slug}",
                        'has_children' => $category->children->count() > 0,
                        'posts_count' => $category->posts->count(),
                        'posts' => $category->posts->map(function ($post) {
                            return [
                                'id' => $post->id,
                                'title' => $post->title,
                                'slug' => $post->slug,
                                'summary' => $post->summary,
                                'description' => $post->description,
                                'photo' => $post->photo,
                                'url' => "/posts/{$post->slug}",
                                'created_at' => $post->created_at->format('d/m/Y')
                            ];
                        }),
                        'children' => $category->children->map(function ($child) {
                            return [
                                'id' => $child->id,
                                'name' => $child->name,
                                'slug' => $child->slug,
    'summary' => $child->summary,
                                'url' => "/specialties/{$child->slug}",
                                'posts_count' => $child->posts->count(),
                                'posts' => $child->posts->map(function ($post) {
                                    return [
                                        'id' => $post->id,
                                        'title' => $post->title,
                                        'slug' => $post->slug,
                                        'summary' => $post->summary,
                                        'description' => $post->description,
                                        'photo' => $post->photo,
                                        'url' => "/posts/{$post->slug}",
                                        'created_at' => $post->created_at->format('d/m/Y')
                                    ];
                                })
                            ];
                        })
                    ];
                });

                return response()->json([
                    'success' => true,
                    'data' => $formattedCategories,
                    'total' => $categories->count(),
                    'message' => 'Lấy dữ liệu dropdown phương pháp chữa bệnh thành công'
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra khi lấy dữ liệu dropdown',
                    'error' => $e->getMessage()
                ], 500);
            }
        }

}
