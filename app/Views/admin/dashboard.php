<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bảng điều khiển</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
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

        .card-body {
            text-align: center;
        }

        .card-title {
            font-size: 1.2rem;
            color: #343a40;
        }

        .card-text {
            font-size: 2.5rem;
            font-weight: bold;
            color: #007bff;
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

        .table th {
            background-color: #f1f3f5;
            font-weight: 600;
        }

        .btn {
            transition: transform 0.2s ease, background-color 0.2s ease;
        }

        .btn:hover {
            transform: scale(1.05);
        }

        .product-img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 8px;
        }

        @media (max-width: 576px) {
            .card-text {
                font-size: 2rem;
            }

            .table-responsive {
                font-size: 0.85rem;
            }

            .product-img {
                width: 40px;
                height: 40px;
            }
        }
    </style>
</head>

<body>
    <?php
    $adminModel = new \App\Models\Admin();
    $stats = $adminModel->getStats();
    $revenueByDay = $adminModel->getRevenueByPeriod('day');
    $revenueByMonth = $adminModel->getRevenueByPeriod('month');
    $revenueByYear = $adminModel->getRevenueByPeriod('year');
    $topSellers = $adminModel->getSellerComparisons('revenue');
    $violatingSellers = $adminModel->detectViolatingSellers();
    $topProducts = $adminModel->getTopSellingProducts(10);
    $topCategories = $adminModel->getTopSellingCategories(5);
    ?>
    <?php include __DIR__ . '/layouts/header.php'; ?>
    <main class="container mt-4">
        <h2 class="fw-bold text-dark mb-4">Bảng điều khiển</h2>
        <!-- Summary Cards -->
        <div class="row g-4 mb-4">
            <div class="col-md-3 col-sm-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Sản phẩm</h5>
                        <p class="card-text"><?php echo htmlspecialchars($stats['products']); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Người dùng</h5>
                        <p class="card-text"><?php echo htmlspecialchars($stats['users']); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Đơn hàng</h5>
                        <p class="card-text"><?php echo htmlspecialchars($stats['admin_total_orders']); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Doanh thu</h5>
                        <p class="card-text"><?php echo number_format($stats['admin_total_revenue'], 0, ',', '.'); ?> VNĐ</p>
                    </div>
                </div>
            </div>
        </div>
        <!-- Revenue Chart -->
        <div class="card mb-4">
            <div class="card-body p-4">
                <h5 class="card-title fw-bold">Doanh thu</h5>
                <div class="mb-3">
                    <select id="revenuePeriod" class="form-select w-auto">
                        <option value="day">Theo ngày (30 ngày gần nhất)</option>
                        <option value="month">Theo tháng (12 tháng gần nhất)</option>
                        <option value="year">Theo năm (5 năm gần nhất)</option>
                    </select>
                </div>
                <canvas id="revenueChart"></canvas>
            </div>
        </div>
        <!-- Top Selling Products -->
        <!-- <div class="card mb-4">
            <div class="card-body p-4">
                <h5 class="card-title fw-bold">Top 10 sản phẩm bán chạy</h5>
                <div class="table-responsive">
                    <table class="table table-hover table-borderless">
                        <thead>
                            <tr>
                                <th>Hình ảnh</th>
                                <th>Tên sản phẩm</th>
                                <th>Danh mục</th>
                                <th>Người bán</th>
                                <th>Số lượng bán</th>
                                <th>Doanh thu (VNĐ)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($topProducts)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4">Không có sản phẩm bán chạy!</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($topProducts as $product): ?>
                                    <tr>
                                        <td>
                                            <img src="<?php echo htmlspecialchars($product['image'] ? ($product['is_partner_paid'] == 1 ? '/Uploads/partners/' . $product['image'] : '/Uploads/' . $product['image']) : '/assets/images/default-product.jpg'); ?>"
                                                 class="product-img" alt="<?php echo htmlspecialchars($product['title']); ?>">
                                        </td>
                                        <td><a href="/products/<?php echo $product['id']; ?>" class="text-decoration-none"><?php echo htmlspecialchars($product['title']); ?></a></td>
                                        <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                                        <td><?php echo htmlspecialchars($product['seller_name']); ?></td>
                                        <td><?php echo number_format($product['total_sold'], 0); ?></td>
                                        <td><?php echo number_format($product['total_revenue'], 0, ',', '.'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div> -->
        <!-- Top Selling Categories -->
        <div class="card mb-4">
            <div class="card-body p-4">
                <h5 class="card-title fw-bold">Top 5 danh mục bán chạy</h5>
                <div class="table-responsive">
                    <table class="table table-hover table-borderless">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Tên danh mục</th>
                                <th>Số lượng bán</th>
                                <th>Doanh thu (VNĐ)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($topCategories)): ?>
                                <tr>
                                    <td colspan="4" class="text-center py-4">Không có danh mục bán chạy!</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($topCategories as $category): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($category['id']); ?></td>
                                        <td><?php echo htmlspecialchars($category['category_name']); ?></td>
                                        <td><?php echo number_format($category['total_sold'], 0); ?></td>
                                        <td><?php echo number_format($category['total_revenue'], 0, ',', '.'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!-- Top Sellers -->
        <div class="card mb-4">
            <div class="card-body p-4">
               <h5 class="card-title fw-bold text-success">Top 10 người bán tiềm năng</h5>
<div class="table-responsive">
    <table class="table table-hover table-borderless">
        <thead>
            <tr>
                <th>ID</th>
                <th>Tên người bán</th>
                <th>Doanh thu 30 ngày (VNĐ)</th>
                <th>Số đơn hoàn thành</th>
                <th>Đánh giá TB</th>
                <th>Tỷ lệ hủy</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($potentialSellers)): ?>
                <tr><td colspan="6" class="text-center text-muted">Chưa có người bán nào đạt tiêu chí</td></tr>
            <?php else: foreach ($potentialSellers as $s): ?>
                <tr class="table-success">
                    <td><?= $s['id'] ?></td>
                    <td><strong><?= htmlspecialchars($s['username']) ?></strong></td>
                    <td class="text-success fw-bold"><?= number_format($s['revenue_30d']) ?></td>
                    <td><?= $s['total_delivered'] ?></td>
                    <td><span class="badge bg-success"><?= number_format($s['avg_rating'], 1) ?> ★</span></td>
                    <td><?= number_format($s['cancel_rate'], 1) ?>%</td>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>
            </div>
        </div>
       <!-- Violating Sellers -->
