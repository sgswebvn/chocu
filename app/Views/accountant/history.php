<?php

use App\Helpers\Session;

$user = Session::get('user');
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kế toán - Lịch sử xử lý</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; padding-top: 80px; }
        .card { border-radius: 16px; box-shadow: 0 8px 30px rgba(0,0,0,0.1); }
    </style>
</head>
<body>

    <div class="container py-5">
        <h2 class="text-center mb-5 fw-bold text-primary">Lịch sử xử lý rút tiền</h2>
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead class="table-dark">
                            <tr>
                                <th>Shop</th>
                                <th>Số tiền</th>
                                <th>Trạng thái</th>
                                <th>Thời gian xử lý</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($processed as $p): ?>
                            <tr>
                                <td><?= htmlspecialchars($p['username']) ?></td>
                                <td class="fw-bold text-danger"><?= number_format($p['amount']) ?>đ</td>
                                <td>
                                    <span class="badge bg-<?= $p['status']=='completed'?'success':($p['status']=='approved'?'info':'danger') ?>">
                                        <?= $p['status']=='completed'?'Đã chuyển':($p['status']=='approved'?'Đã duyệt':'Từ chối') ?>
                                    </span>
                                </td>
                                <td><?= $p['processed_at'] ? date('d/m/Y H:i', strtotime($p['processed_at'])) : '-' ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="text-center mt-4">
            <a href="/accountant/dashboard" class="btn btn-primary">Quay lại Dashboard</a>
        </div>
    </div>
</body>
</html>