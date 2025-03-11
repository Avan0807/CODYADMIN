<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CompanyNews;
use Illuminate\Http\Request;

class ApiCompanyNewsController extends Controller
{
    /**
     * API: Lấy tất cả tin tức công ty.
     */
    public function getAllNews()
    {
        $news = CompanyNews::latest()->get();
        return response()->json($news);
    }

    /**
     * API: Lấy chi tiết một tin tức theo ID.
     */
    public function getNewsDetail($id)
    {
        $news = CompanyNews::findOrFail($id);
        return response()->json($news);
    }

    /**
     * API: Lấy 5 tin tức mới nhất.
     */
    public function getLatestNews()
    {
        $news = CompanyNews::latest()->take(5)->get();
        return response()->json($news);
    }
}
