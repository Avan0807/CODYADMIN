<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Appointment;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\GetdoctorsController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\DoctorsController;
use App\Http\Controllers\AppointmentsController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\PostCommentController;
use App\Http\Controllers\MedicalRecordController;
use App\Http\Controllers\TreatmentLogController;
use App\Http\Controllers\Api\ApiNotificationController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Api\ApiProductController;
use App\Http\Controllers\Api\ApiDoctorController;
use App\Http\Controllers\Api\ApiAuthAdminController;
use App\Http\Controllers\Api\ApiAuthController;
use App\Http\Controllers\Api\ApiReviewDoctorController;
use App\Http\Controllers\Api\ApiAffiliateController;
use App\Http\Controllers\Api\ApiOrderController;
use App\Http\Controllers\Api\ApiCartController;
use App\Http\Controllers\Api\ApiDoctorReviewController;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Api\ApiDoctorCommissionController;
use App\Http\Controllers\Api\ApiCompanyNewsController;
use App\Http\Controllers\Api\ApiCampaignNotificationsController;
use App\Http\Controllers\Api\ApiFollowersDoctorController;
use App\Http\Controllers\Api\ApiAffiliateOrdersController;
use App\Http\Controllers\Api\ApiClinicController;
use App\Http\Controllers\Api\ApiMeetingController;
use App\Http\Controllers\Api\ApiShippingController;
use App\Http\Controllers\Api\ApiBannerController;
use App\Http\Controllers\Api\ApiSpecialtiesController;
use App\Http\Controllers\Api\ApiCategoryController;
use App\Http\Controllers\Api\ApiForumThreadController;
use App\Http\Controllers\Api\ApiMobileShippingController;
use App\Services\GHNService;

// ================== GHN SHIPPING APIs ==================

// ✅ Public GHN APIs (không cần auth)
Route::prefix('ghn')->group(function () {
    Route::get('/provinces', [ApiMobileShippingController::class, 'getProvinces']);
    Route::get('/districts', [ApiMobileShippingController::class, 'getDistricts']);
    Route::get('/wards', [ApiMobileShippingController::class, 'getWards']);
});

// ✅ Protected Shipping APIs (cần auth)
Route::middleware('auth:sanctum')->prefix('shipping')->group(function () {
    // Shipping Services & Calculation
    Route::post('/services', [ApiMobileShippingController::class, 'getShippingServices']);
    Route::post('/calculate', [ApiMobileShippingController::class, 'calculateShippingFee']);
    Route::post('/auto-calculate', [ApiOrderController::class, 'autoCalculateShipping']);
    
    // Order Management
    Route::post('/create-order', [ApiMobileShippingController::class, 'createShippingOrder']);
    Route::get('/track', [ApiMobileShippingController::class, 'trackOrder']);
});

// ================== ORDER APIs ==================

// ✅ Protected Order APIs (cần auth)
Route::middleware('auth:sanctum')->prefix('order')->group(function () {
    // Order Management
    Route::post('/store', [ApiOrderController::class, 'store']);
    Route::post('/track-ghn', [ApiOrderController::class, 'trackGHNOrder']);
    Route::get('/status', [ApiOrderController::class, 'getOrdersByStatus']);
});

Route::prefix('forum')->group(function () {

    // Lấy danh sách threads với filter và phân trang
    Route::get('/threads', [ApiForumThreadController::class, 'getThreads']);

    // Lấy threads hot nhất
    Route::get('/threads/hot', [ApiForumThreadController::class, 'getHotThreads']);

    // Tìm kiếm threads
    Route::get('/threads/search', [ApiForumThreadController::class, 'searchThreads']);

    // Lấy threads theo category
    Route::get('/category/{categoryId}/threads', [ApiForumThreadController::class, 'getThreadsByCategory']);

    // Lấy chi tiết thread theo slug
    Route::get('/threads/{slug}', [ApiForumThreadController::class, 'getThreadBySlug']);

});

