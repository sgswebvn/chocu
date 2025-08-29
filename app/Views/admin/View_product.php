<DOCUMENT filename="View_product.php">
    <!DOCTYPE html>
    <html lang="vi">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Chi tiết sản phẩm</title>
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

            .product-image {
                max-width: 100%;
                border-radius: 8px;
                transition: transform 0.3s ease;
            }

            .product-image:hover {
                transform: scale(1.02);
            }

            .btn {
                transition: transform 0.2s ease, background-color 0.2s ease;
            }

            .btn:hover {
                transform: scale(1.05);
            }

            @media (max-width: 576px) {
                .product-image {
                    max-width: 100%;
                }
            }
        </style>
    </head>

    <body>
        <?php include __DIR__ . '/layouts/header.php'; ?>
        <main class="container mt-4">
            <div class="row align-items-center mb-4">
                <div class="col-auto">
                    <h2 class="fw-bold text-dark">Chi tiết sản phẩm</h2>
                </div>
                <div class="col-auto ms-auto">
                    <a href="/admin/products" class="btn btn-outline-primary">Quay lại sản phẩm</a>
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
                    <h5 class="card-title fw-bold">Thông tin sản phẩm</h5>
                    <?php
                    $statusMap = [
                        'pending' => 'Đang chờ duyệt',
                        'approved' => 'Đã duyệt',
                        'rejected' => 'Bị từ chối'
                    ];
                    ?>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>ID:</strong> <?php echo htmlspecialchars($product['id']); ?></p>
                            <p><strong>Tiêu đề:</strong> <?php echo htmlspecialchars($product['title']); ?></p>
                            <p><strong>Mô tả:</strong> <?php echo htmlspecialchars($product['description']); ?></p>
                            <p><strong>Giá:</strong> <?php echo htmlspecialchars(number_format($product['price'], 0, ',', '.')); ?> VNĐ</p>
                            <p><strong>Trạng thái:</strong> <?php echo htmlspecialchars($statusMap[$product['status']] ?? $product['status']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Danh mục:</strong> <?php echo htmlspecialchars($product['category_name']); ?></p>
                            <p><strong>Người đăng:</strong> <?php echo htmlspecialchars($product['username']); ?> (ID: <?php echo htmlspecialchars($product['user_id']); ?>)</p>
                            <p><strong>Ngày đăng:</strong> <?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($product['created_at']))); ?></p>
                            <p><strong>Lượt xem:</strong> <?php echo htmlspecialchars($product['views'] ?? 0); ?></p>
                        </div>
                    </div>
                    <?php if ($product['image']): ?>
                        <p><strong>Hình ảnh:</strong></p>
                        <img src="/Uploads/<?php echo htmlspecialchars($product['image']); ?>" alt="Product Image" class="product-image mb-3">
                    <?php else: ?>
                        <p><strong>Hình ảnh:</strong> Không có</p>
                    <?php endif; ?>
                    <hr>
                    <h6 class="fw-bold">Cập nhật trạng thái</h6>
                    <div class="d-flex gap-2">
                        <?php if ($product['status'] !== 'approved'): ?>
                            <a href="/admin/products/status/<?php echo $product['id']; ?>/approved" class="btn btn-sm btn-success update-status" data-id="<?php echo $product['id']; ?>" data-status="approved">Duyệt</a>
                        <?php endif; ?>
                        <?php if ($product['status'] !== 'rejected'): ?>
                            <a href="/admin/products/status/<?php echo $product['id']; ?>/rejected" class="btn btn-sm btn-danger update-status" data-id="<?php echo $product['id']; ?>" data-status="rejected">Từ chối</a>
                        <?php endif; ?>
                        <?php if ($product['status'] !== 'pending'): ?>
                            <a href="/admin/products/status/<?php echo $product['id']; ?>/pending" class="btn btn-sm btn-warning update-status" data-id="<?php echo $product['id']; ?>" data-status="pending">Chờ duyệt</a>
                        <?php endif; ?>
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
                document.querySelectorAll('.update-status').forEach(button => {
                    button.addEventListener('click', function(e) {
                        e.preventDefault();
                        const productId = this.getAttribute('data-id');
                        const status = this.getAttribute('data-status');
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
                            }
                        });
                    });
                });
            });
        </script>
    </body>

    </html>
</DOCUMENT>