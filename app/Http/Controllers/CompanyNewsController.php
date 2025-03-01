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
        $news = CompanyNews::latest()->paginate(10);
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
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'published_at' => 'nullable|date'
        ]);

        $data = $request->all();

        // Xử lý hình ảnh
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('company_news', 'public');
        }

        CompanyNews::create($data);

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
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'published_at' => 'nullable|date'
        ]);

        $news = CompanyNews::findOrFail($id);
        $data = $request->all();

        // Xử lý hình ảnh (nếu có)
        if ($request->hasFile('image')) {
            // Xóa ảnh cũ nếu có
            if ($news->image) {
                Storage::disk('public')->delete($news->image);
            }
            $data['image'] = $request->file('image')->store('company_news', 'public');
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
