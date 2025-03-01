<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Product;
use App\Models\Category;
use App\Models\PostTag;
use App\Models\PostCategory;
use App\Models\Post;
use App\Models\Cart;
use App\Models\Brand;
use App\Models\Booking;
use App\Models\Doctor;
use App\Models\User;
use Auth;
use Session;
use Newsletter;
use DB;
use Hash;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View as ViewContract;

class FrontendController extends Controller
{

    public function index(Request $request)
    {
        return redirect()->route($request->user()->role);
    }

    public function home()
    {
        $featured = Product::where('status', 'active')->where('is_featured', 1)->orderBy('price', 'DESC')->limit(2)->get();
        $posts = Post::where('status', 'active')->orderBy('id', 'DESC')->limit(3)->get();
        $banners = Banner::where('status', 'active')->limit(3)->orderBy('id', 'DESC')->get();
        // return $banner;
        $products = Product::where('status', 'active')->orderBy('id', 'DESC')->limit(8)->get();
        $category = Category::where('status', 'active')->where('is_parent', 1)->orderBy('title', 'ASC')->get();
        // return $category;
        // $featured = Product::with('cat_info:id,title')
        //     ->select('id', 'title', 'price', 'cat_id', 'photo')
        //     ->where('status', 'active')
        //     ->where('is_featured', 1)
        //     ->orderBy('price', 'DESC')
        //     ->limit(2)
        //     ->get();

        // $posts = Post::with('category:id,title', 'tags:id,title')
        //     ->select('id', 'title', 'post_cat_id', 'photo', 'created_at')
        //     ->where('status', 'active')
        //     ->orderBy('id', 'DESC')
        //     ->limit(3)
        //     ->get();

        // $banners = Banner::select('id', 'title', 'photo', 'link')
        //     ->where('status', 'active')
        //     ->orderBy('id', 'DESC')
        //     ->limit(3)
        //     ->get();

        // $products = Product::with('category:id,title', 'brand:id,title')
        //     ->select('id', 'title', 'price', 'cat_id', 'brand_id', 'photo')
        //     ->where('status', 'active')
        //     ->orderBy('id', 'DESC')
        //     ->limit(8)
        //     ->get();

        // $category = Category::select('id', 'title')
        // ->where('status', 'active')
        // ->where('is_parent', 1)
        // ->orderBy('title', 'ASC')
        // ->get();
        return view('frontend.index')
            ->with('featured', $featured)
            ->with('posts', $posts)
            ->with('banners', $banners)
            ->with('product_lists', $products)
            ->with('category_lists', $category);
    }

    public function aboutUs()
    {
        return view('frontend.pages.about-us');
    }

    public function contact()
    {
        return view('frontend.pages.contact');
    }

    public function productDetail($slug)
    {
        $product_detail = Product::getProductBySlug($slug);
        // dd($product_detail);
        return view('frontend.pages.product_detail')->with('product_detail', $product_detail);
    }

    // public function productGrids()
    // {
    //     $products = Product::query();

    //     if (!empty($_GET['category'])) {
    //         $slug = explode(',', $_GET['category']);
    //         // dd($slug);
    //         $cat_ids = Category::select('id')->whereIn('slug', $slug)->pluck('id')->toArray();
    //         // dd($cat_ids);
    //         $products->whereIn('cat_id', $cat_ids);
    //         // return $products;
    //     }
    //     if (!empty($_GET['brand'])) {
    //         $slugs = explode(',', $_GET['brand']);
    //         $brand_ids = Brand::select('id')->whereIn('slug', $slugs)->pluck('id')->toArray();
    //         $products->whereIn('brand_id', $brand_ids);
    //     }
    //     if (!empty($_GET['sortBy'])) {
    //         if ($_GET['sortBy'] == 'title') {
    //             $products = $products->where('status', 'active')->orderBy('title', 'ASC');
    //         }
    //         if ($_GET['sortBy'] == 'price') {
    //             $products = $products->orderBy('price', 'ASC');
    //         }
    //     }

    //     if (!empty($_GET['price'])) {
    //         $price = explode('-', $_GET['price']);
    //         // return $price;
    //         // if(isset($price[0]) && is_numeric($price[0])) $price[0]=floor(Helper::base_amount($price[0]));
    //         // if(isset($price[1]) && is_numeric($price[1])) $price[1]=ceil(Helper::base_amount($price[1]));

    //         $products->whereBetween('price', $price);
    //     }

    //     $recent_products = Product::where('status', 'active')->orderBy('id', 'DESC')->limit(3)->get();
    //     // Sort by number
    //     if (!empty($_GET['show'])) {
    //         $products = $products->where('status', 'active')->paginate($_GET['show']);
    //     } else {
    //         $products = $products->where('status', 'active')->paginate(9);
    //     }
    //     // $products = Product::where('status', 'active')->paginate(8);

