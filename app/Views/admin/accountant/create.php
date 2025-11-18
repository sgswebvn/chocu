<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh sách tài khoản Kế toán</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; font-family: 'Inter', sans-serif; padding-top: 70px; }
        .nav-link:hover { background-color: #495057; color: #fff !important; }
        .card {
            border: none; border-radius: 16px; box-shadow: 0 8px 30px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
        }
        .card:hover { transform: translateY(-8px); box-shadow: 0 15px 40px rgba(0,0,0,0.12); }
        .table th { background-color: #212529; color: white; }
        .table-hover tbody tr:hover { background-color: #f1f3f5; }
        .btn { transition: all 0.2s ease; }
        .btn:hover { transform: scale(1.05); }
        .form-control:focus { border-color: #0d6efd; box-shadow: 0 0 0 0.25rem rgba(13,110,253,.15); }
        .input-group-text { cursor: pointer; }
        .password-toggle { user-select: none; }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../layouts/header.php'; ?>

    <?php
    use App\Helpers\Session;
    use App\Config\Database;

    if (!Session::get('user') || Session::get('user')['role'] !== 'admin') {
        header('Location: /login'); exit;
    }

    $pdo = (new Database())->getConnection();
    $stmt = $pdo->prepare("SELECT id, username, email, created_at FROM users WHERE role = 'accountant' ORDER BY created_at DESC");
    $stmt->execute();
    $accountants = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>

    <main class="container py-5">
        <div class="row g-5">
            <!-- CỘT TRÁI: FORM TẠO TÀI KHOẢN -->
            <div class="col-lg-5">
                <div class="card h-100">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <i class="bi bi-person-plus-fill text-primary" style="font-size: 3rem;"></i>
                            <h3 class="mt-3 fw-bold text-primary">Tạo tài khoản Kế toán</h3>
                            <p class="text-muted">Thêm nhân viên kế toán để quản lý rút tiền</p>
                        </div>

                        <!-- Thông báo -->
                        <?php if ($success = Session::get('success')): ?>
                            <div class="alert alert-success alert-dismissible fade show">
                                <?= htmlspecialchars($success) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                            <?php Session::unset('success'); ?>
                        <?php endif; ?>

                        <?php if ($error = Session::get('error')): ?>
                            <div class="alert alert-danger alert-dismissible fade show">
                                <?= htmlspecialchars($error) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                            <?php Session::unset('error'); ?>
                        <?php endif; ?>

                        <form id="createForm" action="/admin/accountants/create" method="POST">
                            <div class="mb-4">
                                <label class="form-label fw-bold">Họ và tên</label>
                                <input type="text" name="username" class="form-control form-control-lg" placeholder="Nguyễn Văn A" required>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">Email</label>
                                <input type="email" name="email" class="form-control form-control-lg" placeholder="ketoan@example.com" required>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">Mật khẩu</label>
                                <div class="input-group input-group-lg">
                                    <input type="password" name="password" id="password" class="form-control" minlength="6" placeholder="Tối thiểu 6 ký tự" required>
                                    <span class="input-group-text password-toggle" onclick="togglePassword()">
                                        <i class="bi bi-eye-slash" id="eyeIcon"></i>
                                    </span>
                                </div>
                                <small class="text-muted">Mật khẩu sẽ được ẩn – chỉ hiện khi nhấn biểu tượng mắt</small>
                            </div>

                            <div class="d-grid mt-5">
                                <button type="submit" class="btn btn-success btn-lg fw-bold">
                                    Tạo tài khoản Kế toán
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- CỘT PHẢI: DANH SÁCH TÀI KHOẢN -->
            <div class="col-lg-7">
                <div class="card h-100">
                    <div class="card-body p-5">
                        <div class="d-flex align-items-center mb-4">
                            <i class="bi bi-people-fill text-primary me-3" style="font-size: 2.5rem;"></i>
                            <div>
                                <h3 class="fw-bold mb-0">Danh sách tài khoản Kế toán</h3>
                                <p class="text-muted mb-0"><?= count($accountants) ?> tài khoản đang hoạt động</p>
                            </div>
                        </div>

                        <?php if (empty($accountants)): ?>
                            <div class="text-center py-5 text-muted">
                                <i class="bi bi-person-x" style="font-size: 4rem; opacity: 0.3;"></i>
                                <p class="mt-3 fw-bold">Chưa có tài khoản kế toán nào</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th>Họ tên</th>
                                            <th>Email</th>
                                            <th>Ngày tạo</th>
                                            <th>Hành động</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($accountants as $acc): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width:40px;height:40px;">
                                                        <i class="bi bi-person"></i>
                                                    </div>
                                                    <strong><?= htmlspecialchars($acc['username']) ?></strong>
                                                </div>
                                            </td>
                                            <td><?= htmlspecialchars($acc['email']) ?></td>
                                            <td><?= date('d/m/Y', strtotime($acc['created_at'])) ?></td>
                                            <td>
                                                <button class="btn btn-outline-danger btn-sm" onclick="deleteAccount(<?= $acc['id'] ?>)">
                                                    Xóa
                                                </button>
                                                <button class="btn btn-outline-primary btn-sm" onclick="resetPassword(<?= $acc['id'] ?>)">
                                                    Reset MK
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
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Hiện/ẩn mật khẩu
        function togglePassword() {
            const input = document.getElementById('password');
            const icon = document.getElementById('eyeIcon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('bi-eye-slash', 'bi-eye');
            } else {
                input.type = 'password';
                icon.classList.replace('bi-eye', 'bi-eye-slash');
            }
        }

        // Tạo tài khoản
        document.getElementById('createForm').onsubmit = async function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            const res = await fetch('/admin/accountants/create', {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await res.json();

            Swal.fire({
                icon: data.success ? 'success' : 'error',
                title: data.success ? 'Thành công!' : 'Lỗi!',
                text: data.message,
                timer: data.success ? 2000 : null
            }).then(() => {
                if (data.success) {
                    this.reset();
                    location.reload();
                }
            });
        };

        // Xóa tài khoản
        function deleteAccount(id) {
            Swal.fire({
                title: 'Xóa tài khoản?',
                text: "Hành động này không thể hoàn tác!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Xóa',
                cancelButtonText: 'Hủy',
                confirmButtonColor: '#d33'
            }).then(r => {
                if (r.isConfirmed) {
                    fetch('/admin/accountants/delete', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: 'id=' + id
                    }).then(() => location.reload());
                }
            });
        }

        // Reset mật khẩu
        function resetPassword(id) {
            Swal.fire({
                title: 'Reset mật khẩu?',
                text: "Mật khẩu mới sẽ là: 123456",
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Reset',
                cancelButtonText: 'Hủy'
            }).then(r => {
                if (r.isConfirmed) {
                    fetch('/admin/accountants/reset-password', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: 'id=' + id
                    }).then(() => {
                        Swal.fire('Đã reset!', 'Mật khẩu mới: 123456', 'success');
                    });
                }
            });
        }
    </script>

    <?php include __DIR__ . '/../layouts/footer.php'; ?>
</body>
</html>