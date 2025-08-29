<?php
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';
?>

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
                            <h5 class="m-b-10">Lịch sử giao dịch</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <h5>Danh sách giao dịch</h5>
                        <div class="table-responsive">
                            <table class="table table-hover table-borderless mb-0">
                                <thead>
                                    <tr>
                                        <th>MÃ GIAO DỊCH</th>
                                        <th>SỐ TIỀN</th>
                                        <th>PHƯƠNG THỨC</th>
                                        <th>TRẠNG THÁI</th>
                                        <th>NGÀY</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($transactions as $transaction): ?>
                                        <?php
                                        $statusClass = match ($transaction['status']) {
                                            'completed' => 'text-success',
                                            'pending' => 'text-warning',
                                            'failed' => 'text-danger',
                                            'cancelled' => 'text-muted',
                                            default => 'text-muted',
                                        };

                                        $statusText = match ($transaction['status']) {
                                            'completed' => 'Đã hoàn tất',
                                            'pending' => 'Đang xử lý',
                                            'failed' => 'Thất bại',
                                            'cancelled' => 'Đã hủy',
                                            default => 'Không rõ',
                                        };
                                        ?>
                                        <tr>
                                            <td><a href="#" class="text-muted"><?php echo htmlspecialchars($transaction['order_code']); ?></a></td>
                                            <td><?php echo number_format($transaction['amount'], 0, ',', '.'); ?> VNĐ</td>
                                            <td><?php echo htmlspecialchars($transaction['payment_method']); ?></td>
                                            <td>
                                                <span class="d-flex align-items-center gap-2">
                                                    <i class="fas fa-circle <?php echo $statusClass; ?> f-10 m-r-5"></i>
                                                    <?php echo $statusText; ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($transaction['created_at'])); ?></td>
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
</div>

<?php
require_once __DIR__ . '/../layouts/footer.php';
?>