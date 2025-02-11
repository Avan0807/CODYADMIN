<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ApiProductController;
use App\Http\Controllers\Api\ApiUsersController;
use App\Http\Controllers\Api\ApiOrderController;
use App\Http\Controllers\Api\ApiBannerController;
use App\Http\Controllers\Api\ApiBrandController;
use App\Http\Controllers\Api\ApiCartController;
use App\Http\Controllers\Api\ApiCategoryController;
use App\Http\Controllers\Api\ApiCouponController;
use App\Http\Controllers\Api\ApiMessageController;
use App\Http\Controllers\Api\ApiNotificationController;
use App\Http\Controllers\Api\ApiPostCategoryController;
use App\Http\Controllers\Api\ApiPostController;
use App\Http\Controllers\Api\ApiPostTagController;
use App\Http\Controllers\Api\ApiProductReviewController;
use App\Http\Controllers\Api\ApiShippingController;
use App\Http\Controllers\Api\ApiAuthController;
use App\Http\Controllers\Api\ApiDoctorController;
use App\Http\Controllers\Api\ApiAuthAdminController;
use App\Http\Controllers\Api\ApiAppointmentController;

// =================== ADMIN LOGIN ===================
Route::post('admin/login', [ApiAuthAdminController::class, 'adminLogin']); // Đăng nhập admin
Route::post('admin/logout', [ApiAuthAdminController::class, 'logout'])->middleware('auth:sanctum'); // Đăng xuất admin

// =================== DOCTOR AUTHENTICATION ===================
Route::prefix('doctor')->group(function () {
    Route::post('/login', [ApiDoctorController::class, 'doctorLogin']); // Bác sĩ đăng nhập

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [ApiDoctorController::class, 'doctorLogout']); // Bác sĩ đăng xuất
        Route::put('/update/{id}', [ApiDoctorController::class, 'update']); // Cập nhật thông tin bác sĩ
    });
});

// =================== ADMIN DOCTOR MANAGEMENT ===================
Route::prefix('admin')->middleware('auth:sanctum')->group(function () {
    Route::post('/create-doctor', [ApiDoctorController::class, 'createDoctor']); // Admin tạo tài khoản bác sĩ
    Route::delete('/delete-doctor/{id}', [ApiDoctorController::class, 'deleteDoctor']); // Admin xóa tài khoản bác sĩ
});

// =================== DOCTOR ROUTES ===================
Route::get('doctors', [ApiDoctorController::class, 'index']); // Lấy danh sách bác sĩ
Route::get('doctors/{id}', [ApiDoctorController::class, 'show']); // Lấy thông tin bác sĩ theo ID

// =================== AUTH ROUTES ===================
Route::post('login', [ApiAuthController::class, 'login']);
Route::post('logout', [ApiAuthController::class, 'logout']);
Route::post('register', [ApiAuthController::class, 'register']);

// =================== PRODUCT ROUTES ===================
Route::get('products', [ApiProductController::class, 'index']);
Route::post('products', [ApiProductController::class, 'store']);
Route::get('products/{id}', [ApiProductController::class, 'show']);
Route::put('products/{id}', [ApiProductController::class, 'update']);
Route::delete('products/{id}', [ApiProductController::class, 'destroy']);

// =================== SHIPPING ROUTES ===================
Route::get('shippings', [ApiShippingController::class, 'index']);
Route::post('shippings', [ApiShippingController::class, 'store']);
Route::get('shippings/{id}', [ApiShippingController::class, 'show']);
Route::put('shippings/{id}', [ApiShippingController::class, 'update']);
Route::delete('shippings/{id}', [ApiShippingController::class, 'destroy']);

// =================== PRODUCT REVIEW ROUTES ===================
Route::get('product-reviews', [ApiProductReviewController::class, 'index']);
Route::post('product-reviews', [ApiProductReviewController::class, 'store']);
Route::get('product-reviews/{id}', [ApiProductReviewController::class, 'show']);
Route::put('product-reviews/{id}', [ApiProductReviewController::class, 'update']);
Route::delete('product-reviews/{id}', [ApiProductReviewController::class, 'destroy']);

// =================== POST ROUTES ===================
Route::get('posts', [ApiPostController::class, 'index']);
Route::post('posts', [ApiPostController::class, 'store']);
Route::get('posts/{id}', [ApiPostController::class, 'show']);
Route::put('posts/{id}', [ApiPostController::class, 'update']);
Route::delete('posts/{id}', [ApiPostController::class, 'destroy']);

// =================== POST CATEGORY ROUTES ===================
Route::get('post-categories', [ApiPostCategoryController::class, 'index']);
Route::post('post-categories', [ApiPostCategoryController::class, 'store']);
Route::get('post-categories/{id}', [ApiPostCategoryController::class, 'show']);
Route::put('post-categories/{id}', [ApiPostCategoryController::class, 'update']);
Route::delete('post-categories/{id}', [ApiPostCategoryController::class, 'destroy']);

