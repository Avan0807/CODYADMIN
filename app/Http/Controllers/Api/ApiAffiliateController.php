<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AffiliateLink;
use App\Models\Product;
use App\Models\ProductCommission;

class ApiAffiliateController extends Controller
{
    public function generateLink($product_slug) {
        $doctorID = Auth::id();

        // ✅ Tìm sản phẩm theo slug
        $product = Product::where('slug', $product_slug)->firstOrFail();

        // ✅ Kiểm tra xem link đã tồn tại chưa
        $existingLink = AffiliateLink::where([
            ['doctor_id', $doctorID],
            ['product_id', $product->id]
        ])->first();

        $affiliate_link = "";
        $commissionPercentage = 0; // Giá trị mặc định
        $hashRef = ""; // Khởi tạo biến tránh lỗi

        if ($existingLink) {
            $affiliate_link = url("http://toikhoe.vn/product-detail/{$product->slug}?ref={$existingLink->hash_ref}");
            $hashRef = $existingLink->hash_ref; // Lấy hash_ref từ link đã tồn tại

            // ✅ Đảm bảo `product_link` luôn có dữ liệu
            if (empty($existingLink->product_link)) {
                $existingLink->update([
                    'product_link' => $affiliate_link
                ]);
            }

            $commissionPercentage = $existingLink->commission_percentage;
        } else {
            // ✅ Tạo mã hash_ref mới
            $hashRef = hash('sha256', $doctorID . $product->id . time());

            // ✅ Tạo link sản phẩm kèm hash_ref
            $affiliate_link = url("http://toikhoe.vn/product-detail/{$product->slug}?ref={$hashRef}");

            // ✅ Lưu vào bảng `affiliate_links`
            $affiliate = AffiliateLink::create([
                'doctor_id' => $doctorID,
                'product_id' => $product->id,
                'hash_ref' => $hashRef,
                'product_link' => $affiliate_link, // ✅ Đảm bảo lưu `product_link`
                'commission_percentage' => $commissionPercentage
            ]);
        }

        // ✅ Tạo deeplink để mở app
        $deep_link = "https://toikhoe.vn/deep-link/product/{$product->slug}?ref={$hashRef}";
        $open_app_link = "yourapp://product/{$product->slug}?ref={$hashRef}";
        $fallback_url = $affiliate_link;

        return response()->json([
            'message' => $existingLink ? 'Link Affiliate đã tồn tại!' : 'Link Affiliate được tạo thành công!',
            'affiliate_link' => $affiliate_link,
            'deep_link' => $deep_link,
            'open_app_link' => $open_app_link,
            'fallback_url' => $fallback_url,
            'commission_percentage' => $commissionPercentage,
            'data' => $existingLink ?? $affiliate
        ], $existingLink ? 200 : 201);
    }

    public function trackClick(Request $request, $affiliate_code) {
        // Tìm thông tin affiliate link
        $affiliate = \DB::table('affiliate_links')->where('affiliate_code', $affiliate_code)->first();

        if (!$affiliate) {
            return response()->json(['error' => 'Affiliate link không tồn tại.'], 404);
        }

        $ip_address = $request->ip();
        $user_agent = $request->header('User-Agent');
        $doctor_id = $affiliate->doctor_id;
        $product_id = $affiliate->product_id;

        // ✅ Lưu thông tin click vào bảng `affiliate_clicks` (bất kể có cộng điểm hay không)
        \DB::table('affiliate_clicks')->insert([
            'doctor_id' => $doctor_id,
            'product_id' => $product_id,
            'affiliate_code' => $affiliate_code,
            'ip_address' => $ip_address,
            'user_agent' => $user_agent,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // 🛑 Kiểm tra xem IP/User-Agent đã click trong 10 phút gần đây chưa (chống spam điểm)
        $recentClick = \DB::table('affiliate_clicks')
                        ->where('doctor_id', $doctor_id)
                        ->where('product_id', $product_id)
                        ->where(function ($query) use ($ip_address, $user_agent) {
                            $query->where('ip_address', $ip_address)
                                  ->orWhere('user_agent', $user_agent);
                        })
                        ->where('created_at', '>', now()->subMinutes(10)) // Chỉ tính điểm 1 lần mỗi 10 phút
                        ->exists();

        if (!$recentClick) {
            // ✅ Chưa có click gần đây => Cộng điểm cho bác sĩ
            \DB::table('doctors')->where('id', $doctor_id)->increment('points', 1);
            $pointsAdded = 1;
        } else {
            // 🛑 Nếu đã click gần đây => Không cộng điểm
            $pointsAdded = 0;
        }

        return response()->json([
            'message' => 'Click được ghi nhận!',
            'doctor_id' => $doctor_id,
            'product_id' => $product_id,
            'points_added' => $pointsAdded
        ], 200);
    }
}
