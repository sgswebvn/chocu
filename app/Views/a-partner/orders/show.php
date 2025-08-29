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
                            <h5 class="m-b-10">Chi tiết đơn hàng #<?php echo htmlspecialchars($order['id']); ?></h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <h5>Thông tin đơn hàng</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Mã đơn hàng:</strong> #<?php echo htmlspecialchars($order['id']); ?></p>
                                <p><strong>Người mua:</strong> <?php echo htmlspecialchars($order['buyer_name']); ?></p>
                                <p><strong>Sản phẩm:</strong> <?php echo htmlspecialchars($order['title']); ?></p>
                                <p><strong>Số lượng:</strong> <?php echo htmlspecialchars($order['quantity']); ?></p>
                                <p><strong>Tổng tiền:</strong> <?php echo number_format($order['total_price'], 0, ',', '.'); ?> VNĐ</p>
                                <p><strong>Trạng thái:</strong>
                                    <span class="badge bg-<?php echo $order['status'] === 'pending' ? 'warning' : ($order['status'] === 'processing' ? 'info' : ($order['status'] === 'shipped' ? 'primary' : ($order['status'] === 'delivered' ? 'success' : ($order['status'] === 'confirmed' ? 'success' : 'danger')))); ?>">
                                        <?php echo htmlspecialchars($order['status']); ?>
                                    </span>
                                </p>
                                <p><strong>Ngày đặt:</strong> <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></p>
                                <p><strong>Phương thức thanh toán:</strong> <?php echo htmlspecialchars($order['payment_method'] === 'cod' ? 'Thanh toán khi nhận hàng' : 'PayOS'); ?></p>
                                <?php if ($order['status'] === 'shipped' || $order['status'] === 'delivered'): ?>
                                    <p><strong>Mã vận đơn:</strong> <?php echo htmlspecialchars($order['tracking_number'] ?: 'Chưa có'); ?></p>
                                    <p><strong>Đơn vị vận chuyển:</strong> <?php echo htmlspecialchars($order['carrier'] ?: 'Chưa có'); ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <h6>Thông tin giao hàng</h6>
                                <?php if ($orderDetails): ?>
                                    <p><strong>Họ tên:</strong> <?php echo htmlspecialchars($orderDetails['fullname']); ?></p>
                                    <p><strong>Số điện thoại:</strong> <?php echo htmlspecialchars($orderDetails['phone']); ?></p>
                                    <p><strong>Địa chỉ:</strong> <?php echo htmlspecialchars($orderDetails['house_no'] . ', ' . $orderDetails['road_name'] . ', ' . $orderDetails['town_city'] . ', ' . $orderDetails['state'] . ', ' . $orderDetails['pincode']); ?></p>
                                    <p><strong>Địa điểm nổi bật:</strong> <?php echo htmlspecialchars($orderDetails['landmark'] ?: 'Không có'); ?></p>
                                <?php else: ?>
                                    <p>Không có thông tin giao hàng.</p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if (in_array($order['status'], ['pending', 'processing', 'shipped'])): ?>
                            <h5 class="mt-4">Cập nhật trạng thái</h5>
                            <form action="/partners/orders/update/<?php echo $order['id']; ?>" method="POST">
                                <input type="hidden" name="_token" value="<?php echo Session::get('csrf_token'); ?>">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Trạng thái</label>
                                            <select name="status" class="form-control">
                                                <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Chờ xử lý</option>
                                                <option value="processing" <?php echo $order['status'] === 'processing' ? 'selected' : ''; ?>>Đang xử lý</option>
                                                <option value="shipped" <?php echo $order['status'] === 'shipped' ? 'selected' : ''; ?>>Đã giao vận chuyển</option>
                                                <option value="delivered" <?php echo $order['status'] === 'delivered' ? 'selected' : ''; ?>>Đã giao hàng</option>
                                                <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Hủy</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary mt-3">Cập nhật</button>
                                <a href="/partners/orders" class="btn btn-success mt-3">Quay trở lại</a>

                            </form>
                        <?php endif; ?>
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