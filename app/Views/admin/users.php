<DOCUMENT filename="users.php">
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý người dùng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; padding-top: 80px; font-family: 'Inter', sans-serif; }
        .card { border: none; border-radius: 16px; box-shadow: 0 4px 25px rgba(0,0,0,0.08); }
        .table thead { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        .status-badge { font-size: 0.85rem; padding: 0.4em 0.8em; border-radius: 50px; }
        .action-btn { min-width: 100px; font-size: 0.9rem; }
        .filter-group { background: white; padding: 12px 16px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .search-input { border-radius: 50px; padding-left: 40px; }
        .search-wrapper { position: relative; }
        .search-wrapper i { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #999; }
    </style>
</head>
<body>
    <?php include __DIR__ . '/layouts/header.php'; ?>

    <main class="container mt-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
            <h2 class="fw-bold text-dark mb-0">
                <i class="bi bi-people-fill me-2"></i>Quản lý người dùng
            </h2>

            <!-- TÌM KIẾM + LỌC TRẠNG THÁI -->
            <div class="d-flex flex-wrap gap-2 align-items-center">
                <form action="/admin/users" method="GET" class="d-flex gap-2">
                    <!-- Ô tìm kiếm -->
                    <div class="search-wrapper">
                        <i class="bi bi-search"></i>
                        <input type="text" name="keyword" class="form-control search-input" 
                               placeholder="Tìm tên, email..." 
                               value="<?php echo htmlspecialchars($_GET['keyword'] ?? ''); ?>">
                    </div>

                    <!-- Lọc trạng thái -->
                    <select name="status" class="form-select filter-group" style="min-width: 180px;">
                        <option value="">Tất cả trạng thái</option>
                        <option value="active" <?php echo ($_GET['status'] ?? '') === 'active' ? 'selected' : ''; ?>>
                            Đang hoạt động
                        </option>
                        <option value="banned" <?php echo ($_GET['status'] ?? '') === 'banned' ? 'selected' : ''; ?>>
                            Bị khóa
                        </option>
                    </select>

                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-funnel"></i> Lọc
                    </button>
                </form>
            </div>
        </div>

        <!-- Thông báo thành công/lỗi -->
        <?php if ($success = \App\Helpers\Session::get('success')): ?>
            <script>Swal.fire('Thành công', '<?php echo addslashes($success); ?>', 'success');</script>
            <?php \App\Helpers\Session::unset('success'); ?>
        <?php endif; ?>
        <?php if ($error = \App\Helpers\Session::get('error')): ?>
            <script>Swal.fire('Lỗi', '<?php echo addslashes($error); ?>', 'error');</script>
            <?php \App\Helpers\Session::unset('error'); ?>
        <?php endif; ?>

        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4">ID</th>
                                <th>Người dùng</th>
                                <th>Vai trò</th>
                                <th>Email</th>
                                <th class="text-center">Trạng thái</th>
                                <th class="text-center">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($users)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox display-4 d-block mb-3"></i>
                                        Không tìm thấy người dùng nào!
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td class="ps-4 fw-600">#<?php echo $user['id']; ?></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar rounded-circle bg-primary text-white me-3 d-flex align-items-center justify-content-center"
                                                     style="width: 40px; height: 40px; font-size: 16px;">
                                                    <?php echo strtoupper(substr($user['username'], 0, 2)); ?>
                                                </div>
                                                <div>
                                                    <div class="fw-bold"><?php echo htmlspecialchars($user['username']); ?></div>
                                                    <small class="text-muted">ID: <?php echo $user['id']; ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if ($user['role'] === 'admin'): ?>
                                                <span class="badge bg-danger status-badge">Admin</span>
                                            <?php elseif ($user['role'] === 'partners'): ?>
                                                <span class="badge bg-warning text-dark status-badge">Nhà cung cấp</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary status-badge">Người dùng</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td class="text-center">
                                            <?php if ($user['role'] === 'admin'): ?>
                                                <span class="badge bg-danger status-badge">Admin</span>
                                            <?php elseif ($user['is_active'] == 1): ?>
                                                <span class="badge bg-success status-badge">Hoạt động</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary status-badge">Bị khóa</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($user['role'] !== 'admin'): ?>
                                                <?php if ($user['is_active'] == 1): ?>
                                                    <a href="/admin/users/deactivate/<?php echo $user['id']; ?>"
                                                       class="btn btn-sm btn-outline-danger action-btn"
                                                       onclick="return confirm('Bạn có chắc muốn KHÓA tài khoản này?')">
                                                        <i class="bi bi-lock"></i> Khóa
                                                    </a>
                                                <?php else: ?>
                                                    <a href="/admin/users/activate/<?php echo $user['id']; ?>"
                                                       class="btn btn-sm btn-success action-btn">
                                                        <i class="bi bi-unlock"></i> Mở khóa
                                                    </a>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="text-muted small">Không thể thao tác</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <?php include __DIR__ . '/layouts/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>
</html>
</DOCUMENT>