// Route lấy 6 danh mục, mỗi danh mục có 3 bài viết mới nhất
Route::get('/categories/with-posts', [ApiCategoryController::class, 'getCategoriesWithLatestPosts']);

// Route lấy Dịch vụ y tế (Phương pháp chữa bệnh) - 6 categories con của ID 87
Route::get('/categories/medical-services', [ApiCategoryController::class, 'getMedicalServices']);

// Route lấy category theo slug và các posts liên quan
Route::get('/categories/{slug}', [ApiCategoryController::class, 'getCategoryBySlug']);

// Lấy dữ liệu theo chuyên khoa
Route::get('specialty/{specialtyId}/data', [ApiSpecialtiesController::class, 'getSpecialtyData']);

// Banner
Route::get('banners', [ApiBannerController::class, 'index']);
Route::get('banners/latest', [ApiBannerController::class, 'getLatest']);

// Shipppp
Route::group(['prefix' => 'shipping', 'namespace' => 'API'], function () {
    Route::get('/provinces', [ApiShippingController::class, 'getProvinces']);
    Route::get('/districts/{province_id}', [ApiShippingController::class, 'getDistricts']);
    Route::post('/calculate', [ApiShippingController::class, 'calculateShipping']);
});

// Meettings
Route::get('meetings', [ApiMeetingController::class, 'apiGetAllMeeting']);
Route::middleware('auth:sanctum')->get('doctor/{doctorId}/meetings', [ApiMeetingController::class, 'apiGetDoctorMeetings']);
Route::middleware('auth:sanctum')->post('create-meeting', [ApiMeetingController::class, 'apiCreateMeeting']);

// Các route liên quan đến thông báo chiến dịch cho người dùng đã xác thực (user, doctor)
Route::middleware(['auth:sanctum'])->get('/campaign-notifications/authenticated', [ApiCampaignNotificationsController::class, 'getNotificationsForAuthenticatedUser']);
// Lấy 5 thông báo chiến dịch mới nhất cho người dùng đã xác thực (user, doctor)
Route::middleware(['auth:sanctum'])->get('/campaign-notifications/authenticated/latest', [ApiCampaignNotificationsController::class, 'getLatestFiveForAuthenticatedUser']);
// Lấy chi tiết một thông báo chiến dịch theo ID cho người dùng đã xác thực (user, doctor)
Route::middleware(['auth:sanctum'])->get('/campaign-notifications/{id}', [ApiCampaignNotificationsController::class, 'getNotificationDetail']);

// Các route liên quan đến tin tức công ty (public, không cần xác thực)
// Lấy tất cả tin tức của công ty
Route::get('/company-news', [ApiCompanyNewsController::class, 'getAllNews']);
// Lấy chi tiết tin tức công ty theo ID
Route::get('/company-news/{id}', [ApiCompanyNewsController::class, 'getNewsDetail']);
// Lấy tin tức mới nhất của công ty
Route::get('/company-news/latest', [ApiCompanyNewsController::class, 'getLatestNews']);



// Các route yêu cầu xác thực bằng Sanctum
Route::middleware('auth:sanctum')->group(function () {
    // Lấy thông tin hoa hồng của bác sĩ đã xác thực
    Route::get('/doctor/commissions', [ApiDoctorCommissionController::class, 'getDoctorCommission']);
});

// Route upload file lên S3 (public, không yêu cầu xác thực)
Route::post('/upload', function (Request $request) {
    // Kiểm tra xem có file được upload không
    if (!$request->hasFile('file')) {
        return response()->json(['error' => 'Không có file được upload'], 400);
    }

    $file = $request->file('file');

    // Lưu file vào thư mục 'images' trên S3 mà không đặt ACL (Access Control List)
    $path = Storage::disk('s3')->putFileAs('images', $file, $file->getClientOriginalName());

    // Lấy URL công khai của file đã upload
    $url = Storage::disk('s3')->url($path);

    // Trả về URL file đã upload dưới dạng JSON
    return response()->json(['url' => $url]);
});