<div class="card mb-4">
    <div class="card-body p-4">
        <h5 class="card-title fw-bold">Người bán vi phạm (cần xử lý)</h5>
        <div class="table-responsive">
            <table class="table table-hover table-borderless align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Người bán</th>
                        <th>Đánh giá TB</th>
                        <th>Số đơn hủy</th>
                        <th>Tỷ lệ hủy (%)</th>
                        <th>Số báo cáo</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($violatingSellers)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-success">
                                Không có người bán nào vi phạm
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($violatingSellers as $seller): ?>
                            <tr class="<?= $seller['reports_count'] >= 3 || $seller['cancellations'] >= 5 ? 'table-danger' : 'table-warning' ?>">
                                <td><?= htmlspecialchars($seller['id']) ?></td>
                                <td>
                                    <a href="/admin/users/view/<?= $seller['id'] ?>" class="text-decoration-none fw-500">
                                        <?= htmlspecialchars($seller['username']) ?>
                                    </a>
                                </td>
                                <td>
                                    <span class="badge <?= $seller['avg_rating'] < 4.0 ? 'bg-danger' : 'bg-warning' ?>">
                                        <?= number_format($seller['avg_rating'], 1) ?> ★
                                    </span>
                                </td>
                                <td class="text-danger fw-bold"><?= $seller['cancellations'] ?></td>
                                <td><?= $seller['cancel_rate'] ?>%</td>
                                <td class="text-danger fw-bold"><?= $seller['reports_count'] ?></td>
                                <td>
                                    <a href="/admin/users/view/<?= $seller['id'] ?>" class="btn btn-sm btn-outline-primary">
                                        Xem chi tiết
                                    </a>
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
            <?php if ($success = \App\Helpers\Session::get('success')): ?>
                Swal.fire({
                    icon: 'success',
                    title: 'Thành công',
                    text: '<?php echo htmlspecialchars($success); ?>',
                    confirmButtonText: 'OK'
                });
                <?php \App\Helpers\Session::unset('success'); ?>
            <?php endif; ?>
            <?php if ($error = \App\Helpers\Session::get('error')): ?>
                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi',
                    text: '<?php echo htmlspecialchars($error); ?>',
                    confirmButtonText: 'OK'
                });
                <?php \App\Helpers\Session::unset('error'); ?>
            <?php endif; ?>

            // Revenue Chart
            const revenueData = {
                day: <?php echo json_encode(array_reverse($revenueByDay)); ?>,
                month: <?php echo json_encode(array_reverse($revenueByMonth)); ?>,
                year: <?php echo json_encode(array_reverse($revenueByYear)); ?>
            };

            const ctx = document.getElementById('revenueChart').getContext('2d');
            let revenueChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: revenueData.day.map(item => item.period),
                    datasets: [{
                        label: 'Doanh thu (VNĐ)',
                        data: revenueData.day.map(item => item.revenue),
                        borderColor: '#007bff',
                        backgroundColor: 'rgba(0, 123, 255, 0.1)',
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return value.toLocaleString('vi-VN') + ' VNĐ';
                                }
                            }
                        }
                    }
                }
            });

            document.getElementById('revenuePeriod').addEventListener('change', function() {
                const period = this.value;
                revenueChart.data.labels = revenueData[period].map(item => item.period);
                revenueChart.data.datasets[0].data = revenueData[period].map(item => item.revenue);
                revenueChart.update();
            });
        });
    </script>
</body>

</html>