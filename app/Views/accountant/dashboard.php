<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kế toán - Quản lý rút tiền</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <style>
        :root { --primary: #5a3f7d; --success: #28a745; --danger: #dc3545; --info: #17a2b8; }
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            font-family: 'Segoe UI', sans-serif;
            min-height: 100vh;
            color: #333;
        }
        .navbar { background: rgba(255,255,255,0.95); box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
        .card { border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.15); transition: all 0.3s ease; }
        .card:hover { transform: translateY(-8px); box-shadow: 0 20px 40px rgba(0,0,0,0.2); }
        .stat-card { background: white; color: #333; text-align: center; padding: 2rem; }
        .stat-number { font-size: 3rem; font-weight: 800; }
        .table { border-radius: 16px; overflow: hidden; }
        .table thead { background: var(--primary); color: white; }
        .btn-action { min-width: 100px; }
        .badge-status { font-size: 0.9rem; padding: 0.5em 1em; }
        footer { background: rgba(0,0,0,0.7); color: white; padding: 1.5rem 0; margin-top: 3rem; }
    </style>
</head>
<body>
    <!-- Navbar riêng cho kế toán -->
    <nav class="navbar navbar-expand-lg navbar-light fixed-top">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary" href="/accountant/dashboard">
                Kế toán - Chợ C2C
            </a>
            <div class="d-flex align-items-center gap-3">
                <span class="text-dark">Xin chào, <strong><?= htmlspecialchars($user['username']) ?></strong></span>
                <a href="/logout" class="btn btn-outline-danger btn-sm">Đăng xuất</a>
            </div>
        </div>
    </nav>

    <div class="container" style="padding-top: 100px;">
        <div class="text-center mb-5">
            <h1 class="display-5 fw-bold text-white">Quản lý yêu cầu rút tiền</h1>
            <p class="lead text-white opacity-90">Duyệt hoặc từ chối yêu cầu rút tiền từ các đối tác</p>
        </div>

        <!-- Thống kê nhanh -->
        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="stat-card rounded-4">
                    <i class="bi bi-hourglass-split text-warning" style="font-size: 2.5rem;"></i>
                    <div class="stat-number text-warning"><?= count($pending) ?></div>
                    <p class="mb-0 fw-bold">Yêu cầu đang chờ</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card rounded-4">
                    <i class="bi bi-check-circle-fill text-success" style="font-size: 2.5rem;"></i>
                    <div class="stat-number text-success">
                        <?php
                        $today = date('Y-m-d');
                        $stmt = (new \App\Config\Database())->getConnection()->prepare("
                            SELECT COUNT(*) FROM withdrawal_requests 
                            WHERE status IN ('approved','completed') AND DATE(processed_at) = ?
                        ");
                        $stmt->execute([$today]);
                        echo $stmt->fetchColumn();
                        ?>
                    </div>
                    <p class="mb-0 fw-bold">Đã xử lý hôm nay</p>
                </div>
            </div>
            <div class="col-md-4">
                <a href="/accountant/history" class="text-decoration-none">
                    <div class="stat-card rounded-4 text-center">
                        <i class="bi bi-clock-history text-info" style="font-size: 2.5rem;"></i>
                        <div class="stat-number text-info">→</div>
                        <p class="mb-0 fw-bold">Xem lịch sử</p>
                    </div>
                </a>
            </div>
        </div>

        <!-- Danh sách yêu cầu chờ duyệt -->
        <div class="card">
            <div class="card-body p-4">
                <h3 class="text-center mb-4 text-primary">
                    <i class="bi bi-cash-coin me-2"></i>Yêu cầu rút tiền đang chờ duyệt
                </h3>

                <?php if (empty($pending)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-check-circle-fill text-success" style="font-size: 5rem; opacity: 0.3;"></i>
                        <h4 class="mt-3 text-success">Tuyệt vời! Không còn yêu cầu nào đang chờ!</h4>
                        <p class="text-muted">Bạn đã xử lý hết rồi</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Shop</th>
                                    <th>Ngân hàng</th>
                                    <th>Số tiền</th>
                                    <th>Thời gian yêu cầu</th>
                                    <th class="text-center">Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pending as $r): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($r['username']) ?></strong>
                                        <br><small class="text-muted">ID: <?= $r['user_id'] ?></small>
                                    </td>
                                    <td>
                                        <img src="<?= htmlspecialchars($r['logo']) ?>" width="40" class="me-2 rounded" alt="bank">
                                        <strong><?= htmlspecialchars($r['bank_name']) ?></strong><br>
                                        <small>Tài khoản ngân hàng : <?= htmlspecialchars($r['account_number']) ?></small>
                                        <br>
                                        <small>Tên tài khoản:  <?= htmlspecialchars($r['account_holder']) ?></small>
                                    </td>
                                    <td class="fw-bold text-danger fs-5">
                                        <?= number_format($r['amount']) ?>đ
                                    </td>
                                    <td>
                                        <?= date('d/m/Y H:i', strtotime($r['requested_at'])) ?>
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-success btn-sm btn-action me-2" onclick="approve(<?= $r['id'] ?>)">
                                            Duyệt
                                        </button>
                                        <button class="btn btn-danger btn-sm btn-action" onclick="reject(<?= $r['id'] ?>)">
                                            Từ chối
                                        </button>
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

    <!-- Footer riêng -->
    <footer class="text-center mt-5">
        <p class="mb-0">&copy; 2025 Chợ C2C - Hệ thống kế toán v1.0</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const approve = (id) => Swal.fire({
            title: 'Duyệt yêu cầu rút tiền?', icon: 'question', showCancelButton: true,
            confirmButtonText: 'Duyệt ngay', cancelButtonText: 'Hủy'
        }).then(r => r.isConfirmed && action('/accountant/approve', id));

        const reject = (id) => Swal.fire({
            title: 'Từ chối yêu cầu?', input: 'text', inputLabel: 'Lý do từ chối',
            showCancelButton: true, confirmButtonText: 'Từ chối', cancelButtonText: 'Hủy'
        }).then(res => res.isConfirmed && res.value && action('/accountant/reject', id, res.value));

        const action = (url, id, note = '') => {
            fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'id=' + id + (note ? '&note=' + encodeURIComponent(note) : '')
            })
            .then(r => r.json())
            .then(res => {
                Swal.fire({ icon: res.success ? 'success' : 'error', title: res.success ? 'Thành công!' : 'Lỗi!', text: res.message, timer: 2000 })
                .then(() => res.success && location.reload());
            });
        };
    </script>
</body>
</html>