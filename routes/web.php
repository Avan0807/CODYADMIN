<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AffiliateOrderController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Agent\DashboardAgentController;
use App\Http\Controllers\Agent\LoginController;
use App\Http\Controllers\Agent\LinkController;
use App\Http\Controllers\Agent\OrderAgentController;
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

// Root redirect 
Route::get('/', function () {
    // Kiểm tra đã login chưa và redirect về đúng dashboard
    if (Auth::guard('web')->check() || Auth::guard('admin')->check()) {
        return redirect('/admin');
    }
    
    if (Auth::guard('agent')->check()) {
        return redirect()->route('agent.dashboard');
    }
    
    // Chưa login → về admin login (default)
    return redirect('/admin/login');
});

// Admin login routes
Route::get('/admin/login', 'Auth\LoginController@showLoginForm')
    ->middleware('guest:web,admin')
    ->name('admin.login.form');
Route::post('/admin/login', 'Auth\LoginController@login')
    ->middleware('guest:web,admin')
    ->name('admin.login');

// Alias cho compatibility (nếu cần)
Route::get('/login', function() {
    return redirect('/admin/login');
})->name('login');



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
    // Post tag
    Route::resource('/post-tag','PostTagController');
    // Post
    Route::resource('/post','PostController');
    // Message
    Route::resource('/message','MessageController');
    Route::get('/message/five','MessageController@messageFive')->name('messages.five');

    // Order
    Route::resource('/order','OrderController');
    
    // 🚀 **THÊM MỚI - Ajax routes cho Order DataTables**
    Route::get('/order/data/ajax','OrderController@getOrdersData')->name('orders.data');
    Route::post('/order/delete/ajax','OrderController@ajaxDelete')->name('orders.ajax-delete');
    
    // Shipping
    Route::resource('/shipping','ShippingController');

    // affilate order 
    Route::get('/affiliate-orders', [AffiliateOrderController::class, 'index'])->name('admin.affiliate.orders.index');
    Route::post('/affiliate-orders/{id}/update', [AffiliateOrderController::class, 'update'])->name('admin.affiliate.orders.update');
    Route::get('/affiliate-orders/stats', [AffiliateOrderController::class, 'stats'])->name('admin.affiliate.orders.stats');
    Route::get('/affiliate-orders/report', [AffiliateOrderController::class, 'reportByDoctor'])->name('admin.affiliate.orders.report');
    Route::post('/affiliate-orders/bulk-pay', [AffiliateOrderController::class, 'bulkPay'])->name('admin.affiliate.orders.bulk_pay');
    Route::get('/affiliate-orders/export', [AffiliateOrderController::class, 'export'])->name('admin.affiliate.orders.export');

    //affile
    Route::get('/products/affiliate', 'ProductController@getAffiliateProducts')->name('products.affiliate');
    Route::post('/products/bulk-commission', 'ProductController@bulkUpdateCommission')->name('products.bulk-commission');
    Route::get('/products/affiliate-stats', 'ProductController@getAffiliateStats')->name('products.affiliate-stats');

    // Dashboard routes
    Route::get('/stats', [DashboardController::class, 'getStats'])->name('admin.stats');

    // Chart API routes
    Route::get('/dashboard/top-products', [DashboardController::class, 'topProducts'])->name('admin.dashboard.top-products');
    Route::get('/dashboard/order-status-trend', [DashboardController::class, 'orderStatusTrend'])->name('admin.dashboard.order-status-trend');
    Route::get('/dashboard/revenue-vs-commission', [DashboardController::class, 'revenueVsCommission'])->name('admin.dashboard.revenue-vs-commission');
    Route::get('/dashboard/top-doctors', [DashboardController::class, 'topDoctors'])->name('admin.dashboard.top-doctors');
    Route::get('/dashboard/order-growth', [DashboardController::class, 'orderGrowth'])->name('admin.dashboard.order-growth');

    // Export routes
    Route::get('/dashboard/export', [DashboardController::class, 'exportData'])->name('admin.dashboard.export');
    
    
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


    // Agent routes - tương tự như Doctor
    Route::resource('agent','AgentController');

    
    // Agent Orders - tương tự như Affiliate Orders  
    Route::get('/agent-orders', 'AgentOrderController@index')->name('agent.orders.index');
    Route::post('/agent-orders/{id}/update', 'AgentOrderController@update')->name('agent.orders.update');
    Route::get('/agent-orders/stats', 'AgentOrderController@stats')->name('agent.orders.stats');
    Route::get('/agent-orders/report', 'AgentOrderController@reportByAgent')->name('agent.orders.report');
    Route::post('/agent-orders/bulk-pay', 'AgentOrderController@bulkPay')->name('agent.orders.bulk_pay');
    Route::get('/agent-orders/export', 'AgentOrderController@export')->name('agent.orders.export');

    // Agent Links - quản lý affiliate links của agents
    Route::get('/agent-links', 'AgentLinkController@index')->name('adminagent.links.index');
    Route::get('/agent-links/create', 'AgentLinkController@create')->name('agent.links.create');
    Route::post('/agent-links', 'AgentLinkController@store')->name('agent.links.store');
    Route::get('/agent-links/{id}/edit', 'AgentLinkController@edit')->name('agent.links.edit');
    Route::patch('/agent-links/{id}', 'AgentLinkController@update')->name('agent.links.update');
    Route::delete('/agent-links/{id}', 'AgentLinkController@destroy')->name('agent.links.destroy');
    Route::post('/agent-links/bulk-delete', 'AgentLinkController@bulkDelete')->name('agent.links.bulk-delete');
    Route::post('/agent-links/export', 'AgentLinkController@export')->name('agent.links.export');
    Route::post('/agent-links/generate/{product_slug}', 'AgentLinkController@generateLink')->name('agent.links.generate');

    // Agent status update - tương tự như affiliate orders
    Route::post('/agent/{id}/update-status', 'AgentController@updateStatus')->name('agent.update-status');
    Route::post('/agents/bulk-action', 'AgentController@bulkAction')->name('agents.bulk-action');

    // Agent commissions - tương tự commission hiện tại
    Route::get('/agent-commissions', 'AgentCommissionController@index')->name('agent.commissions.index');
    Route::get('/agent-commissions/{agent_id}', 'AgentCommissionController@show')->name('agent.commission.detail');

});



