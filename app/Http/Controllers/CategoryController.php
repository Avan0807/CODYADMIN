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
        $parent_cats = Category::where('is_parent', 1)
                               ->orderBy('title', 'ASC')
                               ->get();
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
            'title'      => 'string|required',
            'summary'    => 'string|nullable',
            'photo'      => 'string|nullable',
            'status'     => 'required|in:active,inactive',
            'is_parent'  => 'sometimes|in:1',
            'parent_id'  => 'nullable|exists:categories,id',
        ]);

        $data = $request->all();
        $slug = Str::slug($request->title);
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
        $parent_cats = Category::where('is_parent', 1)->get();
        $category    = Category::findOrFail($id);

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
            'title'      => 'string|required',
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
        $category  = Category::findOrFail($request->id);
        $child_cat = Category::getChildByParentID($request->id);

        if (count($child_cat) <= 0) {
            return response()->json(['status' => false, 'msg' => '', 'data' => null]);
        } else {
            return response()->json(['status' => true, 'msg' => '', 'data' => $child_cat]);
        }
    }
}