// =================== POST TAG ROUTES ===================
Route::get('post-tags', [ApiPostTagController::class, 'index']);
Route::post('post-tags', [ApiPostTagController::class, 'store']);
Route::get('post-tags/{id}', [ApiPostTagController::class, 'show']);
Route::put('post-tags/{id}', [ApiPostTagController::class, 'update']);
Route::delete('post-tags/{id}', [ApiPostTagController::class, 'destroy']);


// =================== CATEGORY ROUTES ===================
Route::get('categories', [ApiCategoryController::class, 'index']);
Route::post('categories', [ApiCategoryController::class, 'store']);
Route::get('categories/{id}', [ApiCategoryController::class, 'show']);
Route::put('categories/{id}', [ApiCategoryController::class, 'update']);
Route::delete('categories/{id}', [ApiCategoryController::class, 'destroy']);

// =================== CART ROUTES ===================
Route::post('add-to-cart', [ApiCartController::class, 'addToCart']);
Route::post('single-add-to-cart', [ApiCartController::class, 'singleAddToCart']);
Route::delete('cart-delete', [ApiCartController::class, 'cartDelete']);
Route::post('cart-update', [ApiCartController::class, 'cartUpdate']);
Route::post('checkout-now/{product_id}', [ApiCartController::class, 'checkoutNow']);

// =================== COUPON ROUTES ===================
Route::get('coupons', [ApiCouponController::class, 'index']);
Route::post('apply-coupon', [ApiCouponController::class, 'couponStore']);

// =================== BRAND ROUTES ===================
Route::get('brands', [ApiBrandController::class, 'index']);
Route::post('brands', [ApiBrandController::class, 'store']);
Route::get('brands/{id}', [ApiBrandController::class, 'show']);
Route::put('brands/{id}', [ApiBrandController::class, 'update']);
Route::delete('brands/{id}', [ApiBrandController::class, 'destroy']);

// =================== BANNER ROUTES ===================
Route::get('banners', [ApiBannerController::class, 'index']);
Route::post('banners', [ApiBannerController::class, 'store']);
Route::get('banners/{id}', [ApiBannerController::class, 'show']);
Route::put('banners/{id}', [ApiBannerController::class, 'update']);
Route::delete('banners/{id}', [ApiBannerController::class, 'destroy']);

// =================== ORDERS ROUTES ===================
Route::get('orders', [ApiOrderController::class, 'index']);
Route::post('orders', [ApiOrderController::class, 'store']);
Route::get('orders/{id}', [ApiOrderController::class, 'show']);
Route::put('orders/{id}', [ApiOrderController::class, 'update']);
Route::delete('orders/{id}', [ApiOrderController::class, 'destroy']);

Route::post('track-order', [ApiOrderController::class, 'productTrackOrder']);
Route::get('order-pdf/{id}', [ApiOrderController::class, 'pdf']);
Route::get('income-chart', [ApiOrderController::class, 'incomeChart']);

// =================== USER ROUTES ===================
Route::get('users', [ApiUsersController::class, 'index']);
Route::post('users', [ApiUsersController::class, 'store']);
Route::get('users/{id}', [ApiUsersController::class, 'show']);
Route::put('users/{id}', [ApiUsersController::class, 'update']);
Route::delete('users/{id}', [ApiUsersController::class, 'destroy']);

// =================== MESSAGE ROUTES ===================
Route::get('messages', [ApiMessageController::class, 'index']);
Route::post('messages', [ApiMessageController::class, 'store']);
Route::get('messages/{id}', [ApiMessageController::class, 'show']);
Route::put('messages/{id}', [ApiMessageController::class, 'update']);
Route::delete('messages/{id}', [ApiMessageController::class, 'destroy']);
Route::get('messages/unread', [ApiMessageController::class, 'messageFive']);

// =================== NOTIFICATION ROUTES ===================
Route::get('notifications', [ApiNotificationController::class, 'index']);
Route::get('notifications/{id}', [ApiNotificationController::class, 'show']);
Route::delete('notifications/{id}', [ApiNotificationController::class, 'delete']);

// =================== APPOINTMENTS ROUTES ===================
Route::prefix('appointments')->group(function () {
    Route::post('/create', [ApiAppointmentController::class, 'createAppointment']);
    Route::get('/all', [ApiAppointmentController::class, 'getAllAppointments']);
    Route::get('/user/{user_id}', [ApiAppointmentController::class, 'getAppointmentsByUser']);
    Route::get('/recent/{user_id}', [ApiAppointmentController::class, 'getRecentAppointments']);
    Route::put('/confirm/{id}', [ApiAppointmentController::class, 'confirmAppointment']);
    Route::put('/complete/{id}', [ApiAppointmentController::class, 'completeAppointment']);
    Route::delete('/cancel/{id}', [ApiAppointmentController::class, 'cancelAppointment']);
    Route::delete('/delete/{id}', [ApiAppointmentController::class, 'deleteAppointment']);
});

