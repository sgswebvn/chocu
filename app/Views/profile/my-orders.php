<?php

namespace App\Helpers;

use App\Helpers\Session;

// Bắt đầu session
Session::start();

// Kiểm tra đăng nhập
if (!Session::get('user')) {
    Session::set('error', 'Vui lòng đăng nhập để xem đơn hàng!');
    header('Location: /login');
    exit;
}

// Xác định đường dẫn
$userLink = Session::get('user') ? '/profile' : '/login';
$currentUserId = Session::get('user')['id'] ?? null;

// Xử lý thông báo
$error = Session::get('error');
$success = Session::get('success');
if ($error) {
    Session::unset('error');
}
if ($success) {
    Session::unset('success');
}

// Bao gồm header
include __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../products/linkcss.php';
?>

<main class="pt-90">
    <div class="mb-4 pb-4"></div>
    <section class="my-account container">
        <div class="row">
            <!-- Sidebar Menu -->
            <?php include __DIR__ . '/./layouts/nav.php'; ?>

            <!-- Nội dung chính -->
            <div class="col-lg-9">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>
                <?php if (empty($orders)): ?>
                    <p class="text-muted">Bạn chưa có đơn hàng nào.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Mã đơn</th>
                                    <th>Sản phẩm</th>
                                    <th>Người bán</th>
                                    <th>Số lượng</th>
                                    <th>Tổng tiền</th>
                                    <th>Trạng thái</th>
                                    <th>Vận chuyển</th>
                                    <th>Thời gian</th>
                                    <th>Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td>#<?= htmlspecialchars($order['id']) ?></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <?php if (!empty($order['image'])): ?>
                                                    <img src="/Uploads/<?= htmlspecialchars($order['image']) ?>" alt="<?= htmlspecialchars($order['title']) ?>" style="width: 50px; height: 50px; object-fit: cover; margin-right: 10px;">
                                                <?php endif; ?>
                                                <?= htmlspecialchars($order['title']) ?>
                                            </div>
                                        </td>
                                        <td><?= htmlspecialchars($order['seller_name'] ?? 'Không xác định') ?></td>
                                        <td><?= htmlspecialchars($order['quantity']) ?></td>
                                        <td><?= number_format($order['total_price'], 0, ',', '.') ?> VND</td>
                                        <td>
                                            <?php
                                            $statusClass = match ($order['status']) {
                                                'delivered' => 'success',
                                                'shipped' => 'info',
                                                'processing' => 'primary',
                                                'pending' => 'warning',
                                                'cancelled' => 'danger',
                                                'pending_payment' => 'danger',
                                                'confirmed' => 'danger'
                                            };
                                            $statusText = match ($order['status']) {
                                                'delivered' => 'Đã giao',
                                                'shipped' => 'Đang giao',
                                                'processing' => 'Đang xử lý',
                                                'pending' => 'Chờ xử lý',
                                                'cancelled' => 'Đã hủy',
                                                'pending_payment' => 'Chờ thanh toán',
                                                'confirmed' => 'Thanh toán thành công'
                                            };
                                            ?>
                                            <span class="badge bg-<?= $statusClass ?> badge-status" data-bs-toggle="tooltip" data-bs-placement="top" title="<?= htmlspecialchars($statusText) ?>">
                                                <?= htmlspecialchars($statusText) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($order['tracking_number'] && $order['carrier']): ?>
                                                <span class="text-muted">
                                                    <?= htmlspecialchars($order['carrier']) ?>: <?= htmlspecialchars($order['tracking_number']) ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">Chưa có thông tin</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars(date('d/m/Y H:i', strtotime($order['created_at']))) ?></td>
                                        <td>
                                            <a href="/orders/<?= htmlspecialchars($order['id']) ?>" class="btn btn-sm text-primary" title="Xem chi tiết">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <?php if (in_array($order['status'], ['pending', 'processing'])): ?>
                                                <a href="/orders/cancel/<?= htmlspecialchars($order['id']) ?>" class="btn btn-sm text-danger js-cancel-order" data-id="<?= htmlspecialchars($order['id']) ?>" title="Hủy đơn hàng">
                                                    <i class="bi bi-x-circle"></i>
                                                </a>
                                                <?php if ($order['status'] === 'pending'): ?>
                                                    <a href="/orders/pay/<?= htmlspecialchars($order['id']) ?>" class="btn btn-sm text-success" title="Thanh toán lại">
                                                        <i class="bi bi-credit-card"></i>
                                                    </a>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
</main>

