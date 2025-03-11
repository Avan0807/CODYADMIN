<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AffiliateLink;
use App\Models\Product;
use App\Models\ProductCommission;

class ApiAffiliateController extends Controller
{
    public function generateLink($product_slug)
    {
        $doctorID = Auth::id();

        // ✅ Tìm sản phẩm theo slug
        $product = Product::where('slug', $product_slug)->firstOrFail();

        // ✅ Xóa các bản ghi trùng lặp, chỉ giữ lại 1 bản ghi duy nhất
        AffiliateLink::where('doctor_id', $doctorID)
            ->where('product_id', $product->id)
            ->orderBy('id', 'DESC')
            ->skip(1)
            ->delete();

        // ✅ Kiểm tra xem link đã tồn tại chưa (sau khi xóa trùng)
        $existingLink = AffiliateLink::where([
            ['doctor_id', $doctorID],
            ['product_id', $product->id]
        ])->first();

        $hashRef = "";
        $affiliate_link = "";
        $commissionPercentage = 0;

        // ✅ Lấy commission từ bảng product_commissions nếu chưa có
        if (!$existingLink || is_null($existingLink->commission_percentage)) {
            $commissionData = Product::where('product_id', $product->id)->first();
            $commissionPercentage = $commissionData ? $commissionData->commission_percentage : 10.00;
        }

        if ($existingLink) {
            $hashRef = $existingLink->hash_ref;
            $affiliate_link = $existingLink->product_link ?? "https://toikhoe.vn/product-detail/{$product->slug}?ref={$hashRef}";

            // ✅ Cập nhật commission hoặc product_link nếu thiếu
            $existingLink->update([
                'product_link' => $affiliate_link,
                'commission_percentage' => $existingLink->commission_percentage ?? $commissionPercentage
            ]);
        } else {
            // ✅ Tạo hash_ref mới
            $hashRef = hash('sha256', $doctorID . $product->id . time());

            // ✅ Tạo link sản phẩm
            $affiliate_link = "https://toikhoe.vn/product-detail/{$product->slug}?ref={$hashRef}";

            // ✅ Lưu link mới vào DB
            $existingLink = AffiliateLink::create([
                'doctor_id' => $doctorID,
                'product_id' => $product->id,
                'hash_ref' => $hashRef,
                'product_link' => $affiliate_link,
                'commission_percentage' => $commissionPercentage
            ]);
        }

        // ✅ Tạo open_app_link theo yêu cầu mới
        $openAppLink = "https://toikhoe.vn/product-detail/{$product->slug}?ref={$hashRef}";

        return response()->json([
            'message' => $existingLink->wasRecentlyCreated ? 'Link Affiliate được tạo thành công!' : 'Link Affiliate đã tồn tại!',
            'affiliate_link' => $affiliate_link,
            'deep_link' => "https://toikhoe.vn/deep-link/product/{$product->slug}?ref={$hashRef}",
            'open_app_link' => $openAppLink,
            'fallback_url' => $openAppLink,
            'commission_percentage' => $commissionPercentage,
            'data' => $existingLink
        ], $existingLink->wasRecentlyCreated ? 201 : 200);
    }

    public function trackClick(Request $request, $hash_ref)
    {
        // ✅ Tìm affiliate link theo hash_ref
        $affiliate = \DB::table('affiliate_links')->where('hash_ref', $hash_ref)->first();

        if (!$affiliate) {
            return response()->json(['error' => 'Affiliate link không tồn tại.'], 404);
        }

        $ip_address = $request->ip();
        $user_agent = $request->header('User-Agent');
        $doctor_id = $affiliate->doctor_id;
        $product_id = $affiliate->product_id;

        // ✅ Kiểm tra xem IP/User-Agent đã click trong 10 phút gần đây chưa (chống spam điểm)
        $recentClick = \DB::table('affiliate_clicks')
            ->where('doctor_id', $doctor_id)
            ->where('product_id', $product_id)
            ->where(function ($query) use ($ip_address, $user_agent) {
                $query->where('ip_address', $ip_address)
                      ->orWhere('user_agent', $user_agent);
            })
            ->where('created_at', '>', now()->subMinutes(10))
            ->exists();

        // ✅ Lưu thông tin click
        \DB::table('affiliate_clicks')->insert([
            'doctor_id' => $doctor_id,
            'product_id' => $product_id,
            'hash_ref' => $hash_ref,  // Sử dụng hash_ref làm mã nhận diện
            'ip_address' => $ip_address,
            'user_agent' => $user_agent,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $pointsAdded = 0;

        // ✅ Nếu chưa click gần đây => Cộng điểm
        if (!$recentClick) {
            \DB::table('doctors')->where('id', $doctor_id)->increment('points', 1);
            $pointsAdded = 1;
        }

        return response()->json([
            'message' => 'Click được ghi nhận!',
            'doctor_id' => $doctor_id,
            'product_id' => $product_id,
            'points_added' => $pointsAdded
        ], 200);
    }




    // ✅ API lấy danh sách sản phẩm tiếp thị của bác sĩ đang đăng nhập
    public function getAffiliateProducts()
    {
        $doctor = Auth::user(); // Lấy bác sĩ đăng nhập

        if (!$doctor) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn chưa đăng nhập.',
            ], 401);
        }

        // Lấy danh sách sản phẩm tiếp thị
        $affiliateProducts = AffiliateLink::where('doctor_id', $doctor->id)
            ->with('product') // Lấy thông tin sản phẩm liên kết
            ->get()
            ->map(function ($affiliate) {
                // Nếu commission_percentage bị NULL, lấy từ bảng ProductCommission hoặc mặc định 10%
                $commissionPercentage = $affiliate->commission_percentage ??
                    Product::where('product_id', $affiliate->product_id)->value('commission_percentage') ??
                    0;

                return [
                    'product_name' => $affiliate->product->name,
                    'product_link' => $affiliate->product_link,
                    'commission_percentage' => $commissionPercentage . '%',
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'Danh sách sản phẩm tiếp thị của bác sĩ',
            'data' => $affiliateProducts
        ], 200);
    }

}
