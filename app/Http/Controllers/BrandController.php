<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Brand;
use Illuminate\Support\Str;
use Illuminate\Contracts\View\View as ViewContract;

class BrandController extends Controller
{
    /**
     * Danh sách thương hiệu
     */
    public function index()
    {
        $brand = Brand::orderBy('id', 'DESC')->get();
        return view('backend.brand.index')->with('brands', $brand);
    }

    /**
     * Hiển thị form thêm mới
     */
    public function create(): ViewContract
    {
        return view('backend.brand.create');
    }

    /**
     * Lưu thương hiệu mới
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'title' => 'string|required',
        ]);

        $data = $request->all();
        $slug = Str::slug($request->title);
        $count = Brand::where('slug', $slug)->count();

        if ($count > 0) {
            $slug = $slug . '-' . date('ymdis') . '-' . rand(0, 999);
        }

        $data['slug'] = $slug;

        $status = Brand::create($data);
        if ($status) {
            request()->session()->flash('success', 'Thêm thương hiệu thành công');
        } else {
            request()->session()->flash('error', 'Đã xảy ra lỗi, vui lòng thử lại');
        }
        return redirect()->route('brand.index');
    }

    /**
     * Chi tiết thương hiệu (tùy chọn)
     */
    public function show($id)
    {
        //
    }

    /**
     * Hiển thị form chỉnh sửa
     */
    public function edit($id)
    {
        $brand = Brand::find($id);
        if (!$brand) {
            request()->session()->flash('error', 'Không tìm thấy thương hiệu');
            return redirect()->back();
        }
        return view('backend.brand.edit')->with('brand', $brand);
    }

    /**
     * Cập nhật thương hiệu
     */
    public function update(Request $request, $id)
    {
        $brand = Brand::find($id);
        $this->validate($request, [
            'title' => 'string|required',
        ]);

        $data = $request->all();
        $status = $brand->fill($data)->save();

        if ($status) {
            request()->session()->flash('success', 'Cập nhật thương hiệu thành công');
        } else {
            request()->session()->flash('error', 'Đã xảy ra lỗi, vui lòng thử lại');
        }
        return redirect()->route('brand.index');
    }

    /**
     * Xóa thương hiệu
     */
    public function destroy($id)
    {
        $brand = Brand::find($id);
        if ($brand) {
            $status = $brand->delete();
            if ($status) {
                request()->session()->flash('success', 'Xóa thương hiệu thành công');
            } else {
                request()->session()->flash('error', 'Đã xảy ra lỗi, vui lòng thử lại');
            }
            return redirect()->route('brand.index');
        } else {
            request()->session()->flash('error', 'Không tìm thấy thương hiệu');
            return redirect()->back();
        }
    }
}
