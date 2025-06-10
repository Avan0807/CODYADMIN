<?php

use App\Models\Message;
use App\Models\Category;
use App\Models\Order;
use App\Models\Wishlist;
use App\Models\Shipping;
use App\Models\Cart;

// use Auth;
class Helper{
    public static function messageList()
    {
        return Message::whereNull('read_at')->orderBy('created_at', 'desc')->get();
    }


    public static function getAllCategory() {
        return Category::getAllParentWithChild();  // Gọi static đúng cách
    }


    public static function getCombinedCategoriesMenu()
    {
        // Lấy các danh mục cấp 1 có type = 'product'
        $parentCategories = Category::where('status', 'active')
            ->where('type', 'product')
            ->whereNull('parent_id')
            ->orderBy('display_order')
            ->get();

        if ($parentCategories->count() > 0) {
            ?>
            <li class="mega-menu-container">
                <a href="javascript:void(0);">Danh Mục <i class="ti-angle-down"></i></a>
                <div class="mega-menu">
                    <div class="mega-menu-inner">
                        <div class="mega-menu-left">
                            <ul class="mega-menu-categories">
                                <?php foreach ($parentCategories as $index => $parent): ?>
                                    <li class="<?= ($index === 0) ? 'active' : '' ?>" data-category="<?= $parent->slug ?>">
                                        <a href="javascript:void(0);">
                                            <?php if ($parent->icon && strpos($parent->icon, 'fa-') !== false): ?>
                                                <i class="<?= $parent->icon ?>"></i>
                                            <?php endif; ?>
                                            <?= $parent->name ?>
                                            <i class="ti-angle-right"></i>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>

                        <div class="mega-menu-right">
                            <?php foreach ($parentCategories as $index => $parent): ?>
                                <div class="mega-submenu <?= ($index === 0) ? 'active' : '' ?>" id="submenu-<?= $parent->slug ?>">
                                    <div class="mega-submenu-group">
                                        <h4>
                                            <a href="<?= route('product-cat', $parent->slug) ?>">
                                                <?php if ($parent->icon && strpos($parent->icon, 'fa-') !== false): ?>
                                                    <i class="<?= $parent->icon ?>"></i>
                                                <?php endif; ?>
                                                <?= $parent->name ?>
                                            </a>
                                        </h4>

                                        <?php
                                        $childCategories = Category::where('status', 'active')
                                            ->where('type', 'product')
                                            ->where('parent_id', $parent->id)
                                            ->orderBy('display_order')
                                            ->get();
                                        ?>

                                        <?php if ($childCategories->count()): ?>
                                            <ul>
                                                <?php foreach ($childCategories as $child): ?>
                                                    <li>
                                                        <a href="<?= route('product-sub-cat', [$parent->slug, $child->slug]) ?>">
                                                            <?= $child->name ?>
                                                        </a>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </li>
            <?php
        }
    }


    public static function productCategoryList($option = 'all') {
        if ($option === 'all') {
            return Category::where('type', 'product')->orderBy('id', 'DESC')->get();
        }

        return Category::where('type', 'product')
            ->whereHas('products')
            ->orderBy('id', 'DESC')
            ->get();
    }


    // Cart Count

    public static function cartSummary($user_id = null)
    {
        if (!Auth::check()) return null;
        $user_id = $user_id ?? auth()->id();

        $items = Cart::with('product:id,title,slug,photo')
            ->where('user_id', $user_id)
            ->whereNull('order_id')
            ->get();

        $totalQty = $items->sum('quantity');
        $totalPrice = $items->sum(function($item) {
            return $item->price * $item->quantity;
        });

        return (object)[
            'count' => $items->count(),
            'quantity' => $totalQty,
            'total_price' => $totalPrice,
            'items' => $items,
        ];
    }


    // Wishlist Count
    public static function wishlistSummary($user_id = null)
    {
        if (!Auth::check()) return null;
        $user_id = $user_id ?? auth()->id();

        $items = Wishlist::with('product:id,title,slug,photo')
            ->where('user_id', $user_id)
            ->whereNull('cart_id')
            ->get();

        $totalQty = $items->sum('quantity');
        $totalPrice = $items->sum(function ($item) {
            return $item->price * $item->quantity;
        });

        return (object)[
            'count' => $items->count(),
            'quantity' => $totalQty,
            'total_price' => $totalPrice,
            'items' => $items,
        ];
    }