// ================== ĐÁNH GIÁ BÁC SĨ ==================

// Các route yêu cầu xác thực để thao tác với đánh giá bác sĩ
Route::middleware('auth:sanctum')->group(function () {
    // Đăng đánh giá cho bác sĩ
    Route::post('doctor-reviews', [ApiDoctorReviewController::class, 'store']);
    // Xóa đánh giá của bác sĩ theo ID
    Route::delete('doctor-reviews/{id}', [ApiDoctorReviewController::class, 'destroy']);
});

// Lấy danh sách đánh giá của một bác sĩ (public, không yêu cầu xác thực)
Route::get('doctor-reviews/{doctor_id}', [ApiDoctorReviewController::class, 'index']);


// ================== ADMIN - QUẢN LÝ ĐƠN HÀNG ==================

// Route yêu cầu xác thực để admin cập nhật trạng thái đơn hàng
Route::middleware('auth:sanctum')->group(function () {
    // Cập nhật trạng thái đơn hàng theo ID
    Route::post('/admin/update-order-status/{order_id}', [ApiOrderController::class, 'updateOrderStatus']);
});



// ✅ Các route yêu cầu xác thực (middleware 'auth:sanctum')
Route::middleware('auth:sanctum')->group(function () {

    // 🔍 API lấy danh sách sản phẩm tiếp thị của bác sĩ đã đăng nhập
    Route::get('/doctor/affiliate-products', [ApiAffiliateController::class, 'getAffiliateProducts']);

    // 🔗 API tạo link affiliate cho sản phẩm (dành cho bác sĩ đã đăng nhập)
    Route::get('/generate-link/{product_slug}', [ApiAffiliateController::class, 'generateLink']);

    // 📦 API lấy danh sách đơn hàng của bác sĩ (theo ID bác sĩ)
    Route::get('/doctor/{doctor_id}/orders', [ApiDoctorController::class, 'orders']);

    // 💸 API yêu cầu rút tiền hoa hồng cho bác sĩ (đã đăng nhập)
    Route::post('/doctor/request-payout', [ApiDoctorController::class, 'requestPayout']);

    // 🏥 API lưu thông tin bác sĩ vào đơn hàng (sau khi đặt hàng)
    Route::post('/order/storeDoctor', [ApiOrderController::class, 'storeDoctor']);
});

// ✅ API ghi nhận click vào link affiliate (không yêu cầu đăng nhập)
Route::get('/affiliate/click/{hash_ref}', [ApiAffiliateController::class, 'trackClick']);

// ✅ API track affiliate khi người dùng vào chi tiết sản phẩm (không yêu cầu đăng nhập)
Route::get('/product-detail/{product_slug}', [ApiProductController::class, 'trackAffiliate']);



// AUTHENTICATION ROUTES
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::post('/register', [RegisterController::class, 'apiRegister']);


// =================== ADMIN cho rut tien  ===================
Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::post('/admin/approve-payout/{id}', [ApiAuthAdminController::class, 'approvePayout']);
    Route::post('/admin/reject-payout/{id}', [ApiAuthAdminController::class, 'rejectPayout']);
});


// =================== ADMIN DOCTOR MANAGEMENT ===================
Route::prefix('admin')->middleware('auth:sanctum')->group(function () {
    Route::post('/create-doctor', [ApiDoctorController::class, 'createDoctor']);
    Route::delete('/delete-doctor/{id}', [ApiDoctorController::class, 'deleteDoctor']);

});

// Login
Route::post('/login', [LoginController::class, 'apiLogin']);
// Doctor login
Route::post('/login/doctor', [LoginController::class, 'apiDoctorLogin']);

// =================== DOCTOR AUTHENTICATION ===================
Route::prefix('doctor')->group(function () {

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [ApiDoctorController::class, 'doctorLogout']);
    });
});

