<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý sản phẩm</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Inter', sans-serif;
            padding-top: 70px;
        }

        .nav-link {
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .nav-link:hover {
            background-color: #495057;
            color: #fff !important;
        }

        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 25px rgba(0, 0, 0, 0.1);
        }

        .table {
            border-radius: 12px;
            overflow: hidden;
        }

        .table th,
        .table td {
            vertical-align: middle;
            transition: background-color 0.3s ease;
            border-bottom: 1px solid #dee2e6;
        }

        .table-hover tbody tr:hover {
            background-color: #e9ecef;
        }

        .btn,
        .form-select {
            transition: transform 0.2s ease, background-color 0.2s ease;
        }

        .btn:hover,
        .form-select:hover {
            transform: scale(1.05);
        }

        .table th {
            background-color: #f1f3f5;
            font-weight: 600;
        }

        .form-select {
            width: 120px;
        }

        @media (max-width: 576px) {
            .table-responsive {
                font-size: 0.85rem;
            }

            .form-select {
                width: 100px;
                font-size: 0.8rem;
            }
        }
    </style>
</head>

<body>
    <?php include __DIR__ . '/layouts/header.php'; ?>
    <main class="container mt-4">
        <div class="row align-items-center mb-4">
            <div class="col-auto">
                <h2 class="fw-bold text-dark">Quản lý sản phẩm</h2>
            </div>
            <div class="col-auto ms-auto">
                <form class="d-flex" action="/admin/products/search" method="GET">
                    <input class="form-control me-2" type="search" name="keyword" placeholder="Tìm kiếm sản phẩm" value="<?php echo htmlspecialchars($_GET['keyword'] ?? ''); ?>">
                    <button class="btn btn-outline-primary" type="submit">Tìm</button>
                </form>
            </div>
        </div>
        <?php if ($success = \App\Helpers\Session::get('success')): ?>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'success',
                        title: 'Thành công',
                        text: '<?php echo htmlspecialchars($success); ?>',
                        confirmButtonText: 'OK'
                    });
                });
            </script>
            <?php \App\Helpers\Session::unset('success'); ?>
        <?php endif; ?>
        <?php if ($error = \App\Helpers\Session::get('error')): ?>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Lỗi',
                        text: '<?php echo htmlspecialchars($error); ?>',
                        confirmButtonText: 'OK'
                    });
                });
            </script>
            <?php \App\Helpers\Session::unset('error'); ?>
        <?php endif; ?>
        <div class="card">
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-hover table-borderless">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Tên sản phẩm</th>
                                <th>Mô tả</th>
                                <th>Trạng thái</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($products)): ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4">Không có sản phẩm nào!</td>
                                </tr>
                            <?php else: ?>
                                <?php
                                $statusMap = [
                                    'pending' => 'Đang chờ duyệt',
                                    'approved' => 'Đã duyệt',
                                    'rejected' => 'Bị từ chối'
                                ];
                                ?>
                                <?php foreach ($products as $product): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($product['id']); ?></td>
                                        <td><?php echo htmlspecialchars($product['title']); ?></td>
                                        <td><?php echo htmlspecialchars(substr($product['description'], 0, 50)); ?>...</td>
                                        <td><?php echo htmlspecialchars($statusMap[$product['status']] ?? $product['status']); ?></td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <select class="form-select form-select-sm update-status" data-id="<?php echo $product['id']; ?>">
                                                    <option value="" disabled selected>Chọn Trạng thái</option>
                                                    <?php if ($product['status'] !== 'approved'): ?>
                                                        <option value="approved">Duyệt</option>
                                                    <?php endif; ?>
                                                    <?php if ($product['status'] !== 'rejected'): ?>
                                                        <option value="rejected">Từ chối</option>
                                                    <?php endif; ?>
                                                    <?php if ($product['status'] !== 'pending'): ?>
                                                        <option value="pending">Chờ duyệt</option>
                                                    <?php endif; ?>
                                                </select>
                                                <a href="/admin/products/view/<?php echo $product['id']; ?>" class="btn btn-sm btn-primary">Xem chi tiết</a>
                                            </div>
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
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.update-status').forEach(select => {
                select.addEventListener('change', function() {
                    const productId = this.getAttribute('data-id');
                    const status = this.value;
                    const statusText = {
                        'approved': 'Duyệt',
                        'rejected': 'Từ chối',
                        'pending': 'Chờ duyệt'
                    } [status];

                    Swal.fire({
                        title: 'Xác nhận cập nhật',
                        text: `Bạn có chắc muốn đặt trạng thái sản phẩm này thành "${statusText}"?`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Cập nhật',
                        cancelButtonText: 'Hủy'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            fetch(`/admin/products/status/${productId}/${status}`, {
                                    method: 'GET',
                                    headers: {
                                        'X-Requested-With': 'XMLHttpRequest'
                                    }
                                })
                                .then(response => response.json())
                                .then(data => {
                                    Swal.fire({
                                        icon: data.success ? 'success' : 'error',
                                        title: data.success ? 'Thành công' : 'Lỗi',
                                        text: data.message,
                                        confirmButtonText: 'OK'
                                    }).then(() => {
                                        if (data.success) {
                                            window.location.reload();
                                        }
                                    });
                                })
                                .catch(error => {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Lỗi',
                                        text: 'Đã xảy ra lỗi khi cập nhật trạng thái!',
                                        confirmButtonText: 'OK'
                                    });
                                });
                        } else {
                            this.value = ''; // Reset select
                        }
                    });
                });
            });
        });
    </script>
</body>

</html>