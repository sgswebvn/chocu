<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý danh mục</title>
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

        .btn {
            transition: transform 0.2s ease, background-color 0.2s ease;
        }

        .btn:hover {
            transform: scale(1.05);
        }

        .table th {
            background-color: #f1f3f5;
            font-weight: 600;
        }

        @media (max-width: 576px) {
            .table-responsive {
                font-size: 0.85rem;
            }

            .btn-sm {
                font-size: 0.8rem;
            }
        }
    </style>
</head>

<body>
    <?php include __DIR__ . '/../layouts/header.php'; ?>
    <main class="container mt-4">
        <div class="row align-items-center mb-4">
            <div class="col-auto">
                <h2 class="fw-bold text-dark">Quản lý danh mục</h2>
            </div>
            <div class="col-auto ms-auto">
                <a href="/admin/categories/create" class="btn btn-primary">Thêm danh mục mới</a>
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
                                <th>Tên danh mục</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($categories)): ?>
                                <tr>
                                    <td colspan="3" class="text-center py-4">Không có danh mục nào!</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($categories as $category): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($category['id']); ?></td>
                                        <td><?php echo htmlspecialchars($category['name']); ?></td>
                                        <td>
                                            <a href="/admin/categories/edit/<?php echo $category['id']; ?>" class="btn btn-sm btn-warning me-1">Sửa</a>
                                            <button class="btn btn-sm btn-danger delete-btn" data-id="<?php echo $category['id']; ?>">Xóa</button>
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
    <?php include __DIR__ . '/../layouts/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const deleteButtons = document.querySelectorAll('.delete-btn');
            deleteButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const categoryId = this.getAttribute('data-id');
                    Swal.fire({
                        title: 'Bạn có chắc chắn?',
                        text: 'Bạn muốn xóa danh mục này?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Xóa',
                        cancelButtonText: 'Hủy',
                        confirmButtonColor: '#dc3545',
                        cancelButtonColor: '#6c757d'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            fetch('/admin/categories/delete/' + categoryId, {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-Requested-With': 'XMLHttpRequest'
                                    }
                                })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success) {
                                        Swal.fire({
                                            icon: 'success',
                                            title: 'Thành công',
                                            text: data.message,
                                            confirmButtonText: 'OK'
                                        }).then(() => window.location.reload());
                                    } else {
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'Lỗi',
                                            text: data.message,
                                            confirmButtonText: 'OK'
                                        });
                                    }
                                })
                                .catch(error => {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Lỗi',
                                        text: 'Đã xảy ra lỗi khi xóa danh mục!',
                                        confirmButtonText: 'OK'
                                    });
                                });
                        }
                    });
                });
            });
        });
    </script>
</body>

</html>