// ================== DOCTOR INCOME CHART (ADMIN) ==================

// API thống kê thu nhập bác sĩ theo tháng (chỉ admin hoặc xác thực)
Route::middleware('auth:sanctum')->get('/doctor-income-chart', [ApiDoctorController::class, 'apiDoctorIncomeChart']);
// API thống kê lịch hẹn của bác sĩ theo tháng
Route::middleware('auth:sanctum')->get('/doctor-appointment-chart', [ApiDoctorController::class, 'apiDoctorAppointmentChart']);
// API thống kê affiliate của bác sĩ theo tháng
Route::middleware('auth:sanctum')->get('/doctor-affiliate-chart', [ApiDoctorController::class, 'apiDoctorAffiliateChart']);

// =================== DOCTOR review ===================
Route::get('doctor-reviews/{doctor_id}', [ApiReviewDoctorController::class, 'index']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('doctor-reviews', [ApiReviewDoctorController::class, 'store']);
});

// lấy danh sách phòng khám
Route::get('/clinics', [ApiClinicController::class, 'index']);

// logout users
Route::middleware('auth:sanctum')->post('logout', [ApiAuthController::class, 'logout']);

// admin Logout
Route::post('admin/logout', [ApiAuthAdminController::class, 'logout'])->middleware('auth:sanctum');


// ================== USERS ROUTES ==================

// Lấy thông tin người dùng theo ID (yêu cầu xác thực bằng Sanctum)
Route::middleware('auth:sanctum')->get('/user/{id}', [UsersController::class, 'apiGetUserById']);

// Cập nhật thông tin User
Route::middleware('auth:sanctum')->put('/user/update', [UsersController::class, 'apiUpdateUser']);


// Lấy danh sách thông báo của User
Route::middleware('auth:sanctum')->get('/user/{userID}/notifications', [UsersController::class, 'getNotifications']);
// Đánh dấu thông báo đã đọc
Route::middleware('auth:sanctum')->post('/user/notifications/{notificationID}/read', [UsersController::class, 'markNotificationAsRead']);
// Lấy danh sách thông báo chưa đọc
Route::middleware('auth:sanctum')->get('/user/{userID}/notifications/unread', [UsersController::class, 'getUnreadNotifications']);
// Xóa một thông báo
Route::middleware('auth:sanctum')->delete('/user/notifications/{notificationID}', [UsersController::class, 'deleteNotification']);



// ================== DOCTORS ROUTES ==================

// ================== LẤY DANH SÁCH BÁC SĨ ==================

// Lấy danh sách bác sĩ (yêu cầu xác thực)
Route::get('/doctors', [GetdoctorsController::class, 'apiHome']);

// Lấy toàn bộ danh sách bác sĩ (public, không yêu cầu xác thực)
Route::get('/alldoctors', [DoctorsController::class, 'apiGetAllDoctors']);

// Lấy thông tin chi tiết của một bác sĩ theo ID (public, không yêu cầu xác thực)
Route::get('/doctors/{doctorID}', [DoctorsController::class, 'apiGetDoctorsByDoctorId']);

// Cập nhật thông tin bác sĩ
Route::middleware('auth:sanctum')->post('/doctor/update', [DoctorsController::class, 'updateApi']);

// Lấy danh sách đơn hàng tiếp thị của từng bác sĩ
Route::middleware('auth:sanctum')->get('/doctor/affiliate-orders', [ApiAffiliateOrdersController::class, 'index']);


// Tất cả wishlist bác sĩ
Route::middleware('auth:sanctum')->get('/wishlist', [ApiFollowersDoctorController::class, 'index']);
// Thêm bác sĩ vào wishlist (yêu cầu xác thực)
Route::middleware('auth:sanctum')->post('/addwishlist/{doctor_id}', [ApiFollowersDoctorController::class, 'store']);

