<?php

use App\Models\Message;
use App\Models\Category;
use App\Models\PostTag;
use App\Models\PostCategory;
use App\Models\Order;
use App\Models\Wishlist;
use App\Models\Shipping;
use App\Models\Cart;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class Helper
{
    // Lấy danh sách tin nhắn chưa đọc (cache 1 phút)
    public static function messageList()
    {
        return Cache::remember('unread_messages', 60, function () {
            return Message::whereNull('read_at')->orderBy('created_at', 'desc')->get();
        });
    }

    // Lấy tất cả danh mục cha với danh mục con
    public static function getAllCategory()
    {
        return Cache::remember('categories_all', 3600, function () {
            return Category::with('child_cat')->get();
        });
    }

    // Lấy danh mục để hiển thị trên header
    public static function getHeaderCategory()
    {
        return self::getAllCategory();
    }

    // Lấy danh sách sản phẩm theo danh mục
    public static function productCategoryList($option = 'all')
    {
        return Cache::remember('product_categories_' . $option, 3600, function () use ($option) {
            return $option == 'all'
                ? Category::orderBy('id', 'DESC')->get()
                : Category::has('products')->orderBy('id', 'DESC')->get();
        });
    }

    // Lấy danh sách thẻ bài viết
    public static function postTagList($option = 'all')
    {
        return Cache::remember('post_tags_' . $option, 3600, function () use ($option) {
            return $option == 'all'
                ? PostTag::orderBy('id', 'DESC')->get()
                : PostTag::has('posts')->orderBy('id', 'DESC')->get();
        });
    }

    // Lấy danh sách danh mục bài viết
    public static function postCategoryList($option = 'all')
    {
        return Cache::remember('post_categories_' . $option, 3600, function () use ($option) {
            return $option == 'all'
                ? PostCategory::orderBy('id', 'DESC')->get()
                : PostCategory::has('posts')->orderBy('id', 'DESC')->get();
        });
    }

    // Đếm số lượng sản phẩm trong giỏ hàng
    public static function cartCount($user_id = null)
    {
        if (!Auth::check()) return 0;

        $user_id = $user_id ?? auth()->id();

        return Cache::remember("cart_count_{$user_id}", 60, function () use ($user_id) {
            return Cart::where('user_id', $user_id)->whereNull('order_id')->sum('quantity');
        });
    }

    // Lấy danh sách sản phẩm trong giỏ hàng
    public static function getAllProductFromCart($user_id = null)
    {
        if (!Auth::check()) return collect();

        $user_id = $user_id ?? auth()->id();

        return Cache::remember("cart_products_{$user_id}", 60, function () use ($user_id) {
            return Cart::with('product')->where('user_id', $user_id)->whereNull('order_id')->get();
        });
    }

    // Tính tổng giá trị giỏ hàng
    public static function totalCartPrice($user_id = null)
    {
        if (!Auth::check()) return 0;

        $user_id = $user_id ?? auth()->id();

        return Cache::remember("cart_total_price_{$user_id}", 60, function () use ($user_id) {
            return Cart::where('user_id', $user_id)
                ->whereNull('order_id')
                ->join('products', 'carts.product_id', '=', 'products.id')
                ->sum(DB::raw('products.price * carts.quantity'));
        });
    }

    // Đếm số lượng sản phẩm trong wishlist
    public static function wishlistCount($user_id = null)
    {
        if (!Auth::check()) return 0;

        $user_id = $user_id ?? auth()->id();

        return Cache::remember("wishlist_count_{$user_id}", 60, function () use ($user_id) {
            return Wishlist::where('user_id', $user_id)->whereNull('cart_id')->sum('quantity');
        });
    }

    // Lấy danh sách sản phẩm trong wishlist
    public static function getAllProductFromWishlist($user_id = null)
    {
        if (!Auth::check()) return collect();

        $user_id = $user_id ?? auth()->id();

        return Cache::remember("wishlist_products_{$user_id}", 60, function () use ($user_id) {
            return Wishlist::with('product')->where('user_id', $user_id)->whereNull('cart_id')->get();
        });
    }

    // Tính tổng giá trị wishlist
    public static function totalWishlistPrice($user_id = null)
    {
        if (!Auth::check()) return 0;

        $user_id = $user_id ?? auth()->id();

        return Cache::remember("wishlist_total_price_{$user_id}", 60, function () use ($user_id) {
            return Wishlist::where('user_id', $user_id)->whereNull('cart_id')->sum('price');
        });
    }

    // Tính tổng giá trị đơn hàng bao gồm phí vận chuyển
    public static function grandPrice($id, $user_id)
    {
        $order = Order::find($id);

        if (!$order) return 0;

        $shipping_price = (float) optional($order->shipping)->price;
        $order_price = self::totalCartPrice($user_id);

        return number_format($order_price + $shipping_price, 0, ',', '.');
    }

    // Tính doanh thu mỗi tháng
    public static function earningPerMonth()
    {
        return Cache::remember('monthly_earnings', 60, function () {
            return Order::where('status', 'delivered')
                ->with('cart_info')
                ->get()
                ->sum(function ($order) {
                    return $order->cart_info->sum('price');
                });
        });
    }

    // Lấy danh sách phương thức vận chuyển
    public static function shipping()
    {
        return Cache::remember('shipping_methods', 3600, function () {
            return Shipping::orderBy('id', 'DESC')->get();
        });
    }
}
