<?php

use App\Helpers\Session;

$user = Session::get('user');
?>

<?php require_once __DIR__ . '/../layouts/navbar.php'; ?>

<!-- [Page specific CSS] -->
<link rel="stylesheet" href="/assets2/css/plugins/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700&display=swap" id="main-font-link">
<link rel="stylesheet" href="/assets2/fonts/tabler-icons.min.css">
<link rel="stylesheet" href="/assets2/fonts/feather.css">
<link rel="stylesheet" href="/assets2/fonts/fontawesome.css">
<link rel="stylesheet" href="/assets2/fonts/material.css">
<link rel="stylesheet" href="/assets2/css/style.css" id="main-style-link">
<link rel="stylesheet" href="/assets2/css/style-preset.css" id="preset-style-link">

<header class="pc-header">
    <div class="header-wrapper">
        <div class="me-auto pc-mob-drp">
            <ul class="list-unstyled">
                <li class="pc-h-item pc-sidebar-collapse">
                    <a href="#" class="pc-head-link ms-0" id="sidebar-hide">
                        <i class="ti ti-menu-2"></i>
                    </a>
                </li>
                <li class="pc-h-item pc-sidebar-popup">
                    <a href="#" class="pc-head-link ms-0" id="mobile-collapse">
                        <i class="ti ti-menu-2"></i>
                    </a>
                </li>
            </ul>
        </div>

    </div>
</header>

<div class="pc-container">
    <div class="pc-content">
        <div class="page-header">
            <div class="page-block">
                <div class="row align-items-center">
                    <div class="col-md-12">
                        <div class="page-header-title">
                            <h5 class="m-b-10">Quản lý đơn hàng</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <table class="table table-hover table-bordered">
                            <thead>
                                <tr>
                                    <th>Mã đơn hàng</th>
                                    <th>Người mua</th>
                                    <th>Sản phẩm</th>
                                    <th>Số lượng</th>
                                    <th>Tổng tiền</th>
                                    <th>Trạng thái</th>
                                    <th>Ngày đặt</th>
                                    <th>Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td>#<?php echo htmlspecialchars($order['id']); ?></td>
                                        <td><?php echo htmlspecialchars($order['buyer_name']); ?></td>
                                        <td><?php echo htmlspecialchars($order['title']); ?></td>
                                        <td><?php echo htmlspecialchars($order['quantity']); ?></td>
                                        <td><?php echo number_format($order['total_price'], 0, ',', '.'); ?> VNĐ</td>
                                        <td>
                                            <?php
                                            $badgeClass = match ($order['status']) {
                                                'pending' => 'warning',
                                                'processing' => 'info',
                                                'shipped' => 'primary',
                                                'delivered' => 'success',
                                                default => 'danger',
                                            };

                                            $statusText = match ($order['status']) {
                                                'pending' => 'Chờ xử lý',
                                                'processing' => 'Đang xử lý',
                                                'shipped' => 'Đã giao hàng',
                                                'delivered' => 'Đã nhận hàng',
                                                default => 'Đã hủy',
                                            };
                                            ?>
                                            <span class="badge bg-<?php echo $badgeClass; ?>">
                                                <?php echo $statusText; ?>
                                            </span>
                                        </td>

                                        <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                                        <td>
                                            <a href="/partners/orders/<?php echo $order['id']; ?>" class="btn btn-sm btn-primary">Xem chi tiết</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="/assets2/js/plugins/popper.min.js"></script>
<script src="/assets2/js/plugins/simplebar.min.js"></script>
<script src="/assets2/js/plugins/bootstrap.min.js"></script>
<script src="/assets2/js/fonts/custom-font.js"></script>
<script src="/assets2/js/pcoded.js"></script>
<script src="/assets2/js/plugins/feather.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
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
    });
</script>