<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\AffiliateLink;


class ApiProductController extends Controller
{
    /**
     * Hiển thị danh sách sản phẩm (có phân trang).
     */
    public function index()
    {
        $products = Product::where('status', 'active')->paginate(10);
        return response()->json([
            'success' => true,
            'message' => 'Danh sách sản phẩm',
            'data' => $products
        ], 200);
    }

    /**
     * Lưu sản phẩm mới vào CSDL.
     */
    public function store(Request $request)
    {
        // ✅ Kiểm tra quyền Admin
        $user = Auth::user();
        if (!$user || $user->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền thêm sản phẩm!',
            ], 403);
        }

        // ✅ Xác thực dữ liệu đầu vào
        $validator = Validator::make($request->all(), [
            'title'       => 'required|string|max:255',
            'summary'     => 'required|string',
            'description' => 'nullable|string',
            'photo'       => 'required|string',
            'size'        => 'nullable|array',
            'stock'       => 'required|min:0',
            'cat_id'      => 'required|exists:categories,id',
            'brand_id'    => 'nullable|exists:brands,id',
            'child_cat_id'=> 'nullable|exists:categories,id',
            'is_featured' => 'sometimes|boolean',
            'status'      => 'required|in:active,inactive',
            'condition'   => 'required|in:default,new,hot',
            'price'       => 'required|min:0',
            'discount'    => 'nullable|min:0|max:100'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ!',
                'errors' => $validator->errors()
            ], 400);
        }

        DB::beginTransaction();
        try {
            $data = $request->all();

            // ✅ Xử lý slug duy nhất
            $slug  = Str::slug($request->title);
            $count = Product::where('slug', $slug)->count();
            if ($count > 0) {
                $slug = $slug . '-' . date('ymdis') . '-' . rand(0, 999);
            }
            $data['slug'] = $slug;
            $data['is_featured'] = $request->input('is_featured', 0);

            // ✅ Xử lý size (nếu có)
            $data['size'] = $request->has('size') ? implode(',', $request->size) : null;

            // ✅ Tạo sản phẩm
            $product = Product::create($data);

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Sản phẩm đã được thêm thành công!',
                'data' => $product
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi thêm sản phẩm!',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Hiển thị chi tiết sản phẩm.
     */
    public function show($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Sản phẩm không tồn tại!'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Chi tiết sản phẩm',
            'data' => $product
        ], 200);
    }

    /**
     * Cập nhật thông tin sản phẩm.
     */
    public function update(Request $request, $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Sản phẩm không tồn tại!'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'title'       => 'sometimes|string|max:255',
            'summary'     => 'sometimes|string',
            'description' => 'nullable|string',
            'photo'       => 'sometimes|string',
            'size'        => 'nullable|array',
            'stock'       => 'sometimes|min:0',
            'cat_id'      => 'sometimes|exists:categories,id',
            'brand_id'    => 'nullable|exists:brands,id',
            'child_cat_id' => 'nullable|exists:categories,id',
            'is_featured' => 'sometimes|boolean',
            'status'      => 'sometimes|in:active,inactive',
            'condition'   => 'sometimes|in:default,new,hot',
            'price'       => 'sometimes|min:0',
            'discount'    => 'nullable|min:0|max:100'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ!',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $data = $request->all();
            $data['is_featured'] = $request->input('is_featured', 0);

            // Xử lý size (nếu có)
            $data['size'] = $request->has('size') ? implode(',', $request->size) : $product->size;

            // Cập nhật slug nếu có thay đổi title
            if ($request->has('title') && $request->title !== $product->title) {
                $slug = Str::slug($request->title);
                $count = Product::where('slug', $slug)->where('id', '!=', $id)->count();
                if ($count > 0) {
                    $slug = $slug . '-' . date('ymdis') . '-' . rand(0, 999);
                }
                $data['slug'] = $slug;
            }

            $product->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Sản phẩm đã được cập nhật thành công!',
                'data' => $product
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi cập nhật sản phẩm!',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Xóa sản phẩm khỏi CSDL.
     */
    public function destroy($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Sản phẩm không tồn tại!'
            ], 404);
        }

        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Sản phẩm đã được xóa thành công!'
        ], 200);
    }

    /**
     * Tìm sản phẩm theo slug.
     */
    public function findBySlug($slug)
    {
        $product = Product::where('slug', $slug)
            ->with(['category', 'subCategory']) // Load danh mục và danh mục con
            ->first();

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Sản phẩm không tồn tại!'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Chi tiết sản phẩm',
            'data' => [
                'id' => $product->id,
                'title' => $product->title,
                'slug' => $product->slug,
                'summary' => $product->summary,
                'description' => $product->description,
                'photo' => $product->photo,
                'stock' => $product->stock,
                'size' => $product->size,
                'condition' => $product->condition,
                'status' => $product->status,
                'price' => $product->price,
                'discount' => $product->discount,
                'is_featured' => $product->is_featured,
                'cat_id' => $product->cat_id,
                'child_cat_id' => $product->child_cat_id,
                'brand_id' => $product->brand_id,
                'created_at' => $product->created_at,
                'updated_at' => $product->updated_at,

                'cat_info' => $product->category ? [
                    'id' => $product->category->id,
                    'title' => $product->category->title,
                    'slug' => $product->category->slug,
                    'summary' => $product->category->summary,
                    'photo' => $product->category->photo,
                    'is_parent' => $product->category->is_parent,
                    'parent_id' => $product->category->parent_id,
                    'status' => $product->category->status,
                    'created_at' => $product->category->created_at,
                    'updated_at' => $product->category->updated_at,
                ] : null,

                'sub_cat_info' => $product->subCategory ? [
                    'id' => $product->subCategory->id,
                    'title' => $product->subCategory->title,
                    'slug' => $product->subCategory->slug,
                    'summary' => $product->subCategory->summary,
                    'photo' => $product->subCategory->photo,
                    'is_parent' => $product->subCategory->is_parent,
                    'parent_id' => $product->subCategory->parent_id,
                    'status' => $product->subCategory->status,
                    'created_at' => $product->subCategory->created_at,
                    'updated_at' => $product->subCategory->updated_at,
                ] : null
            ]
        ], 200);

    }

    public function trackAffiliate(Request $request, $product_slug)
    {
        // Tìm sản phẩm theo slug, nếu không có trả về lỗi 404
        $product = Product::where('slug', $product_slug)->firstOrFail();

        // Kiểm tra ref (hash_ref) trong URL
        if ($request->has('ref')) {
            $affiliate = AffiliateLink::findByHash($request->query('ref'));

            if ($affiliate) {
                session(['doctor_ref' => $affiliate->doctor_id]); // Lưu vào session
            }
        }

        return response()->json([
            'message' => 'Thông tin sản phẩm và affiliate reference được lưu',
            'product' => $product,
            'doctor_id' => session('doctor_ref') ?? null
        ], 200);
    }

}