Route::group(['prefix' => 'laravel-filemanager', 'middleware' => ['web', 'auth']], function () {
    \UniSharp\LaravelFilemanager\Lfm::routes();
});

// Agent section start
Route::prefix('agent')->group(function () {

    Route::get('/login', [LoginController::class, 'showLoginForm'])
        ->middleware('guest:agent')
        ->name('agent.login.form');
    Route::post('/login', [LoginController::class, 'login'])
        ->middleware('guest:agent')
        ->name('agent.login');
    Route::post('/logout', [LoginController::class, 'logout'])->name('agent.logout');

    // Protected routes
    Route::middleware('auth:agent')->group(function () {
        Route::get('/links', [LinkController::class, 'myLinks'])->name('agent.links.index');
        Route::post('/links/generate/{slug}', [LinkController::class, 'generateLink'])->name('agent.links.generate');
        Route::get('/links/create', [LinkController::class, 'availableProducts'])->name('agent.links.create');
        Route::get('/dashboard', [DashboardAgentController::class, 'index'])->name('agent.dashboard');
        
        // Profile routes
        Route::get('/profile', [DashboardAgentController::class, 'profile'])->name('agent.profile');
        Route::put('/profile', [DashboardAgentController::class, 'updateProfile'])->name('agent.profile.update');

        // Orders routes - DI CHUYỂN VÀO ĐÂY
        Route::prefix('orders')->name('agentorder.')->group(function () {
            Route::get('/', [OrderAgentController::class, 'index'])->name('index');
            Route::get('/{id}', [OrderAgentController::class, 'show'])->name('show');
            Route::get('/export/csv', [OrderAgentController::class, 'export'])->name('export');
            Route::get('/chart/commission', [OrderAgentController::class, 'getCommissionChart'])->name('chart.commission');
            Route::get('/widgets/data', [OrderAgentController::class, 'getWidgetData'])->name('widgets.data');
        });
    });

});


