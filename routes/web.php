<?php


use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Auth::routes(['register'=>false]);

// Reset password
Route::post('password-reset', 'FrontendController@showResetForm')->name('password-reset-form');

Route::get('/', function () {
    return redirect('/login');
});


Route::get('/cart',function(){
    return view('frontend.pages.cart');
})->name('cart');
Route::get('/checkout','CartController@checkout')->name('checkout')->middleware('user');
// Wishlist
Route::get('/wishlist',function(){
    return view('frontend.pages.wishlist');
})->name('wishlist');
Route::get('/wishlist/{slug}','WishlistController@wishlist')->name('add-to-wishlist')->middleware('user');
Route::get('wishlist-delete/{id}','WishlistController@wishlistDelete')->name('wishlist-delete');
Route::post('cart/order','OrderController@store')->name('cart.order');
Route::get('order/pdf/{id}','OrderController@pdf')->name('order.pdf');
Route::get('/income','OrderController@incomeChart')->name('product.order.income');
Route::get('/doctorincome','OrderController@doctorincomeChart')->name('product.doctororder.income');

// Route::get('/user/chart','AdminController@userPieChart')->name('user.piechart');
Route::get('/product-grids','FrontendController@productGrids')->name('product-grids');
Route::get('/product-lists','FrontendController@productLists')->name('product-lists');
Route::match(['get','post'],'/filter','FrontendController@productFilter')->name('shop.filter');
// Order Track
Route::get('/product/track','OrderController@orderTrack')->name('order.track');
Route::post('product/track/order','OrderController@productTrackOrder')->name('product.track.order');
// Blog
Route::get('/blog','FrontendController@blog')->name('blog');
Route::get('/blog-detail/{slug}','FrontendController@blogDetail')->name('blog.detail');
Route::get('/blog/search','FrontendController@blogSearch')->name('blog.search');
Route::post('/blog/filter','FrontendController@blogFilter')->name('blog.filter');
Route::get('blog-cat/{slug}','FrontendController@blogByCategory')->name('blog.category');
Route::get('blog-tag/{slug}','FrontendController@blogByTag')->name('blog.tag');


// Product Review
Route::resource('/review','ProductReviewController');
Route::post('product/{slug}/review', 'ReviewController@store')->name('review.store.product');

// Post Comment
Route::post('post/{slug}/comment','PostCommentController@store')->name('post-comment.store');
Route::resource('/comment','PostCommentController');
// Coupon
Route::post('/coupon-store','CouponController@couponStore')->name('coupon-store');
// Payment
Route::get('payment', 'PayPalController@payment')->name('payment');
Route::get('cancel', 'PayPalController@cancel')->name('payment.cancel');
Route::get('payment/success', 'PayPalController@success')->name('payment.success');



// Backend section start

Route::group(['prefix'=>'/admin','middleware'=>['auth','admin']],function(){
    Route::get('/','AdminController@index')->name('admin');
    Route::get('/file-manager',function(){
        return view('backend.layouts.file-manager');
    })->name('file-manager');
    // user route
    Route::resource('users','UsersController');
    // Doctor
    Route::resource('doctor','DoctorController');
    // Banner
    Route::resource('banner','BannerController');
    // Brand
    Route::resource('brand','BrandController');
    // Profile
    Route::get('/profile','AdminController@profile')->name('admin-profile');
    Route::post('/profile/{id}','AdminController@profileUpdate')->name('profile-update');
    // Category
    Route::resource('/category','CategoryController');
    // Product
    Route::resource('/product','ProductController');
    // Ajax for sub category
    Route::post('/category/{id}/child','CategoryController@getChildByParent');
    // POST category
    Route::resource('/post-category','PostCategoryController');
    // Post tag
    Route::resource('/post-tag','PostTagController');
    // Post
    Route::resource('/post','PostController');
    // Message
    Route::resource('/message','MessageController');
    Route::get('/message/five','MessageController@messageFive')->name('messages.five');

    // Order
    Route::resource('/order','OrderController');
    // Shipping
    Route::resource('/shipping','ShippingController');

    // Thêm routes mới
    Route::get('shipping-provinces', 'ShippingController@provinces')->name('shipping.provinces');
    Route::get('shipping-province/create', 'ShippingController@createProvince')->name('shipping.province.create');
    Route::post('shipping-province', 'ShippingController@storeProvince')->name('shipping.province.store');
    Route::get('shipping-province/{id}/edit', 'ShippingController@editProvince')->name('shipping.province.edit');
    Route::patch('shipping-province/{id}', 'ShippingController@updateProvince')->name('shipping.province.update');
    Route::delete('shipping-province/{id}', 'ShippingController@destroyProvince')->name('shipping.province.destroy');

    Route::get('shipping-locations', 'ShippingController@locations')->name('shipping.locations');
    Route::get('shipping-location/create', 'ShippingController@createLocation')->name('shipping.location.create');
    Route::post('shipping-location', 'ShippingController@storeLocation')->name('shipping.location.store');
    Route::get('shipping-location/{id}/edit', 'ShippingController@editLocation')->name('shipping.location.edit');
    Route::patch('shipping-location/{id}', 'ShippingController@updateLocation')->name('shipping.location.update');
    Route::delete('shipping-location/{id}', 'ShippingController@destroyLocation')->name('shipping.location.destroy');

    // Coupon
    Route::resource('/coupon','CouponController');
    // Settings
    Route::get('settings','AdminController@settings')->name('settings');
    Route::post('setting/update','AdminController@settingsUpdate')->name('settings.update');

    // Notification
    Route::get('/notification/{id}','NotificationController@show')->name('admin.notification');
    Route::get('/notifications','NotificationController@index')->name('all.notification');
    Route::delete('/notification/{id}','NotificationController@delete')->name('notification.delete');
    // Password Change
    Route::get('change-password', 'AdminController@changePassword')->name('change.password.form');
    Route::post('change-password', 'AdminController@changPasswordStore')->name('change.password');

    //Ajax
    Route::post('ajax/route', 'AjaxController@method')->middleware('verify.csrf.ajax');

    // 📌 **Thêm Routes cho các module mới**
    // Phòng khám (Clinics)
    Route::resource('/clinics', 'ClinicController');

    // Quản lý thông báo chiến dịch (Campaign Notifications)
    Route::resource('/campaign_notifications', 'CampaignNotificationController');

    // Tin tức công ty (Company News)
    Route::resource('/company_news', 'CompanyNewsController');

    Route::get('/products-affiliate', 'ProductAffiliateController@index')->name('products.affiliate.index');
    Route::post('/products/update-commission/{id}', 'ProductController@updateCommission')->name('products-affiliate.update-commission');

    Route::get('/affiliate-orders', 'AffiliateOrderController@index')->name('affiliate_orders.index');
    Route::post('/affiliate-orders/{id}/update-status', 'AffiliateOrderController@updateStatus')->name('admin.affiliate.orders.update');

    Route::get('/commissions', 'CommissionController@index')->name('commissions.index');
    Route::get('/commissions/{doctor_id}', 'CommissionController@show')->name('commission.detail');

});



Route::group(['prefix' => 'laravel-filemanager', 'middleware' => ['web', 'auth']], function () {
    \UniSharp\LaravelFilemanager\Lfm::routes();
});

