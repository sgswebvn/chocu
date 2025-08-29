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

        @media (max-width: 576px) {
            .card-text {
                font-size: 2rem;
            }

            .table-responsive {
                font-size: 0.85rem;
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
                        <p class="card-text"><?php echo htmlspecialchars($stats['orders']); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Doanh thu</h5>
                        <p class="card-text"><?php echo number_format($stats['revenue'], 0, ',', '.'); ?> VNĐ</p>
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
        <!-- Top Sellers -->
        <div class="card mb-4">
            <div class="card-body p-4">
                <h5 class="card-title fw-bold">Top 10 người bán tiềm năng</h5>
                <div class="table-responsive">
                    <table class="table table-hover table-borderless">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Tên người bán</th>
                                <th>Doanh thu (VNĐ)</th>
                                <th>Tăng trưởng doanh thu (%)</th>
                                <th>Đánh giá trung bình</th>
                                <th>Tỷ lệ hủy đơn (%)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($topSellers)): ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4">Không có người bán nào!</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($topSellers as $seller): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($seller['id']); ?></td>
                                        <td><?php echo htmlspecialchars($seller['username']); ?></td>
                                        <td><?php echo number_format($seller['revenue'], 0, ',', '.'); ?></td>
                                        <td><?php echo number_format($seller['revenue_growth'], 2); ?></td>
                                        <td><?php echo number_format($seller['avg_rating'], 1); ?></td>
                                        <td><?php echo number_format($seller['cancel_rate'], 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!-- Violating Sellers -->
        <!-- Violating Sellers -->
        <div class="card">
            <div class="card-body p-4">
                <h5 class="card-title fw-bold">Người bán vi phạm</h5>
                <div class="table-responsive">
                    <table class="table table-hover table-borderless">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Tên người bán</th>
                                <th>Đánh giá trung bình</th>
                                <th>Số đơn hủy</th>
                                <th>Số báo cáo</th>
                                <th>Tỷ lệ report (%)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($violatingSellers)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4">Không có người bán vi phạm!</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($violatingSellers as $seller): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($seller['id']); ?></td>
                                        <td><?php echo htmlspecialchars($seller['username']); ?></td>
                                        <td><?php echo number_format($seller['avg_rating'], 1); ?></td>
                                        <td><?php echo htmlspecialchars($seller['cancellations']); ?></td>
                                        <td><?php echo htmlspecialchars($seller['reports']); ?></td>
                                        <td><?php echo number_format($seller['report_rate'], 2); ?></td>
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