    //     // Sort by name , price, category


    //     return view('frontend.pages.product-grids')->with('products', $products)->with('recent_products', $recent_products);
    // }
    public function productGrids(Request $request)
    {
        $products = Product::query();

        if ($request->has('category')) {
            $cat_ids = Category::select('id')
                ->whereIn('slug', explode(',', $request->category))
                ->pluck('id')
                ->toArray();

            $products->whereIn('cat_id', $cat_ids);
        }

        if ($request->has('brand')) {
            $brand_ids = Brand::select('id')
                ->whereIn('slug', explode(',', $request->brand))
                ->pluck('id')
                ->toArray();

            $products->whereIn('brand_id', $brand_ids);
        }

        if ($request->has('sortBy')) {
            $sortBy = $request->sortBy;
            if ($sortBy === 'title') {
                $products->orderBy('title', 'ASC');
            } elseif ($sortBy === 'price') {
                $products->orderBy('price', 'ASC');
            }
        }

        if ($request->has('price')) {
            $price = explode('-', $request->price);
            $products->whereBetween('price', $price);
        }

        $products = $products->select('id', 'title', 'price', 'photo', 'cat_id', 'brand_id')
            ->with('category:id,title', 'brand:id,title')
            ->where('status', 'active')
            ->paginate($request->get('show', 9));

        $recent_products = Product::select('id', 'title', 'price', 'photo')
            ->where('status', 'active')
            ->orderBy('id', 'DESC')
            ->limit(3)
            ->get();

        return view('frontend.pages.product-grids', compact('products', 'recent_products'));
    }

    public function productLists()
    {
        $products = Product::query();

        if (!empty($_GET['category'])) {
            $slug = explode(',', $_GET['category']);
            // dd($slug);
            $cat_ids = Category::select('id')->whereIn('slug', $slug)->pluck('id')->toArray();
            // dd($cat_ids);
            $products->whereIn('cat_id', $cat_ids)->paginate;
            // return $products;
        }
        if (!empty($_GET['brand'])) {
            $slugs = explode(',', $_GET['brand']);
            $brand_ids = Brand::select('id')->whereIn('slug', $slugs)->pluck('id')->toArray();
            $products->whereIn('brand_id', $brand_ids);
        }
        if (!empty($_GET['sortBy'])) {
            if ($_GET['sortBy'] == 'title') {
                $products = $products->where('status', 'active')->orderBy('title', 'ASC');
            }
            if ($_GET['sortBy'] == 'price') {
                $products = $products->orderBy('price', 'ASC');
            }
        }

        if (!empty($_GET['price'])) {
            $price = explode('-', $_GET['price']);
            // return $price;
            // if(isset($price[0]) && is_numeric($price[0])) $price[0]=floor(Helper::base_amount($price[0]));
            // if(isset($price[1]) && is_numeric($price[1])) $price[1]=ceil(Helper::base_amount($price[1]));

            $products->whereBetween('price', $price);
        }

        $recent_products = Product::where('status', 'active')->orderBy('id', 'DESC')->limit(3)->get();
        // Sort by number
        if (!empty($_GET['show'])) {
            $products = $products->where('status', 'active')->paginate($_GET['show']);
        } else {
            $products = $products->where('status', 'active')->paginate(6);
        }
        // Sort by name , price, category


        return view('frontend.pages.product-lists')->with('products', $products)->with('recent_products', $recent_products);
    }
    public function productFilter(Request $request)
    {
        $data = $request->all();
        // return $data;
        $showURL = "";
        if (!empty($data['show'])) {
            $showURL .= '&show=' . $data['show'];
        }

        $sortByURL = '';
        if (!empty($data['sortBy'])) {
            $sortByURL .= '&sortBy=' . $data['sortBy'];
        }

        $catURL = "";
        if (!empty($data['category'])) {
            foreach ($data['category'] as $category) {
                if (empty($catURL)) {
                    $catURL .= '&category=' . $category;
                } else {
                    $catURL .= ',' . $category;
                }
            }
        }

        $brandURL = "";
        if (!empty($data['brand'])) {
            foreach ($data['brand'] as $brand) {
                if (empty($brandURL)) {
                    $brandURL .= '&brand=' . $brand;
                } else {
                    $brandURL .= ',' . $brand;
                }
            }
        }
        // return $brandURL;

        $priceRangeURL = "";
        if (!empty($data['price_range'])) {
            $priceRangeURL .= '&price=' . $data['price_range'];
        }
        if (request()->is('e-shop.loc/product-grids')) {
            return redirect()->route('product-grids', $catURL . $brandURL . $priceRangeURL . $showURL . $sortByURL);
        } else {
            return redirect()->route('product-lists', $catURL . $brandURL . $priceRangeURL . $showURL . $sortByURL);
        }
    }
    public function productSearch(Request $request)
    {
        $recent_products = Product::where('status', 'active')->orderBy('id', 'DESC')->limit(3)->get();
        $products = Product::orwhere('title', 'like', '%' . $request->search . '%')
            ->orwhere('slug', 'like', '%' . $request->search . '%')
            ->orwhere('description', 'like', '%' . $request->search . '%')
            ->orwhere('summary', 'like', '%' . $request->search . '%')
            ->orwhere('price', 'like', '%' . $request->search . '%')
            ->orderBy('id', 'DESC')
            ->paginate('9');
        return view('frontend.pages.product-grids')->with('products', $products)->with('recent_products', $recent_products);
    }

