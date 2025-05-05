<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ApiCategoryController extends Controller
{
    /**
     * Lấy tất cả danh mục, bao gồm cha và con.
     */
    public function index()
    {
        $categories = Category::with('parent')->orderBy('id', 'DESC')->get();
        return response()->json($categories);
    }

    /**
     * Tạo mới danh mục.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'      => 'required|string|max:255',
            'summary'   => 'nullable|string',
            'photo'     => 'nullable|string',
            'status'    => 'required|in:active,inactive',
            'parent_id' => 'nullable|exists:categories,id',
        ]);

        $slug = Str::slug($request->name);
        if (Category::where('slug', $slug)->exists()) {
            $slug .= '-' . now()->format('YmdHis') . '-' . rand(10, 99);
        }

        $category = Category::create([
            'name'      => $request->name,
            'slug'      => $slug,
            'summary'   => $request->summary,
            'photo'     => $request->photo,
            'status'    => $request->status,
            'parent_id' => $request->parent_id,
        ]);

        return response()->json(['success' => 'Tạo danh mục thành công', 'data' => $category], 201);
    }

    /**
     * Cập nhật danh mục.
     */
    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $request->validate([
            'name'      => 'required|string|max:255',
            'summary'   => 'nullable|string',
            'photo'     => 'nullable|string',
            'status'    => 'required|in:active,inactive',
            'parent_id' => 'nullable|exists:categories,id|not_in:' . $id, // tránh chọn chính nó làm cha
        ]);

        $category->update([
            'name'      => $request->name,
            'summary'   => $request->summary,
            'photo'     => $request->photo,
            'status'    => $request->status,
            'parent_id' => $request->parent_id,
        ]);

        return response()->json(['success' => 'Cập nhật danh mục thành công']);
    }

    /**
     * Xóa danh mục và chuyển các danh mục con về null.
     */
    public function destroy($id)
    {
        $category = Category::findOrFail($id);

        // Gỡ liên kết con trước khi xóa
        Category::where('parent_id', $id)->update(['parent_id' => null]);

        $category->delete();

        return response()->json(['success' => 'Xóa danh mục thành công']);
    }

    /**
     * Lấy danh sách danh mục con theo ID danh mục cha.
     */
    public function getChildByParent($id)
    {
        $parent = Category::findOrFail($id);
        $children = $parent->children()->get();

        return response()->json([
            'status' => true,
            'data' => $children,
        ]);
    }
}