// Xóa bác sĩ khỏi wishlist (yêu cầu xác thực)
Route::middleware('auth:sanctum')->delete('/delwishlist/{doctor_id}', [ApiFollowersDoctorController::class, 'destroy']);


// ================== THÔNG TIN BỆNH NHÂN ==================

// Lấy thông tin bệnh nhân theo ID (yêu cầu xác thực)
Route::middleware('auth:sanctum')->get('/patient-info/{id}', [DoctorsController::class, 'apiGetPatientInfo']);


// ================== THÔNG BÁO CHO BÁC SĨ ==================

// Lấy tất cả thông báo của bác sĩ (yêu cầu xác thực)
Route::middleware('auth:sanctum')->get('/doctor/{doctorID}/notifications', [DoctorsController::class, 'getNotifications']);

// Đánh dấu thông báo là đã đọc (yêu cầu xác thực)
Route::middleware('auth:sanctum')->post('/doctor/notifications/{notificationID}/read', [DoctorsController::class, 'markNotificationAsRead']);

// Lấy thông báo chưa đọc của bác sĩ (yêu cầu xác thực)
Route::middleware('auth:sanctum')->get('/doctor/{doctorID}/notifications/unread', [DoctorsController::class, 'getUnreadNotifications']);

// Xóa thông báo của bác sĩ theo ID (yêu cầu xác thực)
Route::middleware('auth:sanctum')->delete('/doctor/notifications/{notificationID}/delete', [DoctorsController::class, 'deleteNotification']);




// ================== PRODUCT ROUTES ==================

// Lấy tất cả sản phẩm (public)
Route::get('/products', [ProductController::class, 'apiGetAllProducts']);

// Lấy chi tiết sản phẩm theo ID (public)
Route::get('/products/{slug}', [ProductController::class, 'apiGetProductBySlug']);

// Thêm sản phẩm mới (nên thêm xác thực để tránh lạm dụng)
Route::middleware('auth:sanctum')->post('/productsadd', [ApiProductController::class, 'store']);

// Tìm sản phẩm theo slug (public)
Route::get('/products/slug/{slug}', [ApiProductController::class, 'findBySlug']);


// ================== APPOINTMENT ROUTES ==================

// Lấy tất cả lịch hẹn (Chỉ admin hoặc bác sĩ có thể xem)
Route::middleware('auth:sanctum')->get('/appointments', [AppointmentsController::class, 'ApiGetAllAppointments']);

// Lấy lịch hẹn theo user đang đăng nhập
Route::middleware('auth:sanctum')->get('/appointments/user', [AppointmentsController::class, 'apiGetAppointmentsByUser']);

// Lấy các lịch hẹn sắp tới của user
Route::middleware('auth:sanctum')->get('/appointments/{userID}/upcoming', [AppointmentsController::class, 'apiGetCurrentAppointments']);

// Lấy thông tin chi tiết của lịch hẹn
Route::middleware('auth:sanctum')->get('/appointment-info/{appointmentID}', [AppointmentsController::class, 'apiGetAppointmentInfo']);


// ================== APPOINTMENT BOOKING ==================

// Tạo lịch hẹn mới (chỉ user đã xác thực mới được phép tạo)
Route::middleware('auth:sanctum')->post('/create/appointments', [AppointmentsController::class, 'apiCreateAppointment']);

// Xác nhận lịch hẹn (chỉ bác sĩ xác thực mới được phép xác nhận)
Route::middleware('auth:sanctum')->put('/appointments/{appointmentID}/confirm', [AppointmentsController::class, 'apiConfirmAppointment']);

// Đánh dấu lịch hẹn đã hoàn thành (chỉ bác sĩ xác thực mới được phép hoàn thành)
Route::middleware('auth:sanctum')->put('/appointments/{appointmentID}/complete', [AppointmentsController::class, 'apiCompleteAppointment']);