    public function productBrand(Request $request)
    {
        $products = Brand::getProductByBrand($request->slug);
        $recent_products = Product::where('status', 'active')->orderBy('id', 'DESC')->limit(3)->get();
        if (request()->is('e-shop.loc/product-grids')) {
            return view('frontend.pages.product-grids')->with('products', $products->products)->with('recent_products', $recent_products);
        } else {
            return view('frontend.pages.product-lists')->with('products', $products->products)->with('recent_products', $recent_products);
        }
    }
    public function productCat(Request $request)
    {
        $products = Category::getProductByCat($request->slug);
        // return $request->slug;
        $recent_products = Product::where('status', 'active')->orderBy('id', 'DESC')->limit(3)->get();

        if (request()->is('e-shop.loc/product-grids')) {
            return view('frontend.pages.product-grids')->with('products', $products->products)->with('recent_products', $recent_products);
        } else {
            return view('frontend.pages.product-lists')->with('products', $products->products)->with('recent_products', $recent_products);
        }
    }
    public function productSubCat(Request $request)
    {
        $products = Category::getProductBySubCat($request->sub_slug);
        // return $products;
        $recent_products = Product::where('status', 'active')->orderBy('id', 'DESC')->limit(3)->get();

        if (request()->is('e-shop.loc/product-grids')) {
            return view('frontend.pages.product-grids')->with('products', $products->sub_products)->with('recent_products', $recent_products);
        } else {
            return view('frontend.pages.product-lists')->with('products', $products->sub_products)->with('recent_products', $recent_products);
        }
    }

    public function blog()
    {
        $post = Post::query();

        if (!empty($_GET['category'])) {
            $slug = explode(',', $_GET['category']);
            // dd($slug);
            $cat_ids = PostCategory::select('id')->whereIn('slug', $slug)->pluck('id')->toArray();
            $post->whereIn('post_cat_id', $cat_ids);
            // return $post;
        }
        if (!empty($_GET['tag'])) {
            $slug = explode(',', $_GET['tag']);
            // dd($slug);
            $tag_ids = PostTag::select('id')->whereIn('slug', $slug)->pluck('id')->toArray();
            // return $tag_ids;
            $post->where('post_tag_id', $tag_ids);
            // return $post;
        }

        if (!empty($_GET['show'])) {
            $post = $post->where('status', 'active')->orderBy('id', 'DESC')->paginate($_GET['show']);
        } else {
            $post = $post->where('status', 'active')->orderBy('id', 'DESC')->paginate(9);
        }
        // $post=Post::where('status','active')->paginate(8);
        $rcnt_post = Post::where('status', 'active')->orderBy('id', 'DESC')->limit(3)->get();
        return view('frontend.pages.blog')->with('posts', $post)->with('recent_posts', $rcnt_post);
    }

    public function blogDetail($slug)
    {
        $post = Post::getPostBySlug($slug);
        $rcnt_post = Post::where('status', 'active')->orderBy('id', 'DESC')->limit(3)->get();
        // return $post;
        return view('frontend.pages.blog-detail')->with('post', $post)->with('recent_posts', $rcnt_post);
    }

    public function blogSearch(Request $request)
    {
        // return $request->all();
        $rcnt_post = Post::where('status', 'active')->orderBy('id', 'DESC')->limit(3)->get();
        $posts = Post::orwhere('title', 'like', '%' . $request->search . '%')
            ->orwhere('quote', 'like', '%' . $request->search . '%')
            ->orwhere('summary', 'like', '%' . $request->search . '%')
            ->orwhere('description', 'like', '%' . $request->search . '%')
            ->orwhere('slug', 'like', '%' . $request->search . '%')
            ->orderBy('id', 'DESC')
            ->paginate(8);
        return view('frontend.pages.blog')->with('posts', $posts)->with('recent_posts', $rcnt_post);
    }

