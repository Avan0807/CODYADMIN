<?php

namespace App\Http\Controllers;

use App\Models\CompanyNews;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CompanyNewsController extends Controller
{
    /**
     * Hiển thị danh sách tin tức công ty.
     */
    public function index()
    {
        $news = CompanyNews::latest()->get();
        return view('backend.company_news.index', compact('news'));
    }

    /**
     * Hiển thị form tạo mới tin tức.
     */
    public function create()
    {
        return view('backend.company_news.create');
    }

    /**
     * Lưu tin tức mới vào cơ sở dữ liệu.
     */
    public function store(Request $request)
    {
        // Xác thực dữ liệu đầu vào
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required',
            'image' => 'nullable|string', // Không bắt buộc phải có ảnh
            'published_at' => 'nullable|date'
        ]);
    
        // Lấy tất cả dữ liệu từ request
        $data = $request->all();
    
        // Kiểm tra nếu có ảnh được tải lên
        if ($request->has('image')) {
            // Laravel Filemanager sẽ xử lý việc lưu ảnh vào S3 và trả về đường dẫn
            $data['image'] = $request->input('image'); // Đây là giá trị URL của ảnh
        }
    
        // Tạo bản ghi tin tức trong cơ sở dữ liệu
        CompanyNews::create($data);
    
        // Điều hướng về trang danh sách tin tức với thông báo thành công
        return redirect()->route('company_news.index')->with('success', 'Tin tức công ty đã được tạo thành công.');
    }
    

    /**
     * Hiển thị chi tiết một tin tức.
     */
    public function show($id)
    {
        $news = CompanyNews::findOrFail($id);
        return view('backend.company_news.show', compact('news'));
    }

    /**
     * Hiển thị form chỉnh sửa tin tức.
     */
    public function edit($id)
    {
        $news = CompanyNews::findOrFail($id);
        return view('backend.company_news.edit', compact('news'));
    }

    /**
     * Cập nhật tin tức trong cơ sở dữ liệu.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required',
            'image' => 'string|required',  
            'published_at' => 'nullable|date'
        ]);
    
        $news = CompanyNews::findOrFail($id);
        $data = $request->all();
    
        // Xử lý hình ảnh
        if ($request->hasFile('image')) {
            // Nếu có tệp ảnh mới
            $data['image'] = $request->file('image')->store('company_news', 'public');
        } elseif (!empty($request->image)) {
            // Nếu có URL hình ảnh từ bên ngoài
            $data['image'] = $request->image;
        }
    
        $news->update($data);
    
        return redirect()->route('company_news.index')->with('success', 'Tin tức công ty đã được cập nhật.');
    }
    
    

    /**
     * Xóa tin tức khỏi cơ sở dữ liệu.
     */
    public function destroy($id)
    {
        $news = CompanyNews::findOrFail($id);

        // Xóa ảnh nếu có
        if ($news->image) {
            Storage::disk('public')->delete($news->image);
        }

        $news->delete();

        return redirect()->route('company_news.index')->with('success', 'Tin tức công ty đã bị xóa.');
    }
}
