<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ApiCategoryController extends Controller
{
    /**
     * Danh sách danh mục (API).
     */
    public function index()
    {
        $categories = Category::getAllCategory();
        return response()->json($categories);
    }

    /**
     * Tạo mới danh mục (API).
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'title'     => 'string|required',
            'summary'   => 'string|nullable',
            'photo'     => 'string|nullable',
            'status'    => 'required|in:active,inactive',
            'is_parent' => 'sometimes|in:1',
            'parent_id' => 'nullable|exists:categories,id',
        ]);

        $data = $request->all();
        $slug = Str::slug($request->title);
        $count = Category::where('slug', $slug)->count();

        if ($count > 0) {
            $slug = $slug . '-' . date('ymdis') . '-' . rand(0, 999);
        }
        $data['slug'] = $slug;
        $data['is_parent'] = $request->input('is_parent', 0);

        $category = Category::create($data);

        if ($category) {
            return response()->json(['success' => 'Thêm danh mục thành công'], 201);
        } else {
            return response()->json(['error' => 'Có lỗi xảy ra, vui lòng thử lại!'], 400);
        }
    }

    /**
     * Cập nhật danh mục (API).
     */
    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $this->validate($request, [
            'title'     => 'string|required',
            'summary'   => 'string|nullable',
            'photo'     => 'string|nullable',
            'status'    => 'required|in:active,inactive',
            'is_parent' => 'sometimes|in:1',
            'parent_id' => 'nullable|exists:categories,id',
        ]);

        $data = $request->all();
        $data['is_parent'] = $request->input('is_parent', 0);

        $status = $category->fill($data)->save();

        if ($status) {
            return response()->json(['success' => 'Cập nhật danh mục thành công']);
        } else {
            return response()->json(['error' => 'Có lỗi xảy ra, vui lòng thử lại!'], 400);
        }
    }

    /**
     * Xóa danh mục (API).
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
            return response()->json(['success' => 'Xóa danh mục thành công']);
        } else {
            return response()->json(['error' => 'Có lỗi xảy ra khi xóa danh mục'], 400);
        }
    }

    /**
     * Lấy danh sách danh mục con dựa trên danh mục cha (API).
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
