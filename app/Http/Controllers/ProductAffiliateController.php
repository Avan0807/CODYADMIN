<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AffiliateLink;

class ProductAffiliateController extends Controller
{
    /**
     * Hiển thị danh sách đơn hàng affiliate (sản phẩm có liên kết tiếp thị)
     */
    public function index()
    {
        // Lấy danh sách affiliate links có dữ liệu bác sĩ & sản phẩm
        $affiliateLinks = AffiliateLink::with(['doctor', 'product']) // Load quan hệ bác sĩ & sản phẩm
                                       ->orderBy('id', 'DESC')
                                       ->paginate(10);

        return view('backend.product_affiliate.index', compact('affiliateLinks'));
    }

    public function updateCommission(Request $request, $id)
    {
        $request->validate([
            'commission_percentage' => 'required|numeric|min:0|max:100',
        ]);

        $affiliate = AffiliateLink::findOrFail($id);
        $affiliate->commission_percentage = $request->commission_percentage;
        $affiliate->save();

        return response()->json(['success' => 'Cập nhật hoa hồng thành công!']);
    }

}
