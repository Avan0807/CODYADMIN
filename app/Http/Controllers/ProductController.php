<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * Hiển thị danh sách sản phẩm (trang admin).
     */
    public function index()
    {
        $products = Product::getAllProduct();
        return view('backend.product.index')->with('products', $products);
    }

    /**
     * Trang thêm mới sản phẩm.
     */
    public function create()
    {
        $brand = Brand::get();
        $category = Category::where('is_parent', 1)->get();
        return view('backend.product.create')
            ->with('categories', $category)
            ->with('brands', $brand);
    }

    /**
     * Lưu sản phẩm mới vào CSDL.
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'title'       => 'string|required',
            'summary'     => 'string|required',
            'description' => 'string|nullable',
            'photo'       => 'string|required',
            'size'        => 'nullable',
            'stock'       => "required|numeric",
            'cat_id'      => 'required|exists:categories,id',
            'brand_id'    => 'nullable|exists:brands,id',
            'child_cat_id' => 'nullable|exists:categories,id',
            'is_featured' => 'sometimes|in:1',
            'status'      => 'required|in:active,inactive',
            'condition'   => 'required|in:default,new,hot',
            'price'       => 'required|numeric',
            'discount'    => 'nullable|numeric'
        ]);

        $data = $request->all();

        // Xử lý slug
        $slug  = Str::slug($request->title);
        $count = Product::where('slug', $slug)->count();
        if ($count > 0) {
            $slug = $slug . '-' . date('ymdis') . '-' . rand(0, 999);
        }
        $data['slug'] = $slug;

        $data['is_featured'] = $request->input('is_featured', 0);

        // Xử lý size (nếu có)
        $size = $request->input('size');
        if ($size) {
            $data['size'] = implode(',', $size);
        } else {
            $data['size'] = '';
        }

        $status = Product::create($data);
        if ($status) {
            request()->session()->flash('success', 'Sản phẩm đã được thêm');
        } else {
            request()->session()->flash('error', 'Vui lòng thử lại!!');
        }
        return redirect()->route('product.index');
    }

    /**
     * Hiển thị thông tin chi tiết sản phẩm (nếu cần).
     */
    public function show($id)
    {
        // Chưa sử dụng
    }

    /**
     * Trang chỉnh sửa sản phẩm.
     */
    public function edit($id)
    {
        $brand    = Brand::get();
        $product  = Product::findOrFail($id);
        $category = Category::where('is_parent', 1)->get();
        $items    = Product::where('id', $id)->get();

        return view('backend.product.edit')
            ->with('product', $product)
            ->with('brands', $brand)
            ->with('categories', $category)
            ->with('items', $items);
    }

    /**
     * Cập nhật sản phẩm trong CSDL.
     */
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $this->validate($request, [
            'title'       => 'string|required',
            'summary'     => 'string|required',
            'description' => 'string|nullable',
            'photo'       => 'string|required',
            'size'        => 'nullable',
            'stock'       => "required|numeric",
            'cat_id'      => 'required|exists:categories,id',
            'child_cat_id' => 'nullable|exists:categories,id',
            'is_featured' => 'sometimes|in:1',
            'brand_id'    => 'nullable|exists:brands,id',
            'status'      => 'required|in:active,inactive',
            'condition'   => 'required|in:default,new,hot',
            'price'       => 'required|numeric',
            'discount'    => 'nullable|numeric'
        ]);

        $data = $request->all();
        $data['is_featured'] = $request->input('is_featured', 0);

        // Xử lý size (nếu có)
        $size = $request->input('size');
        if ($size) {
            $data['size'] = implode(',', $size);
        } else {
            $data['size'] = '';
        }

        $status = $product->fill($data)->save();
        if ($status) {
            request()->session()->flash('success', 'Cập nhật sản phẩm thành công');
        } else {
            request()->session()->flash('error', 'Vui lòng thử lại!!');
        }
        return redirect()->route('product.index');
    }

    /**
     * Xóa sản phẩm khỏi CSDL.
     */
    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $status  = $product->delete();

        if ($status) {
            request()->session()->flash('success', 'Đã xóa sản phẩm thành công');
        } else {
            request()->session()->flash('error', 'Đã xảy ra lỗi khi xóa sản phẩm');
        }
        return redirect()->route('product.index');
    }


}
