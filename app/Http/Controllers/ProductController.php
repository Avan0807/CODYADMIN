<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use Illuminate\Support\Str;
use Illuminate\Contracts\View\View as ViewContract;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
class ProductController extends Controller
{
    /**
     * Hiển thị danh sách sản phẩm (trang admin).
     */
    public function index()
    {
        $products = Product::with(['categories'])->get();
        return view('backend.product.index')->with('products', $products);
    }


    /**
     * Trang thêm mới sản phẩm.
     */
    public function create()
    {
        $brands = Brand::get();
        // Lấy tất cả các danh mục, không chỉ danh mục cha
        $categories = Category::orderBy('name')->get();

        return view('backend.product.create', compact('categories', 'brands'));
    }


    /**
     * Lưu sản phẩm mới vào CSDL.
     */
    public function store(Request $request)
    {
        // Validation
        $this->validate($request, [
            'title'       => 'string|required',
            'summary'     => 'string|required',
            'description' => 'string|nullable',
            'photo'       => 'string|required',
            'size'        => 'nullable',
            'stock'       => "required|numeric|min:0",
            'categories'  => 'required|array', // Đổi thành array
            'categories.*' => 'exists:categories,id', // Kiểm tra từng ID
            'brand_id'    => 'required|exists:brands,id',
            'is_featured' => 'sometimes|in:1',
            'status'      => 'required|in:active,inactive',
            'condition'   => 'required|in:default,new,hot',
            'price'       => 'required|numeric|min:0',
            'discount'    => 'nullable|numeric|min:0|max:100'
        ], [
            'title.required' => 'Tiêu đề là bắt buộc.',
            'summary.required' => 'Tóm tắt là bắt buộc.',
            'photo.required' => 'Hình ảnh là bắt buộc.',
            'categories.required' => 'Danh mục là bắt buộc.',
            'categories.array' => 'Danh mục không hợp lệ.',
            'categories.*.exists' => 'Danh mục không tồn tại.',
            'brand_id.required' => 'Thương hiệu là bắt buộc.',
            'stock.required' => 'Số lượng là bắt buộc.',
            'price.required' => 'Giá là bắt buộc.',
            'status.required' => 'Trạng thái là bắt buộc.',
            'condition.required' => 'Tình trạng là bắt buộc.',
            'condition.in' => 'Tình trạng không hợp lệ.',
            'price.min' => 'Giá phải lớn hơn 0.',
            'stock.min' => 'Số lượng phải lớn hơn hoặc bằng 0.',
            'discount.min' => 'Giảm giá không được nhỏ hơn 0.',
            'discount.max' => 'Giảm giá không được lớn hơn 100.'
        ]);

        $data = $request->except('categories'); // Loại trừ categories khỏi dữ liệu

        // Xử lý slug
        $slug  = Str::slug($request->title);
        $count = Product::where('slug', $slug)->count();
        if ($count > 0) {
            $slug = $slug . '-' . date('ymdis') . '-' . rand(0, 999);
        }
        $data['slug'] = $slug;

        // Mặc định is_featured là 0 nếu không chọn
        $data['is_featured'] = $request->input('is_featured', 0);

        // Xử lý discount, nếu không nhập thì mặc định là 0
        $data['discount'] = $request->input('discount', 0);

        // Xử lý size (nếu có)
        $size = $request->input('size');
        $data['size'] = $size ? implode(',', $size) : '';

        // Bắt đầu transaction để đảm bảo tính nhất quán
        DB::beginTransaction();
        try {
            // Lưu dữ liệu sản phẩm
            $product = Product::create($data);

            // Lưu mối quan hệ với danh mục
            if ($request->has('categories')) {
                $product->categories()->attach($request->categories);
            }

            DB::commit();
            request()->session()->flash('success', 'Sản phẩm đã được thêm');
            return redirect()->route('product.index');
        } catch (\Exception $e) {
            DB::rollBack();
            request()->session()->flash('error', 'Đã xảy ra lỗi: ' . $e->getMessage());
            return back()->withInput();
        }
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
        $product = Product::with('categories')->findOrFail($id);
        $brands = Brand::get();
        // Lấy tất cả danh mục thay vì chỉ danh mục cha
        $categories = Category::orderBy('name')->get();

        return view('backend.product.edit', compact('product', 'brands', 'categories'));
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
            'stock'       => "required|numeric|min:0",
            'categories'  => 'required|array', // Đổi thành array
            'categories.*' => 'exists:categories,id', // Kiểm tra từng ID
            'brand_id'    => 'required|exists:brands,id',
            'is_featured' => 'sometimes|in:1',
            'status'      => 'required|in:active,inactive',
            'condition'   => 'required|in:default,new,hot',
            'price'       => 'required|numeric|min:0',
            'discount'    => 'nullable|numeric|min:0|max:100'
        ], [
            'title.required' => 'Tiêu đề là bắt buộc.',
            'summary.required' => 'Tóm tắt là bắt buộc.',
            'photo.required' => 'Hình ảnh là bắt buộc.',
            'categories.required' => 'Danh mục là bắt buộc.',
            'categories.array' => 'Danh mục không hợp lệ.',
            'categories.*.exists' => 'Danh mục không tồn tại.',
            'brand_id.required' => 'Thương hiệu là bắt buộc.',
            'stock.required' => 'Số lượng là bắt buộc.',
            'price.required' => 'Giá là bắt buộc.',
            'status.required' => 'Trạng thái là bắt buộc.',
            'condition.required' => 'Tình trạng là bắt buộc.',
            'condition.in' => 'Tình trạng không hợp lệ.',
            'price.min' => 'Giá phải lớn hơn 0.',
            'stock.min' => 'Số lượng phải lớn hơn hoặc bằng 0.',
            'discount.min' => 'Giảm giá không được nhỏ hơn 0.',
            'discount.max' => 'Giảm giá không được lớn hơn 100.'
        ]);

        $data = $request->except('categories'); // Loại trừ categories khỏi dữ liệu
        $data['is_featured'] = $request->input('is_featured', 0);

        // Xử lý size (nếu có)
        $size = $request->input('size');
        if ($size) {
            $data['size'] = implode(',', $size);
        } else {
            $data['size'] = '';
        }

        // Bắt đầu transaction để đảm bảo tính nhất quán
        DB::beginTransaction();
        try {
            // Cập nhật thông tin sản phẩm
            $product->fill($data)->save();

            // Cập nhật mối quan hệ với danh mục
            if ($request->has('categories')) {
                // Sử dụng sync để xóa các quan hệ cũ và thêm các quan hệ mới
                $product->categories()->sync($request->categories);
            } else {
                // Xóa tất cả các quan hệ nếu không có danh mục nào được chọn
                $product->categories()->detach();
            }

            DB::commit();
            request()->session()->flash('success', 'Cập nhật sản phẩm thành công');
            return redirect()->route('product.index');
        } catch (\Exception $e) {
            DB::rollBack();
            request()->session()->flash('error', 'Đã xảy ra lỗi: ' . $e->getMessage());
            return back()->withInput();
        }
    }

    /**
     * Xóa sản phẩm khỏi CSDL.
     */
    public function destroy($id)
    {
        try {
            // Bắt đầu transaction để đảm bảo tính toàn vẹn dữ liệu
            DB::beginTransaction();

            // Xóa các bản ghi liên quan trong medicine_logs (nếu có)
            DB::table('medicine_logs')->where('product_id', $id)->delete();

            // Sau đó xóa sản phẩm
            $product = Product::findOrFail($id);
            $status = $product->delete();

            // Hoàn tất transaction
            DB::commit();

            if ($status) {
                request()->session()->flash('success', 'Đã xóa sản phẩm và thông báo liên quan thành công');
            } else {
                request()->session()->flash('error', 'Đã xảy ra lỗi khi xóa sản phẩm');
            }
        } catch (\Exception $e) {
            // Rollback transaction nếu có lỗi
            DB::rollBack();
            request()->session()->flash('error', 'Đã xảy ra lỗi: ' . $e->getMessage());
        }

        return redirect()->route('product.index');
    }


    // APIAPI
    public function apiGetAllProducts(Request $request)
    {
        try {
            // Sử dụng mối quan hệ n-n với categories thay vì category và subCategory
            // Thêm các mối quan hệ images, brand và reviews theo model mới
            $products = Product::with(['categories', 'brand', 'images'])
                        ->withCount('reviews')
                        ->withAvg('reviews', 'rate')
                        ->paginate(10);

            return response()->json([
                'success' => true,
                'products' => $products,
            ], 200);
        } catch (Exception $e) {
            Log::error('Error in fetching products: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Không thể lấy danh sách sản phẩm.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function apiGetProductById($id)
    {
        try {
            $product = Product::with(['category', 'subCategory', 'brand', 'reviews'])->find($id);

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy sản phẩm.',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Lấy thông tin sản phẩm thành công.',
                'product' => $product,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error in fetching product: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy sản phẩm.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateCommission(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'commission_percentage' => 'numeric|min:0|max:100'
        ]);

        $product->commission_percentage = $request->commission_percentage;
        $product->save();

        return response()->json([
            'message' => 'Cập nhật hoa hồng thành công!',
            'commission_percentage' => $product->commission_percentage
        ]);
    }

}
