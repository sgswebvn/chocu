<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kế toán - Lịch sử xử lý</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; font-family: 'Segoe UI', sans-serif; padding-top: 80px; }
        .navbar { background: rgba(255,255,255,0.95); box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
        .card { border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .table thead { background: #5a3f7d; color: white; }
        .badge { font-size: 0.9rem; padding: 0.6em 1.2em; }
        footer { background: rgba(0,0,0,0.7); color: white; padding: 1.5rem 0; margin-top: 3rem; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light fixed-top">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary" href="/accountant/dashboard">Kế toán</a>
            <span class="text-dark">Xin chào, <strong><?= htmlspecialchars($user['username']) ?></strong></span>
        </div>
    </nav>

    <div class="container py-5">
        <div class="text-center mb-5">
            <h1 class="display-5 fw-bold text-primary">Lịch sử xử lý rút tiền</h1>
            <p class="lead text-muted">Xem lại tất cả các yêu cầu bạn đã xử lý</p>
        </div>

        <div class="card">
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Shop</th>
                                <th>Số tiền</th>
                                <th>Trạng thái</th>
                                <th>Thời gian xử lý</th>
                                <th>Ghi chú</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($processed)): ?>
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox" style="font-size: 4rem; opacity: 0.3;"></i>
                                        <p class="mt-3">Chưa có yêu cầu nào được xử lý</p>
                                    </td>
                                </tr>
                            <?php else: foreach ($processed as $p): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($p['username']) ?></strong></td>
                                    <td class="fw-bold text-danger"><?= number_format($p['amount']) ?>đ</td>
                                    <td>
                                        <span class="badge bg-<?= $p['status']=='completed'?'success':($p['status']=='approved'?'info':'danger') ?>">
                                            <?= $p['status']=='completed'?'Đã chuyển tiền':($p['status']=='approved'?'Đã duyệt':'Từ chối') ?>
                                        </span>
                                    </td>
                                    <td><?= $p['processed_at'] ? date('d/m/Y H:i', strtotime($p['processed_at'])) : '-' ?></td>
                                    <td class="text-muted"><?= htmlspecialchars($p['admin_note'] ?? '-') ?></td>
                                </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="text-center mt-4">
            <a href="/accountant/dashboard" class="btn btn-primary btn-lg px-5">
                Quay lại Dashboard
            </a>
        </div>
    </div>

    <footer class="text-center">
        <p class="mb-0">&copy; 2025 Chợ C2C - Hệ thống kế toán</p>
    </footer>
</body>
</html>