// Hủy lịch hẹn (chỉ user hoặc bác sĩ liên quan mới được phép hủy)
Route::middleware('auth:sanctum')->put('/appointments/{appointmentID}/cancel', [AppointmentsController::class, 'apiCancelAppointment']);



// ================== DOCTOR'S APPOINTMENTS ==================

// Lấy tất cả lịch hẹn của bác sĩ (yêu cầu xác thực)
Route::middleware('auth:sanctum')->get('/appointments/doctor/{doctorID}/all', [AppointmentsController::class, 'apiGetAllAppointmentsByDoctor']);

// Lấy các lịch hẹn gần đây của bác sĩ (yêu cầu xác thực)
Route::middleware('auth:sanctum')->get('/appointments/doctor/recent', [AppointmentsController::class, 'apiGetRecentAppointments']);

// Bác sĩ từ chối lịch hẹn (yêu cầu xác thực)
Route::middleware('auth:sanctum')->delete('/appointments/doctor/{appointmentID}/reject', [AppointmentsController::class, 'apiDeleteAppointment']);


// ================== PATIENTS ROUTES ==================

// Lấy danh sách tất cả bệnh nhân của bác sĩ (yêu cầu xác thực)
Route::middleware('auth:sanctum')->get('/patients/doctor/all', [AppointmentsController::class, 'getAllPatientsForDoctor']);





// ================== CART ROUTES ==================

// Lấy giỏ hàng của người dùng (public, nên thêm xác thực để bảo mật)
Route::middleware('auth:sanctum')->get('/cart/{userID}', [CartController::class, 'apiGetUserCart']);

// Thêm vào giỏ hàng của người dùng (public, nên thêm xác thực để bảo mật)
Route::middleware('auth:sanctum')->post('/addcart/{userID}/{productId}', [CartController::class, 'apiAddProductToCart']);

// Xóa sản phẩm khỏi giỏ hàng theo userID và productID (public, nên thêm xác thực)
Route::middleware('auth:sanctum')->delete('/delcart/{userId}/{productId}', [CartController::class, 'apiRemoveFromCartByUser']);

// Cập nhật số lượng sản phẩm trong giỏ hàng (public, nên thêm xác thực)
Route::middleware('auth:sanctum')->put('/updatecart/{userId}/{productId}', [CartController::class, 'apiUpdateUserCartQuantity']);

// Mua hàng trực tiếp
Route::middleware('auth:sanctum')->post('/cart/checkout-now/{slug}', [ApiCartController::class, 'checkoutNow']);


// ================== POST ROUTES ==================

// Tạo bài viết mới (yêu cầu xác thực)
Route::middleware('auth:sanctum')->post('/posts/create', [PostController::class, 'apiCreatePost']);

// Lấy tất cả bài viết (public)
Route::get('/posts', [PostController::class, 'apiGetAllPosts']);

// Lấy bài viết theo slug (public)
Route::get('/posts/{slug}', [PostController::class, 'apiGetPostBySlug']);


// ================== POST COMMENTS ROUTES ==================

// Lấy tất cả bình luận của một bài viết (public)
Route::get('/comments/{postId}', [PostCommentController::class, 'getCommentsByPostId']);

// Tạo bình luận cho bài viết (yêu cầu xác thực)
Route::middleware('auth:sanctum')->post('/comments/post_comments', [PostCommentController::class, 'apiCreateComment']);

// Lấy chi tiết một bình luận theo ID (public)
Route::get('/posts/{postId}/comments/{commentId}', [PostCommentController::class, 'apiGetCommentById']);

// Cập nhật nội dung bình luận (yêu cầu xác thực)
Route::middleware('auth:sanctum')->put('/posts/{postId}/comments/{commentId}', [PostCommentController::class, 'apiUpdateComment']);

// Trả lời bình luận (yêu cầu xác thực)
Route::middleware('auth:sanctum')->post('/comments/reply', [PostCommentController::class, 'apiReplyComment']);


// ================== ORDER ROUTES ==================