<!-- Tích hợp SweetAlert2 và Bootstrap Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="assets/js/plugins/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/plugins/bootstrap-slider.min.js"></script>
<script src="assets/js/plugins/swiper.min.js"></script>
<script>
    // Tránh tải trùng lặp countdown.js và theme.js
    if (!window.countdownLoaded) {
        document.write('<script src="assets/js/plugins/countdown.js"><\/script>');
        window.countdownLoaded = true;
    }
    if (!window.themeLoaded) {
        document.write('<script src="assets/js/theme.js"><\/script>');
        window.themeLoaded = true;
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Khởi tạo tooltip
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        tooltipTriggerList.forEach(tooltipTriggerEl => {
            new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Hiển thị thông báo từ Session
        <?php if ($error): ?>
            Swal.fire({
                icon: 'error',
                title: 'Lỗi',
                text: '<?= htmlspecialchars($error) ?>',
                confirmButtonText: 'OK',
                confirmButtonColor: '#d33'
            });
        <?php endif; ?>
        <?php if ($success): ?>
            Swal.fire({
                icon: 'success',
                title: 'Thành công',
                text: '<?= htmlspecialchars($success) ?>',
                confirmButtonText: 'OK',
                confirmButtonColor: '#3085d6',
                timer: 2000,
                timerProgressBar: true
            });
        <?php endif; ?>

        // Xử lý hủy đơn hàng
        const cancelButtons = document.querySelectorAll('.js-cancel-order');
        if (cancelButtons.length > 0) {
            cancelButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const orderId = this.getAttribute('data-id');
                    Swal.fire({
                        icon: 'warning',
                        title: 'Xác nhận',
                        text: 'Bạn có chắc muốn hủy đơn hàng này?',
                        showCancelButton: true,
                        confirmButtonText: 'Hủy đơn',
                        cancelButtonText: 'Không',
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            fetch('/orders/cancel/' + orderId, {
                                    method: 'POST',
                                    headers: {
                                        'X-Requested-With': 'XMLHttpRequest'
                                    }
                                })
                                .then(response => {
                                    if (!response.ok) {
                                        throw new Error('Phản hồi từ server không hợp lệ: ' + response.status);
                                    }
                                    return response.json();
                                })
                                .then(data => {
                                    Swal.fire({
                                        icon: data.success ? 'success' : 'error',
                                        title: data.success ? 'Thành công' : 'Lỗi',
                                        text: data.message || 'Đã xảy ra lỗi không xác định. Vui lòng thử lại!',
                                        confirmButtonText: 'OK',
                                        confirmButtonColor: data.success ? '#3085d6' : '#d33',
                                        timer: data.success ? 2000 : null,
                                        timerProgressBar: true
                                    }).then(() => {
                                        if (data.success) {
                                            window.location.reload();
                                        }
                                    });
                                })
                                .catch(error => {
                                    console.error('Fetch error:', error);
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Lỗi',
                                        text: 'Đã xảy ra lỗi khi hủy đơn hàng: ' + error.message,
                                        confirmButtonText: 'OK',
                                        confirmButtonColor: '#d33'
                                    });
                                });
                        }
                    });
                });
            });
        }
    });
</script>

<style>
    .account-nav {
        list-style: none;
        padding: 0;
    }

    .account-nav__link {
        display: block;
        padding: 10px 0;
        color: #333;
        text-decoration: none;
        font-weight: 500;
        transition: color 0.2s;
    }

    .account-nav__link:hover,
    .account-nav__link.active {
        color: #007bff;
    }

    .table-responsive {
        max-height: 500px;
        overflow-y: auto;
    }

    .btn-sm {
        font-size: 0.85rem;
        padding: 0.25rem 0.5rem;
    }

    .badge-status {
        font-size: 1rem;
        padding: 0.5em 1em;
        font-weight: 600;
    }

    .bg-success {
        background-color: #28a745 !important;
    }

    .bg-info {
        background-color: #17a2b8 !important;
    }

    .bg-primary {
        background-color: #007bff !important;
    }

    .bg-warning {
        background-color: #ffc107 !important;
    }

    .bg-danger {
        background-color: #dc3545 !important;
    }

    .table th,
    .table td {
        vertical-align: middle;
    }

    @media (max-width: 576px) {
        .table-responsive {
            font-size: 0.85rem;
        }

        .btn-sm {
            font-size: 0.75rem;
        }

        .badge-status {
            font-size: 0.9rem;
        }
    }
</style>

<?php
include __DIR__ . '/../layouts/footer.php';
?>