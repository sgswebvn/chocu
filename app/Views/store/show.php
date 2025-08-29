<?php

use App\Helpers\Session;
use App\Models\User;
use App\Models\Partners\Review;

Session::start();

// Xác định đường dẫn dựa trên trạng thái đăng nhập
$userLink = Session::get('user') ? '/profile' : '/login';
$currentUserId = Session::get('user')['id'] ?? null;

// Lấy thông tin shop/người dùng
$userModel = new User();
$shop = $userModel->findById($shopId); // $shopId được truyền từ controller
if (!$shop) {
    error_log("Shop not found for ID: $shopId");
    Session::set('error', 'Không tìm thấy người dùng hoặc gian hàng!');
    header('Location: /');
    exit;
}

// Lấy danh sách sản phẩm (chỉ cho shop)
$products = $shop['is_partner_paid'] == 1 ? $userModel->getProductsByUser($shopId) : [];
error_log("Loaded " . count($products) . " products for shop ID: $shopId");

// Debug dữ liệu sản phẩm
if ($shop['is_partner_paid'] == 1) {
    error_log("Products data for shop ID: $shopId: " . json_encode($products));
}

// Lấy đánh giá của shop (chỉ cho shop)
$reviewModel = new Review();
$shopReviews = $shop['is_partner_paid'] == 1 ? $reviewModel->findByUser($shopId) : [];
$totalShopReviews = $shop['is_partner_paid'] == 1 ? $reviewModel->countReviews($shopId) : 0;
$averageShopRating = $totalShopReviews > 0 ? array_sum(array_column($shopReviews, 'rating')) / $totalShopReviews : 0;

// Lấy đánh giá của người dùng (chỉ cho người dùng không phải shop)
$userReviews = $shop['is_partner_paid'] == 0 ? $reviewModel->findUserReviews($shopId) : [];
$totalUserReviews = count($userReviews);
$averageUserRating = $totalUserReviews > 0 ? array_sum(array_column($userReviews, 'rating')) / $totalUserReviews : 0;

// Lấy danh mục sản phẩm (giả sử có phương thức getAll trong Category model)
$categoryModel = new \App\Models\Category();
$categories = $shop['is_partner_paid'] == 1 ? $categoryModel->getAll() : [];
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $shop['is_partner_paid'] == 1 ? 'Gian hàng' : 'Hồ sơ'; ?> - <?php echo htmlspecialchars($shop['username'] ?? 'Không xác định'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/custom.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f9fafb;
        }

        .sidebar {
            transition: all 0.3s ease;
        }

        .sidebar-hidden {
            transform: translateX(-100%);
        }

        .shop-card,
        .product-card,
        .review-form {
            transition: all 0.2s ease;
            border-radius: 8px;
        }

        .shop-card:hover,
        .product-card:hover,
        .review-form:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .star-rating .bi-star-fill {
            color: #f59e0b;
        }

        .product-card img {
            width: 100%;
            height: 180px;
            object-fit: cover;
            border-radius: 8px 8px 0 0;
        }

        .btn-primary {
            background-color: #2563eb;
            color: white;
            padding: 8px 16px;
            border-radius: 6px;
            transition: background-color 0.2s ease;
        }

        .btn-primary:hover {
            background-color: #1e40af;
        }

        .btn-secondary {
            background-color: #e5e7eb;
            color: #374151;
            padding: 8px 16px;
            border-radius: 6px;
            transition: background-color 0.2s ease;
        }

        .btn-secondary:hover {
            background-color: #d1d5db;
        }

        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                top: 0;
                left: 0;
                height: 100%;
                width: 250px;
                background-color: white;
                z-index: 50;
                padding: 16px;
                box-shadow: 2px 0 8px rgba(0, 0, 0, 0.1);
            }

            .sidebar-hidden {
                display: none;
            }

            .product-card img {
                height: 140px;
            }
        }
    </style>
</head>