    // Total price with shipping and coupon
    public static function grandPrice($id,$user_id){
        $order=Order::find($id);
        dd($id);
        if($order){
            $shipping_price=(float)$order->shipping->price;
            $order_price=self::orderPrice($id,$user_id);
            return number_format((float)($order_price+$shipping_price),0,',','.');
        }else{
            return 0;
        }
    }


    // Admin home
    public static function earningPerMonth(){
        $month_data=Order::where('status','delivered')->get();
        // return $month_data;
        $price=0;
        foreach($month_data as $data){
            $price = $data->cart_info->sum('price');
        }
        return number_format((float)($price),0,',','.');
    }

    public static function shipping(){
        return Shipping::orderBy('id','DESC')->get();
    }

    public static function getAllProduct(){
        return \App\Models\Product::select('id', 'title', 'photo')
            ->where('status', 'active')
            ->orderBy('title')
            ->limit(50) // hoặc 100
            ->get();
    }
        /**
     * Lấy tên địa điểm GHN với cache
     */
    public static function getGHNLocationNames($provinceId = null, $districtId = null, $wardCode = null)
    {
        if (!$provinceId && !$districtId && !$wardCode) {
            return [
                'province' => 'N/A',
                'district' => 'N/A', 
                'ward' => 'N/A',
                'full_address' => 'N/A'
            ];
        }

        $cacheKey = "ghn_location_{$provinceId}_{$districtId}_{$wardCode}";
        
        return \Cache::remember($cacheKey, 3600, function() use ($provinceId, $districtId, $wardCode) {
            $ghn = app(\App\Services\GHNService::class);
            
            $provinceName = 'N/A';
            $districtName = 'N/A';
            $wardName = 'N/A';

            try {
                // Lấy tên tỉnh
                if ($provinceId) {
                    $provinces = $ghn->getProvinces();
                    $province = collect($provinces)->firstWhere('ProvinceID', $provinceId);
                    $provinceName = $province['ProvinceName'] ?? 'Unknown Province';
                }

                // Lấy tên quận/huyện
                if ($districtId && $provinceId) {
                    $districts = $ghn->getDistricts($provinceId);
                    $district = collect($districts)->firstWhere('DistrictID', $districtId);
                    $districtName = $district['DistrictName'] ?? 'Unknown District';
                }

                // Lấy tên phường/xã
                if ($wardCode && $districtId) {
                    $wards = $ghn->getWards($districtId);
                    $ward = collect($wards)->firstWhere('WardCode', $wardCode);
                    $wardName = $ward['WardName'] ?? 'Unknown Ward';
                }

            } catch (\Exception $e) {
                \Log::error('Error getting GHN location names: ' . $e->getMessage());
            }

            return [
                'province' => $provinceName,
                'district' => $districtName,
                'ward' => $wardName,
                'full_address' => $wardName . ', ' . $districtName . ', ' . $provinceName
            ];
        });
    }

    /**
     * Shortcut cho Order model
     */
    public static function getOrderGHNAddress($order)
    {
        if (!$order) return 'N/A';
        
        return self::getGHNLocationNames(
            $order->ghn_to_province_id ?? null,    // Sửa field name
            $order->ghn_to_district_id ?? null,    // Sửa field name  
            $order->ghn_to_ward_code ?? null       // Sửa field name
        );
    }

    /**
     * Format địa chỉ đầy đủ cho Order
     */
    public static function formatOrderShippingAddress($order)
    {
        if (!$order) return 'N/A';
        
        $ghnLocation = self::getOrderGHNAddress($order);
        $shippingAddress = $order->shipping_address ?? '';
        
        if ($shippingAddress && $ghnLocation['full_address'] !== 'N/A') {
            return $shippingAddress . '<br><small class="text-muted">' . $ghnLocation['full_address'] . '</small>';
        }
        
        return $shippingAddress ?: $ghnLocation['full_address'];
    }
    
    public static function getGHNServiceName($serviceId)
    {
        if (!$serviceId) return 'N/A';
        
        $serviceNames = [
            53320 => 'Hàng nặng',
            53321 => 'Giao hàng tiêu chuẩn',
            53322 => 'Giao hàng nhanh', 
            100039 => 'Giao hàng hỏa tốc',
        ];
        
        return $serviceNames[$serviceId] ?? 'Service ID: ' . $serviceId;
    }
}

?>
