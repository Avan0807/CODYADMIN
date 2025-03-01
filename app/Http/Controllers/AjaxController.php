<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AjaxController extends Controller
{
    public function method(Request $request)
    {
        // Xử lý logic Ajax request tại đây
        return response()->json(['message' => 'Ajax request thành công!']);
    }
}
