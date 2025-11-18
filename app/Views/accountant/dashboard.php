<?php

use App\Helpers\Session;

$user = Session::get('user');
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kế toán - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); font-family: 'Inter', sans-serif; min-height: 100vh; }
        .card { border: none; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); }
        .card:hover { transform: translateY(-10px); transition: all 0.3s ease; }
        .table th { background-color: #5a3f7d; color: white; }
        .badge { font-size: 0.9em; padding: 0.6em 1em; }
        .btn { transition: all 0.2s; }
        .btn:hover { transform: scale(1.05); }
    </style>
</head>
<body class="text-white">

    <div class="container py-5">
        <div class="text-center mb-5">
            <h1 class="display-4 fw-bold">Kế toán - Quản lý rút tiền</h1>
            <p class="lead">Chào mừng <?= htmlspecialchars(Session::get('user')['username']) ?>!</p>
        </div>

        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card bg-white text-dark">
                    <div class="card-body text-center">
                        <h5>Yêu cầu đang chờ</h5>
                        <h2 class="text-primary fw-bold"><?= count($pending) ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-white text-dark">
                    <div class="card-body text-center">
                        <h5>Hôm nay đã duyệt</h5>
                        <h2 class="text-success fw-bold">
                            <?php
                            $today = date('Y-m-d');
                            $stmt = (new \App\Config\Database())->getConnection()->prepare("
                                SELECT COUNT(*) FROM withdrawal_requests WHERE status IN ('approved','completed') AND DATE(processed_at) = ?
                            ");
                            $stmt->execute([$today]);
                            echo $stmt->fetchColumn();
                            ?>
                        </h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <a href="/accountant/history" class="text-decoration-none">
                    <div class="card bg-white text-dark">
                        <div class="card-body text-center">
                            <h5>Xem lịch sử xử lý</h5>
                            <h2 class="text-info fw-bold">→</h2>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <div class="card bg-white text-dark">
            <div class="card-body">
                <h3 class="text-center mb-4 text-primary">Yêu cầu rút tiền đang chờ duyệt</h3>
                <?php if (empty($pending)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                        <h4 class="mt-3">Không có yêu cầu nào đang chờ!</h4>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Shop</th>
                                    <th>Ngân hàng</th>
                                    <th>Số tiền</th>
                                    <th>Thời gian</th>
                                    <th>Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pending as $r): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($r['username']) ?></strong></td>
                                    <td>
                                        <img src="<?= htmlspecialchars($r['logo']) ?>" width="35" class="me-2 rounded">
                                        <?= htmlspecialchars($r['bank_name']) ?><br>
                                        <small>STK: <?= htmlspecialchars($r['account_number']) ?></small>
                                    </td>
                                    <td class="fw-bold text-danger fs-5"><?= number_format($r['amount']) ?>đ</td>
                                    <td><?= date('d/m/Y H:i', strtotime($r['requested_at'])) ?></td>
                                    <td>
                                        <button class="btn btn-success btn-sm me-1" onclick="approve(<?= $r['id'] ?>)">Duyệt</button>
                                        <button class="btn btn-danger btn-sm" onclick="reject(<?= $r['id'] ?>)">Từ chối</button>
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

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const approve = (id) => Swal.fire({
            title: 'Duyệt yêu cầu?', icon: 'question', showCancelButton: true, confirmButtonText: 'Duyệt'
        }).then(r => r.isConfirmed && action('/accountant/approve', id));

        const reject = (id) => Swal.fire({
            title: 'Từ chối?', input: 'text', inputLabel: 'Lý do', showCancelButton: true
        }).then(res => res.isConfirmed && res.value && action('/accountant/reject', id, res.value));

        const action = (url, id, note = '') => {
            fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'id=' + id + (note ? '&note=' + encodeURIComponent(note) : '')
            })
            .then(r => r.json())
            .then(res => {
                Swal.fire({ icon: res.success ? 'success' : 'error', title: res.success ? 'Thành công' : 'Lỗi', text: res.message, timer: 1500 })
                .then(() => res.success && location.reload());
            });
        };
    </script>
    <?php include __DIR__ . '/../../layouts/footer.php'; ?>
</body>
</html>