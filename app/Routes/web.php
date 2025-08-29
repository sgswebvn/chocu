<?php

use App\Controllers\AuthController;
use App\Controllers\ProductController;
use App\Controllers\OrderController;
use App\Controllers\FavoriteController;
use App\Controllers\ReviewController;
use App\Controllers\AdminController;
use App\Controllers\CartController;
use App\Controllers\ChatController;
use App\Controllers\CheckoutController;
use App\Controllers\ContactController;
use App\Controllers\PaymentController;
use App\Controllers\ProfileController;
use App\Controllers\ReportController;
use App\Controllers\SellerController;
use App\Controllers\HomeController;
use App\Models\Notification;
use App\Controllers\NotificationController;
use App\Controllers\Partners\PartnersController;
use App\Controllers\Partners\PMessageController;
use App\Controllers\Partners\PProductController;
use App\Controllers\Partners\PReviewController;
use App\Controllers\Partners\PTransactionController;
use App\Controllers\Partners\POrderController;
use App\Controllers\StoreController;
use App\Controllers\UpgradeController;

// === Trang chính ===
$router->get('/', [HomeController::class, 'index']);

// === Auth ===

$router->get('/register', [AuthController::class, 'register']);
$router->post('/register', [AuthController::class, 'register']);
$router->get('/login', [AuthController::class, 'login']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/google-login', [AuthController::class, 'googleLogin']);
$router->get('/google-callback', [AuthController::class, 'googleLogin']);
$router->get('/logout', [AuthController::class, 'logout']);
$router->get('/forgot-password', [AuthController::class, 'forgotPassword']);
$router->post('/forgot-password', [AuthController::class, 'forgotPassword']);
$router->get('/reset-password', [AuthController::class, 'resetPassword']);
$router->post('/reset-password', [AuthController::class, 'resetPassword']);
$router->get('/profile', [AuthController::class, 'profile']);
$router->get('/profile/change-password', [AuthController::class, 'changePassword']);
$router->post('/profile/change-password', [AuthController::class, 'changePassword']);

// === Trang chat với người dùng ===
$router->get('/chat/{product_id}/{seller_id}', [ChatController::class, 'GetChat']);
$router->post('/chat/save', [ChatController::class, 'save']);
$router->get('/conversations', [ChatController::class, 'GetConversations']);

// === Hồ sơ người dùng ===
$router->get('/profile/orders', [ProfileController::class, 'orders']);
$router->get('/profile/products', [ProfileController::class, 'products']);
$router->get('/profile/account-details', [ProfileController::class, 'accountDetails']);
$router->post('/profile/account-details', [ProfileController::class, 'updateAccountDetails']);

$router->get('/profile/my-orders', [ProfileController::class, 'myOrders']);

// === Sản phẩm ===
$router->get('/products', [ProductController::class, 'index']);
$router->get('/products/search', [ProductController::class, 'index']);
$router->get('/products/create', [ProductController::class, 'create']);
$router->post('/products/create', [ProductController::class, 'create']);
$router->get('/products/edit/{id}', [ProductController::class, 'edit']);
$router->post('/products/edit/{id}', [ProductController::class, 'edit']);
$router->post('/products/delete/{id}', [ProductController::class, 'delete']);
$router->get('/products/{id}', [ProductController::class, 'show']);

// === Yêu thích ===
$router->get('/favorites', [FavoriteController::class, 'index']);
$router->post('/favorites/add', [FavoriteController::class, 'add']);
$router->post('/favorites/remove/{id}', [FavoriteController::class, 'remove']);

// === Tố cáo (Report) ===
$router->get('/reports/create/{id}', [ReportController::class, 'create']);
$router->post('/reports/create/{id}', [ReportController::class, 'create']);

// === Đơn hàng ===
$router->post('/orders/{id}', [OrderController::class, 'cancel']);
$router->post('/orders/cancel/{id}', [OrderController::class, 'cancel']);
$router->get('/orders/track/{id}', [OrderController::class, 'track']);

// === Quản lý đơn hàng nhà bán ===
$router->get('/seller/orders/update/{id}', [OrderController::class, 'updateOrder']);
$router->post('/seller/orders/update/{id}', [OrderController::class, 'updateOrder']);

// === Người bán ===
$router->get('/sellers/{id}', [SellerController::class, 'show']);
$router->get('/sellers/rate/{id}', [SellerController::class, 'rate']);
$router->post('/sellers/rate/{id}', [SellerController::class, 'rate']);

// === Thanh toán ===
$router->get('/checkout', [CheckoutController::class, 'index']);
$router->post('/checkout/process', [CheckoutController::class, 'process']);
$router->get('/checkout/callback', [CheckoutController::class, 'payosCallback']);
$router->get('/checkout/success', [CheckoutController::class, 'success']);
$router->get('/checkout/cancel', [CheckoutController::class, 'cancel']);
$router->get('/order/confirmation/{id}', [CheckoutController::class, 'confirmation']);
$router->get('/orders/pay/{id}', [CheckoutController::class, 'payOrder']);

// Thêm vào giỏ hàng
$router->post('/cart/add', [CartController::class, 'add']);
$router->get('/cart', [CartController::class, 'index']);
$router->post('/cart/remove/{id}', [CartController::class, 'remove']);

// Liên hệ
$router->get('/contact', [ContactController::class, 'index']);
$router->post('/contact', [ContactController::class, 'index']);

// === Quản trị (Admin) ===
$router->get('/admin', [AdminController::class, 'dashboard']);

// -- Quản lý sản phẩm --
$router->get('/admin/products', [AdminController::class, 'products']);
$router->get('/admin/search-products', [AdminController::class, 'searchProducts']);
$router->get('/admin/products/status/{id}/{status}', [AdminController::class, 'updateProductStatus']);
$router->get('/admin/products/view/{id}', [AdminController::class, 'view_product']);

// -- Quản lý danh mục sản phẩm --
$router->get('/admin/categories', [AdminController::class, 'index']);
$router->get('/admin/categories/create', [AdminController::class, 'createCategory']);
$router->post('/admin/categories/create', [AdminController::class, 'createCategory']);
$router->get('/admin/categories/edit/{id}', [AdminController::class, 'editCategory']);
$router->post('/admin/categories/edit/{id}', [AdminController::class, 'editCategory']);
$router->post('/admin/categories/delete/{id}', [AdminController::class, 'deleteCategory']);

// -- Quản lý người dùng --
$router->get('/admin/users', [AdminController::class, 'users']);
$router->get('/admin/search-users', [AdminController::class, 'searchUsers']);
$router->get('/admin/users/activate/(\d+)', function ($id) {
    (new \App\Controllers\AdminController)->toggleUserStatus($id, 'activate');
});
$router->get('/admin/users/deactivate/(\d+)', function ($id) {
    (new \App\Controllers\AdminController)->toggleUserStatus($id, 'deactivate');
});

// -- Quản lý tố cáo --
$router->post('/admin/reports/delete/{id}', [AdminController::class, 'deleteReport']);
$router->get('/admin/reports', [AdminController::class, 'reports']);
$router->get('/admin/users/view/{id}', [AdminController::class, 'view_user']);

// Liên hệ
$router->get('/admin/contacts', [AdminController::class, 'contacts']);

// -- Quản lý thông báo --
$router->post('/notifications/mark-read', [NotificationController::class, 'markRead']);
$router->post('/notifications/mark-all-read', [NotificationController::class, 'markAllRead']);
$router->get('/notifications/unread-count', [NotificationController::class, 'getUnreadCount']);





// -- Đối tác --

// Trang chủ
$router->get('/partners', [PartnersController::class, 'index']);
$router->get('/partners/dashboard', [PartnersController::class, 'dashboard']);


// Quản lý thông tin đối tác
$router->get('/partners/profile', [PartnersController::class, 'profile']);

// Quản lý đơn hàng
$router->get('/partners/orders', [POrderController::class, 'index']);
$router->get('/partners/orders/{id}', [POrderController::class, 'show']);
$router->post('/partners/orders/update/{id}', [POrderController::class, 'update']);

// Quản lý tin nhắn
$router->get('/partners/messages', [PartnersController::class, 'messages']);
$router->get('/partners/messages/{id}', [PartnersController::class, 'messageDetails']);
// Quản lý báo cáo
$router->get('/partners/reports', [PartnersController::class, 'reports']);

// Quản lý thông báo
$router->get('/partners/notifications', [PartnersController::class, 'notifications']);
// Quản lý đánh giá
$router->get('/partners/reviews', [PartnersController::class, 'reviews']);
// Quản lý liên hệ
$router->get('/partners/contact', [PartnersController::class, 'contact']);
// Quản lý thanh toán 
$router->get('/partners/payments', [PartnersController::class, 'payments']);


// === Nâng cấp đối tác ===
$router->get('/upgrade', [UpgradeController::class, 'index']);
$router->post('/upgrade/process', [UpgradeController::class, 'process']);
$router->get('/upgrade/success', [UpgradeController::class, 'success']);
$router->get('/upgrade/cancel', [UpgradeController::class, 'cancel']);

// AUth đối tác
$router->get('/partner-register', [AuthController::class, 'partnerRegister']);
$router->post('/partner-register', [AuthController::class, 'partnerRegister']);


// Quản lý sản phẩm đối tác

$router->get('/partners/product', [PProductController::class, 'index']);
$router->get('/partners/product/create', [PProductController::class, 'create']);
$router->post('/partners/product/store', [PProductController::class, 'store']);
$router->get('/partners/product/edit/{id}', [PProductController::class, 'edit']);
$router->post('/partners/product/update/{id}', [PProductController::class, 'update']);
$router->get('/partners/product/delete/{id}', [PProductController::class, 'delete']);


// Quản lý đánh giá đối tác
$router->get('/partners/review', [PReviewController::class, 'index']);
$router->post('/partners/review/reply/{id}', [PReviewController::class, 'reply']);


// Tin nhắn đối tác
$router->get('/partners/message', [PMessageController::class, 'index']);
$router->get('/partners/message/{productId}/{receiverId}', [PMessageController::class, 'view']);
$router->post('/partners/message/send', [PMessageController::class, 'send']);
// Quản lý giao dịch đối tác
$router->get('/partners/transactions', [PTransactionController::class, 'index']);

// Thông tin cá nhân 

$router->get('/partners/profile', [PartnersController::class, 'personalInfo']);
$router->post('/partners/profile', [PartnersController::class, 'updateProfilePartner']);


$router->get('/store/{userId}', [StoreController::class, 'show']);
$router->post('/store/{shopId}/review', [StoreController::class, 'review']);
$router->post('/store/{shopId}/user-review', [StoreController::class, 'userReview']);
$router->post('/store/{shopId}/review/reply/{reviewId}', [StoreController::class, 'replyReview']);