    public function blogFilter(Request $request)
    {
        $data = $request->all();
        // return $data;
        $catURL = "";
        if (!empty($data['category'])) {
            foreach ($data['category'] as $category) {
                if (empty($catURL)) {
                    $catURL .= '&category=' . $category;
                } else {
                    $catURL .= ',' . $category;
                }
            }
        }

        $tagURL = "";
        if (!empty($data['tag'])) {
            foreach ($data['tag'] as $tag) {
                if (empty($tagURL)) {
                    $tagURL .= '&tag=' . $tag;
                } else {
                    $tagURL .= ',' . $tag;
                }
            }
        }
        // return $tagURL;
        // return $catURL;
        return redirect()->route('blog', $catURL . $tagURL);
    }

    public function blogByCategory(Request $request)
    {
        $post = PostCategory::getBlogByCategory($request->slug);
        $rcnt_post = Post::where('status', 'active')->orderBy('id', 'DESC')->limit(3)->get();
        return view('frontend.pages.blog')->with('posts', $post->post)->with('recent_posts', $rcnt_post);
    }

    public function blogByTag(Request $request)
    {
        // dd($request->slug);
        $post = Post::getBlogByTag($request->slug);
        // return $post;
        $rcnt_post = Post::where('status', 'active')->orderBy('id', 'DESC')->limit(3)->get();
        return view('frontend.pages.blog')->with('posts', $post)->with('recent_posts', $rcnt_post);
    }

    // Login

    public function login()
    {
        return view('frontend.pages.login');
    }

    public function loginSubmit(Request $request)
    {
        $data = $request->all();
        if (Auth::attempt(['phone' => $data['phone'], 'password' => $data['password'], 'status' => 'active'])) {
            Session::put('user', $data['phone']);
            request()->session()->flash('success', 'Đã đăng nhập thành công!');
            return redirect()->route('home');
        } else {
            request()->session()->flash('error', 'Số điện thoại và mật khẩu không hợp lệ, vui lòng thử lại!');
            return redirect()->back();
        }
    }

    public function logout()
    {
        Session::forget('user');
        Auth::logout();
        request()->session()->flash('success', 'Đã đăng xuất thành công');
        return back();
    }

    public function register()
    {
        return view('frontend.pages.register');
    }

    public function registerSubmit(Request $request)
    {
        $this->validate($request, [
            'name' => 'string|required|min:2',
            'phone' => 'required|numeric|digits_between:10,15|unique:users,phone',
            'password' => 'required|min:6|confirmed',
        ]);
        $data = $request->all();
        $check = $this->create($data);
        Session::put('user', $data['phone']);
        if ($check) {
            request()->session()->flash('success', 'Đã đăng ký thành công');
            return redirect()->route('home');
        } else {
            request()->session()->flash('error', 'Vui lòng thử lại!');
            return back();
        }
    }

    public function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'phone' => $data['phone'],
            'password' => Hash::make($data['password']),
            'status' => 'active'
        ]);
    }

    // Reset password
    public function showResetForm()
    {
        return view('auth.passwords.old-reset');
    }

    public function subscribe(Request $request)
    {
        if (! Newsletter::isSubscribed($request->email)) {
            Newsletter::subscribePending($request->email);
            if (Newsletter::lastActionSucceeded()) {
                request()->session()->flash('success', 'Đã đăng ký! Vui lòng kiểm tra email của bạn');
                return redirect()->route('home');
            } else {
                Newsletter::getLastError();
                return back()->with('error', 'Có gì đó không ổn! Vui lòng thử lại');
            }
        } else {
            request()->session()->flash('error', 'Đã đăng ký');
            return back();
        }
    }

    public function bookingdoctor()
    {
        // Lấy danh sách bác sĩ từ database
        $doctors = Doctor::all();

        // Render view và truyền danh sách bác sĩ
        return view('frontend.pages.bookdoctor', compact('doctors'));
    }

    public function submitbookdoctor(Request $request)
    {
        // Xử lý lưu thông tin đặt khám
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|digits:10',
            'date' => 'required|date',
            'time' => 'required',
            'doctor_id' => 'required|exists:id',
            'consultation_type' => 'required|in:Online,In-Person',
            'note' => 'nullable|string|max:500',
        ]);

        // Thêm logic lưu dữ liệu vào database
        Booking::create($validated);

        return redirect()->route('bookdoctor')->with('success', 'Đặt khám thành công!');
    }
}