// Lấy tất cả đơn hàng của người dùng (yêu cầu xác thực)
Route::middleware(['auth:sanctum'])->get('/orders', [OrderController::class, 'apiGetUserOrders']);

// Tạo đơn hàng mới (yêu cầu xác thực)
Route::middleware(['auth:sanctum'])->post('/orders/create', [OrderController::class, 'apiCreateOrder']);

// Lấy trạng thái của một đơn hàng cụ thể (yêu cầu xác thực)
Route::middleware(['auth:sanctum'])->get('/orders/{order_id}/status', [OrderController::class, 'apiGetOrderStatus']);

// Lấy trạng thái của tất cả đơn hàng của người dùng (yêu cầu xác thực)
Route::middleware(['auth:sanctum'])->get('/orders/status', [OrderController::class, 'apiGetUserOrdersStatus']);


// ================== MEDICAL RECORD ROUTES ==================

// Lấy hồ sơ bệnh án theo ID (yêu cầu xác thực)
Route::middleware('auth:sanctum')->get('/medical-records/{id}', [MedicalRecordController::class, 'apiGetMedicalRecordById']);

// Lấy tất cả hồ sơ bệnh án của một người dùng (yêu cầu xác thực)
Route::middleware('auth:sanctum')->get('/medical-records/user/{userId}', [MedicalRecordController::class, 'apiGetAllMedicalRecordsByUser']);

// Tạo hồ sơ bệnh án mới (yêu cầu xác thực)
Route::middleware('auth:sanctum')->post('/medical-records/create', [MedicalRecordController::class, 'apiCreateMedicalRecord']);

// Xóa hồ sơ bệnh án theo ID (yêu cầu xác thực)
Route::middleware('auth:sanctum')->delete('/medical-records/delete/{id}', [MedicalRecordController::class, 'apiDeleteMedicalRecord']);


// ================== TREATMENT ROUTES ==================

Route::middleware('auth:sanctum')->group(function () {
    // Lấy tất cả log điều trị của một hồ sơ bệnh án (yêu cầu xác thực)
    Route::get('/treatment-logs/alltreatment/{medical_record_id}', [TreatmentLogController::class, 'apiGetTreatmentLogsByMedicalRecord']);

    // Lấy log điều trị theo ID (yêu cầu xác thực)
    Route::get('/treatment-logs/{id}', [TreatmentLogController::class, 'apiGetTreatmentLogById']);

    // Tạo log điều trị mới cho một hồ sơ bệnh án (yêu cầu xác thực)
    Route::post('/treatment-logs/create/{medical_record_id}', [TreatmentLogController::class, 'apiCreateTreatmentLog']);

    // Xóa log điều trị theo ID (yêu cầu xác thực)
    Route::delete('/treatment-logs/{id}', [TreatmentLogController::class, 'apiDeleteTreatmentLog']);
});



// =================== NOTIFICATION ROUTES ===================

// Các route liên quan đến thông báo, yêu cầu xác thực bằng Sanctum
Route::middleware('auth:sanctum')->group(function () {

    // Lấy tất cả thông báo của người dùng
    Route::get('/notifications', [ApiNotificationController::class, 'index']);

    // Lấy tất cả thông báo chưa đọc của người dùng
    Route::get('/notifications/unread', [ApiNotificationController::class, 'unread']);

    // Đánh dấu một thông báo là đã đọc theo ID
    Route::post('/notifications/read/{id}', [ApiNotificationController::class, 'markAsRead']);

    // Đánh dấu tất cả thông báo là đã đọc
    Route::post('/notifications/read-all', [ApiNotificationController::class, 'markAllAsRead']);

    // Xóa một thông báo theo ID
    Route::delete('/notifications/delete/{id}', [ApiNotificationController::class, 'delete']);

    // Xóa tất cả thông báo của người dùng
    Route::delete('/notifications/delete-all', [ApiNotificationController::class, 'deleteAll']);
});


