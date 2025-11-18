<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<?php require_once __DIR__ . '/../layouts/navbar.php'; ?>

<?php
use App\Helpers\Session;
use App\Config\Database;

$user = Session::get('user');
if (!$user || $user['role'] !== 'partners' || !$user['is_partner_paid']) {
    header('Location: /upgrade'); exit;
}

$pdo = (new Database())->getConnection();
$userId = $user['id'];

// LẤY TỔNG DOANH THU (đã giao thành công)
$stmt = $pdo->prepare("
    SELECT COALESCE(SUM(total_price), 0) as total 
    FROM orders 
    WHERE seller_id = ? AND status = 'delivered'
");
$stmt->execute([$userId]);
$totalRevenue = $stmt->fetchColumn();

// LẤY SỐ TIỀN ĐÃ RÚT
$stmt = $pdo->prepare("
    SELECT COALESCE(SUM(amount), 0) 
    FROM withdrawal_requests 
    WHERE user_id = ? AND status IN ('approved', 'completed')
");
$stmt->execute([$userId]);
$withdrawn = $stmt->fetchColumn();

// TIỀN CÓ THỂ RÚT = DOANH THU - ĐÃ RÚT
$available = $totalRevenue - $withdrawn;

// LẤY NGÂN HÀNG ĐÃ LIÊN KẾT
$stmt = $pdo->prepare("SELECT * FROM bank_accounts WHERE user_id = ? AND is_default = 1 LIMIT 1");
$stmt->execute([$userId]);
$bankAccount = $stmt->fetch(PDO::FETCH_ASSOC);

// LẤY LỊCH SỬ RÚT TIỀN
$stmt = $pdo->prepare("
    SELECT * FROM withdrawal_requests 
    WHERE user_id = ? 
    ORDER BY requested_at DESC 
    LIMIT 20
");
$stmt->execute([$userId]);
$withdrawals = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="pc-container">
    <div class="pc-content">
        <div class="page-header">
            <div class="page-block">
                <h5 class="m-b-10">Rút tiền về tài khoản ngân hàng</h5>
            </div>
        </div>

        <!-- TỔNG QUAN DOANH THU -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card border-success shadow-sm">
                    <div class="card-body text-center">
                        <h5 class="text-success fw-bold">Tổng doanh thu</h5>
                        <h3 class="text-success"><?= number_format($totalRevenue) ?>đ</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-warning shadow-sm">
                    <div class="card-body text-center">
                        <h5 class="text-warning fw-bold">Đã rút</h5>
                        <h3 class="text-warning"><?= number_format($withdrawn) ?>đ</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-primary shadow-sm">
                    <div class="card-body text-center">
                        <h5 class="text-primary fw-bold">Có thể rút</h5>
                        <h3 class="text-primary"><?= number_format($available) ?>đ</h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- FORM RÚT TIỀN -->
            <div class="col-lg-6">
                <div class="card border-primary shadow">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Yêu cầu rút tiền</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!$bankAccount): ?>
                            <div class="alert alert-danger text-center">
                                <i class="ti ti-alert-circle"></i>
                                <strong>Bạn chưa liên kết ngân hàng!</strong><br>
                                <a href="/partners/profile" class="btn btn-sm btn-light mt-2">Liên kết ngay</a>
                            </div>
                        <?php elseif ($available <= 0): ?>
                            <div class="alert alert-warning text-center">
                                <i class="ti ti-wallet"></i>
                                <strong>Chưa đủ số dư để rút tiền</strong>
                            </div>
                        <?php else: ?>
                            <form action="/partners/withdraw/request" method="POST">

    <!-- Thông tin ngân hàng -->
    <div class="p-4 bg-white rounded shadow-sm border mb-4 text-center">
        <img src="<?= htmlspecialchars($bankAccount['logo']) ?>" width="70" class="mb-3 rounded shadow-sm">
        <div class="fw-bold fs-5 mb-1"><?= htmlspecialchars($bankAccount['bank_name']) ?></div>
        <div class="text-muted small mb-1"><?= htmlspecialchars($bankAccount['branch']) ?></div>
        <div class="mt-2">
            <span class="fw-bold">STK:</span>
            <?= htmlspecialchars($bankAccount['account_number']) ?>
        </div>
        <div>
            <span class="fw-bold">Chủ TK:</span>
            <?= htmlspecialchars($bankAccount['account_holder']) ?>
        </div>
    </div>

    <!-- Số dư khả dụng -->
    <div class="alert alert-info text-center fw-bold fs-5">
        Số dư khả dụng: <span class="text-primary"><?= number_format($available) ?>đ</span>
    </div>

    <!-- Nhập số tiền -->
    <div class="mb-3">
        <label class="form-label fw-bold">Số tiền muốn rút <span class="text-danger">*</span></label>

        <div class="input-group input-group-lg">
            <span class="input-group-text fw-bold bg-light">₫</span>
            <input type="number"
                   name="amount"
                   class="form-control text-end fw-bold fs-4"
                   min="50000"
                   max="<?= $available ?>"
                   value="<?= $available ?>"
                   required>
        </div>

        <div class="d-flex justify-content-between mt-1">
            <small class="text-muted">Tối thiểu 50.000đ</small>
            <small class="text-muted">Tối đa <?= number_format($available) ?>đ</small>
        </div>
    </div>

    <!-- Thanh trạng thái -->
    <div class="mb-3">
        <?php
            $percent = $available > 0 ? 100 : 0;
        ?>
        <div class="progress" style="height: 12px; border-radius: 10px;">
            <div class="progress-bar bg-primary"
                 role="progressbar"
                 style="width: <?= $percent ?>%; border-radius: 10px;">
            </div>
        </div>
    </div>

    <!-- Nút rút -->
    <div class="d-flex gap-3">
        <button type="submit" class="btn btn-primary btn-lg w-100 shadow-sm">
            Rút tiền
        </button>

        <button type="button"
                onclick="document.querySelector('input[name=amount]').value = <?= $available ?>;"
                class="btn btn-outline-primary btn-lg w-50">
            Rút tối đa
        </button>
    </div>
</form>

                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- LỊCH SỬ RÚT TIỀN -->
            <div class="col-lg-6">
                <div class="card shadow">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0">Lịch sử rút tiền</h5>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($withdrawals)): ?>
                            <div class="text-center py-5 text-muted">
                                <i class="ti ti-history" style="font-size:50px;"></i>
                                <p>Chưa có yêu cầu rút tiền nào</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Thời gian</th>
                                            <th>Số tiền</th>
                                            <th>Trạng thái</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($withdrawals as $w): ?>
                                        <tr>
                                            <td><?= date('d/m/Y H:i', strtotime($w['requested_at'])) ?></td>
                                            <td class="fw-bold text-danger"><?= number_format($w['amount']) ?>đ</td>
                                            <td>
                                                <?php if ($w['status'] == 'pending'): ?>
                                                    <span class="badge bg-warning">Đang chờ duyệt</span>
                                                <?php elseif ($w['status'] == 'approved'): ?>
                                                    <span class="badge bg-info">Đã duyệt</span>
                                                <?php elseif ($w['status'] == 'completed'): ?>
                                                    <span class="badge bg-success">Hoàn tất</span>
                                                <?php elseif ($w['status'] == 'rejected'): ?>
                                                    <span class="badge bg-danger">Từ chối</span>
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
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>