<body>
    <?php require_once __DIR__ . '/../layouts/header.php'; ?>
    <?php require_once __DIR__ . '/../products/linkcss.php'; ?>

    <main class="container mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <!-- User/Shop Info Section -->
        <section class="mb-8">
            <div class="bg-white rounded-lg shadow p-6 shop-card">
                <div class="flex flex-col sm:flex-row items-center gap-4">
                    <img src="<?php echo htmlspecialchars($shop['images'] ? '/Uploads/partners/' . $shop['images'] : '/assets/images/user/avatar-default.jpg') . '?t=' . time(); ?>"
                        alt="Avatar" class="w-20 h-20 rounded-full object-cover border border-gray-200">
                    <div class="flex-1 text-center sm:text-left">
                        <h1 class="text-2xl font-semibold text-gray-800">
                            <?php echo htmlspecialchars($shop['username'] ?? 'Không xác định'); ?>
                            <?php if ($shop['is_partner_paid'] == 1): ?>
                                <span class="inline-block bg-green-100 text-green-600 text-xs font-medium px-2.5 py-0.5 rounded ml-2">Shop Chính Hãng</span>
                            <?php endif; ?>
                        </h1>
                        <div class="flex flex-col sm:flex-row gap-4 mt-2 text-gray-600 text-sm">
                            <?php if ($shop['is_partner_paid'] == 1): ?>
                                <p><i class="bi bi-star-fill mr-1"></i>Đánh giá shop: <?php echo number_format($averageShopRating, 1); ?>/5 (<?php echo $totalShopReviews; ?> đánh giá)</p>
                                <p><i class="bi bi-box-seam mr-1"></i>Sản phẩm: <?php echo count($products); ?></p>
                            <?php else: ?>
                                <p><i class="bi bi-star-fill mr-1"></i>Đánh giá người dùng: <?php echo number_format($averageUserRating, 1); ?>/5 (<?php echo $totalUserReviews; ?> đánh giá)</p>
                            <?php endif; ?>
                        </div>
                        <div class="mt-4 flex flex-col sm:flex-row gap-3 justify-center sm:justify-start">
                            <?php if ($currentUserId && $shop['id'] == $currentUserId && $shop['is_partner_paid'] == 1): ?>
                                <a href="/partners/product/create" class="btn-primary inline-flex items-center font-medium">
                                    <i class="bi bi-plus-circle mr-2"></i>Thêm sản phẩm
                                </a>
                            <?php endif; ?>
                            <a href="/store/<?php echo $shop['id']; ?>" class="btn-secondary inline-flex items-center font-medium">
                                <i class="bi bi-shop mr-2"></i>Xem <?php echo $shop['is_partner_paid'] == 1 ? 'gian hàng' : 'hồ sơ'; ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Products Section (Chỉ cho shop) -->
        <?php if ($shop['is_partner_paid'] == 1): ?>
            <section class="mb-8">
                <div class="flex flex-col lg:flex-row gap-6">
                    <!-- Sidebar Filters -->
                    <div class="lg:w-1/4 bg-white rounded-lg shadow p-4 sidebar" id="sidebar">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-gray-800">Bộ lọc sản phẩm</h3>
                            <button class="lg:hidden text-gray-600" id="toggle-sidebar">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                        <form id="product-filter-form">
                            <div class="mb-4">
                                <label class="block text-gray-700 text-sm font-medium mb-2">Danh mục</label>
                                <select name="category_id" class="w-full border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-blue-500">
                                    <option value="">Tất cả danh mục</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo htmlspecialchars($category['id']); ?>">
                                            <?php echo htmlspecialchars($category['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-4">
                                <label class="block text-gray-700 text-sm font-medium mb-2">Khoảng giá</label>
                                <input type="number" name="min_price" placeholder="Giá tối thiểu" class="w-full border-gray-300 rounded-lg p-2 mb-2 focus:ring-2 focus:ring-blue-500">
                                <input type="number" name="max_price" placeholder="Giá tối đa" class="w-full border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div class="mb-4">
                                <label class="block text-gray-700 text-sm font-medium mb-2">Sắp xếp</label>
                                <select name="sort" class="w-full border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-blue-500">
                                    <option value="">Mặc định</option>
                                    <option value="price_asc">Giá: Thấp đến cao</option>
                                    <option value="price_desc">Giá: Cao đến thấp</option>
                                    <option value="views_desc">Lượt xem: Cao đến thấp</option>
                                </select>
                            </div>
                            <button type="submit" class="btn-primary w-full font-medium">Áp dụng bộ lọc</button>
                        </form>
                    </div>

                    <!-- Products List -->
                    <div class="lg:w-3/4">
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="text-xl font-semibold text-gray-800">Sản phẩm của shop</h2>
                            <button class="lg:hidden btn-primary font-medium" id="show-sidebar">
                                <i class="bi bi-filter mr-2"></i>Lọc sản phẩm
                            </button>
                        </div>
                        <div class="bg-white rounded-lg shadow p-6">
                            <?php if (empty($products)): ?>
                                <p class="text-gray-600 text-center">Shop hiện chưa có sản phẩm nào.</p>
                                <?php error_log("No products displayed for shop ID: $shopId"); ?>
                            <?php else: ?>
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6" id="product-list">
                                    <?php foreach ($products as $product): ?>
                                        <?php error_log("Rendering product ID: " . ($product['id'] ?? 'unknown') . ", Title: " . ($product['title'] ?? 'missing')); ?>
                                        <div class="bg-white rounded-lg shadow product-card">
                                            <a href="/products/<?php echo htmlspecialchars($product['id'] ?? ''); ?>">
                                                <img src="<?php echo htmlspecialchars('/Uploads/partners/' . ($product['image'] ?? 'default-product.jpg')); ?>"
                                                    alt="<?php echo htmlspecialchars($product['title'] ?? 'Sản phẩm'); ?>" class="w-full h-48 object-cover rounded-t-lg">
                                                <div class="p-4">
                                                    <h3 class="text-base font-medium text-gray-800 truncate"><?php echo htmlspecialchars($product['title'] ?? 'Không có tiêu đề'); ?></h3>
                                                    <p class="text-red-600 font-semibold mt-1"><?php echo number_format($product['price'] ?? 0, 0, ',', '.'); ?> VNĐ</p>
                                                    <p class="text-sm text-gray-600 mt-1"><i class="bi bi-eye mr-1"></i>Lượt xem: <?php echo $product['views'] ?? 0; ?></p>
                                                </div>
                                            </a>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <!-- Shop Reviews Section (Chỉ cho shop) -->
        <?php if ($shop['is_partner_paid'] == 1): ?>
            <section class="mb-8">
                <div class="flex justify-between items-center mb-4 pl-8">
                    <h2 class="text-xl font-semibold text-gray-800">Đánh giá shop</h2>
                    <div>
                        <select id="review-filter" class="border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-blue-500">
                            <option value="">Tất cả đánh giá</option>
                            <option value="5">5 sao</option>
                            <option value="4">4 sao</option>
                            <option value="3">3 sao</option>
                            <option value="2">2 sao</option>
                            <option value="1">1 sao</option>
                        </select>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow p-6 ">
                    <!-- Review Form (Chỉ cho người dùng không phải shop) -->
                    <?php if ($currentUserId && $shop['id'] != $currentUserId && Session::get('user')): ?>
                        <div class="review-form mb-6 p-4 bg-gray-50 rounded-lg">
                            <h3 class="text-base font-semibold text-gray-800 mb-3">Gửi đánh giá shop</h3>
                            <form action="/store/<?php echo $shop['id']; ?>/review" method="POST">
                                <div class="mb-4">
                                    <label class="block text-gray-700 text-sm font-medium mb-1">Điểm đánh giá</label>
                                    <select name="rating" class="w-full border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-blue-500">
                                        <option value="5">5 sao</option>
                                        <option value="4">4 sao</option>
                                        <option value="3">3 sao</option>
                                        <option value="2">2 sao</option>
                                        <option value="1">1 sao</option>
                                    </select>
                                </div>
                                <div class="mb-4">
                                    <label class="block text-gray-700 text-sm font-medium mb-1">Nhận xét</label>
                                    <textarea name="comment" rows="3" class="w-full border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-blue-500" placeholder="Viết nhận xét..." required></textarea>
                                </div>
                                <button type="submit" class="btn-primary font-medium">Gửi đánh giá</button>
                            </form>
                        </div>
                    <?php endif; ?>

                    <!-- Shop Reviews List -->
                    <div id="review-list">
                        <?php if (empty($shopReviews)): ?>
                            <p class="text-gray-600 text-center">Chưa có đánh giá nào cho shop này.</p>
                        <?php else: ?>
                            <div class="space-y-4 max-h-96 overflow-y-auto">
                                <?php foreach ($shopReviews as $review): ?>
                                    <div class="border-b border-gray-200 pb-4 review-item" data-rating="<?php echo $review['rating']; ?>">
                                        <div class="flex justify-between items-center">
                                            <div class="flex items-center gap-2">
                                                <span class="font-medium text-gray-800"><?php echo htmlspecialchars($review['username'] ?? 'Không xác định'); ?></span>
                                                <div class="star-rating">
                                                    <?php for ($i = 0; $i < 5; $i++): ?>
                                                        <i class="bi <?php echo $i < $review['rating'] ? 'bi-star-fill' : 'bi-star'; ?> text-yellow-400"></i>
                                                    <?php endfor; ?>
                                                </div>
                                            </div>
                                            <small class="text-gray-500"><?php echo date('d/m/Y H:i', strtotime($review['created_at'])); ?></small>
                                        </div>
                                        <p class="text-gray-700 mt-2"><?php echo htmlspecialchars($review['comment']); ?></p>
                                        <?php if ($review['reply']): ?>
                                            <p class="text-gray-600 mt-2"><strong>Phản hồi:</strong> <?php echo htmlspecialchars($review['reply']); ?></p>
                                        <?php endif; ?>
                                        <?php if ($shop['id'] == $currentUserId && !$review['reply'] && $shop['is_partner_paid'] == 1): ?>
                                            <form action="/store/<?php echo $shop['id']; ?>/review/reply/<?php echo $review['id']; ?>" method="POST" class="mt-3">
                                                <textarea name="reply" rows="2" class="w-full border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-blue-500" placeholder="Phản hồi đánh giá..." required></textarea>
                                                <button type="submit" class="btn-primary font-medium mt-2">Gửi phản hồi</button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <!-- User Reviews Section (Chỉ cho người dùng không phải shop) -->
        <?php if ($shop['is_partner_paid'] == 0): ?>
            <section class="mb-8">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-semibold text-gray-800">Đánh giá người dùng</h2>
                    <div>
                        <select id="user-review-filter" class="border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-blue-500">
                            <option value="">Tất cả đánh giá</option>
                            <option value="5">5 sao</option>
                            <option value="4">4 sao</option>
                            <option value="3">3 sao</option>
                            <option value="2">2 sao</option>
                            <option value="1">1 sao</option>
                        </select>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <!-- User Review Form (Chỉ cho người dùng không phải shop) -->
                    <?php if ($currentUserId && $shop['id'] != $currentUserId && Session::get('user') && Session::get('user')['is_partner_paid'] == 0): ?>
                        <div class="review-form mb-6 p-4 bg-gray-50 rounded-lg">
                            <h3 class="text-base font-semibold text-gray-800 mb-3">Gửi đánh giá người dùng</h3>
                            <form action="/store/<?php echo $shop['id']; ?>/user-review" method="POST">
                                <div class="mb-4">
                                    <label class="block text-gray-700 text-sm font-medium mb-1">Điểm đánh giá</label>
                                    <select name="rating" class="w-full border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-blue-500">
                                        <option value="5">5 sao</option>
                                        <option value="4">4 sao</option>
                                        <option value="3">3 sao</option>
                                        <option value="2">2 sao</option>
                                        <option value="1">1 sao</option>
                                    </select>
                                </div>
                                <div class="mb-4">
                                    <label class="block text-gray-700 text-sm font-medium mb-1">Nhận xét</label>
                                    <textarea name="comment" rows="3" class="w-full border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-blue-500" placeholder="Viết nhận xét..." required></textarea>
                                </div>
                                <button type="submit" class="btn-primary font-medium">Gửi đánh giá</button>
                            </form>
                        </div>
                    <?php endif; ?>

                    <!-- User Reviews List -->
                    <div id="user-review-list">
                        <?php if (empty($userReviews)): ?>
                            <p class="text-gray-600 text-center">Chưa có đánh giá nào cho người dùng này.</p>
                        <?php else: ?>
                            <div class="space-y-4 max-h-96 overflow-y-auto">
                                <?php foreach ($userReviews as $review): ?>
                                    <div class="border-b border-gray-200 pb-4 user-review-item" data-rating="<?php echo $review['rating']; ?>">
                                        <div class="flex justify-between items-center">
                                            <div class="flex items-center gap-2">
                                                <span class="font-medium text-gray-800"><?php echo htmlspecialchars($review['username'] ?? 'Không xác định'); ?></span>
                                                <div class="star-rating">
                                                    <?php for ($i = 0; $i < 5; $i++): ?>
                                                        <i class="bi <?php echo $i < $review['rating'] ? 'bi-star-fill' : 'bi-star'; ?> text-yellow-400"></i>
                                                    <?php endfor; ?>
                                                </div>
                                            </div>
                                            <small class="text-gray-500"><?php echo date('d/m/Y H:i', strtotime($review['created_at'])); ?></small>
                                        </div>
                                        <p class="text-gray-700 mt-2"><?php echo htmlspecialchars($review['comment']); ?></p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </section>
        <?php endif; ?>
    </main>

    <?php require_once __DIR__ . '/../layouts/footer.php'; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="/assets/js/plugins/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            // Hiển thị thông báo
            <?php if ($success = Session::get('success')): ?>
                Swal.fire({
                    icon: 'success',
                    title: 'Thành công',
                    text: '<?php echo htmlspecialchars($success); ?>',
                    timer: 2000,
                    showConfirmButton: false
                });
                <?php Session::unset('success'); ?>
            <?php endif; ?>
            <?php if ($error = Session::get('error')): ?>
                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi',
                    text: '<?php echo htmlspecialchars($error); ?>',
                    timer: 2000,
                    showConfirmButton: false
                });
                <?php Session::unset('error'); ?>
            <?php endif; ?>

            // Bộ lọc đánh giá shop
            $('#review-filter').on('change', function() {
                const rating = $(this).val();
                $('.review-item').each(function() {
                    if (rating === '' || $(this).data('rating') == rating) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            });

            // Bộ lọc đánh giá người dùng
            $('#user-review-filter').on('change', function() {
                const rating = $(this).val();
                $('.user-review-item').each(function() {
                    if (rating === '' || $(this).data('rating') == rating) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            });

            // Thu gọn/mở rộng sidebar trên mobile
            $('#show-sidebar').on('click', function() {
                $('#sidebar').removeClass('sidebar-hidden').show();
            });
            $('#toggle-sidebar').on('click', function() {
                $('#sidebar').addClass('sidebar-hidden').hide();
            });

            // Bộ lọc sản phẩm (client-side, có thể mở rộng thành AJAX nếu cần)
            $('#product-filter-form').on('submit', function(e) {
                e.preventDefault();
                const categoryId = $('select[name="category_id"]').val();
                const minPrice = parseFloat($('input[name="min_price"]').val()) || 0;
                const maxPrice = parseFloat($('input[name="max_price"]').val()) || Infinity;
                const sort = $('select[name="sort"]').val();

                let filteredProducts = <?php echo json_encode($products); ?>;

                // Lọc theo danh mục
                if (categoryId) {
                    filteredProducts = filteredProducts.filter(product => product.category_id == categoryId);
                }

                // Lọc theo giá
                filteredProducts = filteredProducts.filter(product =>
                    product.price >= minPrice && product.price <= maxPrice
                );

                // Sắp xếp
                if (sort) {
                    filteredProducts.sort((a, b) => {
                        if (sort === 'price_asc') return a.price - b.price;
                        if (sort === 'price_desc') return b.price - a.price;
                        if (sort === 'views_desc') return b.views - a.views;
                        return 0;
                    });
                }

                // Hiển thị danh sách sản phẩm đã lọc
                $('#product-list').empty();
                if (filteredProducts.length === 0) {
                    $('#product-list').html('<p class="text-gray-600 text-center">Không tìm thấy sản phẩm phù hợp.</p>');
                } else {
                    filteredProducts.forEach(product => {
                        $('#product-list').append(`
                            <div class="bg-white rounded-lg shadow product-card">
                                <a href="/products/${product.id}">
                                    <img src="/Uploads/partners/${product.image || 'default-product.jpg'}"
                                        alt="${product.title || 'Sản phẩm'}" class="w-full h-48 object-cover rounded-t-lg">
                                    <div class="p-4">
                                        <h3 class="text-base font-medium text-gray-800 truncate">${product.title || 'Không có tiêu đề'}</h3>
                                        <p class="text-red-600 font-semibold mt-1">${new Intl.NumberFormat('vi-VN').format(product.price || 0)} VNĐ</p>
                                        <p class="text-sm text-gray-600 mt-1"><i class="bi bi-eye mr-1"></i>Lượt xem: ${product.views || 0}</p>
                                    </div>
                                </a>
                            </div>
                        `);
                    });
                }
            });
        });
    </script>
</body>

</html>