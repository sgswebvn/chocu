<?php
require_once __DIR__ . '/layouts/header.php';
require_once __DIR__ . '/layouts/navbar.php';
?>

<header class="pc-header">
    <div class="header-wrapper">
        <div class="me-auto pc-mob-drp">
            <ul class="list-unstyled">
                <li class="pc-h-item pc-sidebar-collapse">
                    <a href="#" class="pc-head-link ms-0" id="sidebar-hide">
                        <i class="ti ti-menu-2"></i>
                    </a>
                </li>
                <li class="pc-h-item pc-sidebar-popup">
                    <a href="#" class="pc-head-link ms-0" id="mobile-collapse">
                        <i class="ti ti-menu-2"></i>
                    </a>
                </li>
            </ul>
        </div>
        <div class="ms-auto">
            <ul class="list-unstyled">
                <li class="dropdown pc-h-item">
                    <a class="pc-head-link dropdown-toggle arrow-none me-0" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                        <i class="ti ti-bell"></i>
                        <span class="badge bg-success pc-h-badge" id="notify-count"><?php echo $this->notificationModel->getUnreadCount($data['user']['id']); ?></span>
                    </a>
                    <div class="dropdown-menu dropdown-notification dropdown-menu-end pc-h-dropdown p-0">
                        <div class="dropdown-header d-flex align-items-center justify-content-between p-3 border-bottom">
                            <h5 class="m-0 fw-semibold">Thông báo</h5>
                            <a href="/notifications/mark-all-read" class="pc-head-link text-success bg-transparent"><i class="ti ti-circle-check"></i></a>
                        </div>
                        <div class="notification-content overflow-auto" style="min-height: 200px; max-height: 60vh;">
                            <div class="list-group list-group-flush w-100" id="notify-list">
                                <?php if (empty($data['notifications'])): ?>
                                    <div class="text-center p-3 text-muted">Không có thông báo</div>
                                <?php else: ?>
                                    <?php foreach ($data['notifications'] as $notification): ?>
                                        <div class="notify-item list-group-item <?php echo $notification['is_read'] ? '' : 'unread'; ?>" data-id="<?php echo $notification['id']; ?>">
                                            <a href="<?php echo $notification['link'] ?: '#'; ?>" class="text-decoration-none text-dark">
                                                <div class="d-flex w-100 justify-content-between">
                                                    <div>
                                                        <div class="notification-title fw-bold"><?php echo htmlspecialchars($notification['title']); ?></div>
                                                        <div class="notification-body text-muted small"><?php echo htmlspecialchars($notification['message']); ?></div>
                                                    </div>
                                                    <div class="notification-time text-muted small text-end"><?php echo date('d/m/Y H:i', strtotime($notification['created_at'])); ?></div>
                                                </div>
                                            </a>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="dropdown-footer p-2 border-top">
                            <a href="/notifications" class="btn btn-primary w-100 text-white">Xem tất cả</a>
                        </div>
                    </div>
                </li>
                <li class="dropdown pc-h-item header-user-profile">
                    <a class="pc-head-link dropdown-toggle arrow-none me-0" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" data-bs-auto-close="outside" aria-expanded="false">
                        <?php
                        $avatar = !empty($data['user']['images'])
                            ? '/uploads/partners/' . htmlspecialchars($data['user']['images']) . '?t=' . time()
                            : '/assets/images/user/avatar-2.jpg';
                        ?>
                        <img src="<?php echo $avatar; ?>" alt="user-image" class="user-avtar wid-35">
                        <span><?php echo htmlspecialchars($data['user']['username']); ?></span>
                    </a>
                    <div class="dropdown-menu dropdown-user-profile dropdown-menu-end pc-h-dropdown">
                        <div class="dropdown-header">
                            <div class="d-flex mb-1">
                                <div class="flex-shrink-0">
                                    <img src="<?php echo $avatar; ?>" alt="user-image" class="user-avtar wid-35">
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-1"><?php echo htmlspecialchars($data['user']['username']); ?></h6>
                                    <span>Đối tác</span>
                                </div>
                                <a href="/logout" class="pc-head-link bg-transparent"><i class="ti ti-power text-danger"></i></a>
                            </div>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</header>

<div class="pc-container">
    <div class="pc-content">
        <div class="page-header">
            <div class="page-block">
                <div class="row align-items-center">
                    <div class="col-md-12">
                        <div class="page-header-title">
                            <h5 class="m-b-10">Dashboard Đối tác</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 col-xl-3">
                <div class="card">
                    <div class="card-body">
                        <h6 class="mb-2 f-w-400 text-muted">Tổng sản phẩm</h6>
                        <h4 class="mb-3"><?php echo $data['total_products']; ?> <span class="badge bg-light-primary border border-primary"><i class="ti ti-package"></i></span></h4>
                        <p class="mb-0 text-muted text-sm">Sản phẩm bạn đang quản lý</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card">
                    <div class="card-body">
                        <h6 class="mb-2 f-w-400 text-muted">Tổng giao dịch</h6>
                        <h4 class="mb-3"><?php echo $data['total_transactions']; ?> <span class="badge bg-light-success border border-success"><i class="ti ti-history"></i></span></h4>
                        <p class="mb-0 text-muted text-sm">Giao dịch đã thực hiện</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card">
                    <div class="card-body">
                        <h6 class="mb-2 f-w-400 text-muted">Tổng đánh giá</h6>
                        <h4 class="mb-3"><?php echo $data['total_reviews']; ?> <span class="badge bg-light-warning border border-warning"><i class="ti ti-star"></i></span></h4>
                        <p class="mb-0 text-muted text-sm">Đánh giá từ khách hàng</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card">
                    <div class="card-body">
                        <h6 class="mb-2 f-w-400 text-muted">Tỷ lệ hủy đơn</h6>
                        <h4 class="mb-3"><?php echo number_format($data['cancellation_rate'], 2); ?>% <span class="badge bg-light-danger border border-danger"><i class="ti ti-x"></i></span></h4>
                        <p class="mb-0 text-muted text-sm">Tỷ lệ đơn hàng bị hủy</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card">
                    <div class="card-body">
                        <h6 class="mb-2 f-w-400 text-muted">Tổng doanh thu</h6>
                        <h4 class="mb-3"><?php echo number_format($data['total_revenue'], 0, ',', '.'); ?> VNĐ <span class="badge bg-light-success border border-success"><i class="ti ti-currency-dollar"></i></span></h4>
                        <p class="mb-0 text-muted text-sm">Tổng doanh thu từ đơn hàng</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card">
                    <div class="card-body">
                        <h6 class="mb-2 f-w-400 text-muted">Đơn đã hoàn thành</h6>
                        <h4 class="mb-3"><?php echo number_format($data['completed_orders'], 0, ',', '.'); ?> <span class="badge bg-light-primary border border-primary"><i class="ti ti-check"></i></span></h4>
                        <p class="mb-0 text-muted text-sm">Số đơn hàng đã giao thành công</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card">
                    <div class="card-body">
                        <h6 class="mb-2 f-w-400 text-muted">Tổng lượt xem gian hàng</h6>
                        <h4 class="mb-3"><?php echo number_format($data['store_views'], 0, ',', '.'); ?> <span class="badge bg-light-info border border-info"><i class="ti ti-eye"></i></span></h4>
                        <p class="mb-0 text-muted text-sm">Tổng lượt xem gian hàng của bạn</p>
                    </div>
                </div>
            </div>

            <div class="col-md-12 col-xl-6">
                <h5 class="mb-3">Hồ sơ đối tác</h5>
                <div class="card">
                    <div class="card-body">
                        <h6 class="mb-2 f-w-400 text-muted">Thông tin cá nhân</h6>
                        <form action="/partners/update" method="POST">
                            <div class="mb-3">
                                <label for="username" class="form-label">Tên người dùng</label>
                                <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($data['user']['username']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($data['user']['email']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Vai trò</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($data['user']['role']); ?>" disabled>
                            </div>
                            <button type="submit" class="btn btn-primary">Cập nhật hồ sơ</button>
                            <a href="/store/<?php echo $data['user']['id']; ?>" class="btn btn-success">Xem gian hàng</a>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-12 col-xl-6">
                <h5 class="mb-3">Top sản phẩm bán chạy</h5>
                <div class="card tbl-card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-borderless mb-0">
                                <thead>
                                    <tr>
                                        <th>Sản phẩm</th>
                                        <th>Số lượng bán</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($data['top_selling_products'])): ?>
                                        <tr>
                                            <td colspan="2" class="text-center">Chưa có dữ liệu</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($data['top_selling_products'] as $product): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($product['title']); ?></td>
                                                <td><?php echo number_format($product['total_quantity'], 0, ',', '.'); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-12 col-xl-6">
                <h5 class="mb-3">Doanh thu theo sản phẩm</h5>
                <div class="card tbl-card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-borderless mb-0">
                                <thead>
                                    <tr>
                                        <th>Sản phẩm</th>
                                        <th>Doanh thu</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($data['revenue_by_product'])): ?>
                                        <tr>
                                            <td colspan="2" class="text-center">Chưa có dữ liệu</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($data['revenue_by_product'] as $product): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($product['title']); ?></td>
                                                <td><?php echo number_format($product['total_revenue'], 0, ',', '.'); ?> VNĐ</td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-12 col-xl-6">
                <h5 class="mb-3">Doanh thu theo nhóm sản phẩm</h5>
                <div class="card tbl-card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-borderless mb-0">
                                <thead>
                                    <tr>
                                        <th>Nhóm sản phẩm</th>
                                        <th>Doanh thu</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($data['revenue_by_category'])): ?>
                                        <tr>
                                            <td colspan="2" class="text-center">Chưa có dữ liệu</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($data['revenue_by_category'] as $category): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($category['category_name']); ?></td>
                                                <td><?php echo number_format($category['total_revenue'], 0, ',', '.'); ?> VNĐ</td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-12">
                <h5 class="mb-3">Biểu đồ doanh thu</h5>
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <h6>Doanh thu theo ngày (7 ngày gần nhất)</h6>
                                <canvas id="dailyRevenueChart"></canvas>
                            </div>
                            <div class="col-md-4">
                                <h6>Doanh thu theo tháng (12 tháng gần nhất)</h6>
                                <canvas id="monthlyRevenueChart"></canvas>
                            </div>
                            <div class="col-md-4">
                                <h6>Doanh thu theo năm (5 năm gần nhất)</h6>
                                <canvas id="yearlyRevenueChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-12">
                <h5 class="mb-3">Dự đoán sản phẩm có tiềm năng</h5>
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <h6>Top 5 sản phẩm tiềm năng</h6>
                                <canvas id="potentialProductsChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tích hợp Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    $(document).ready(function() {
        // Đánh dấu tất cả đã đọc khi nhấp vào nút
        $('a[href="/notifications/mark-all-read"]').on('click', function(e) {
            e.preventDefault(); // Ngăn chặn chuyển hướng
            $.post('/notifications/mark-all-read', {
                user_id: <?php echo $data['user']['id']; ?>
            }, function(response) {
                if (response.success) {
                    // Cập nhật giao diện
                    $('.notify-item.unread').removeClass('unread');
                    $('#notify-count').text('0').hide(); // Ẩn badge nếu không còn thông báo
                    alert('Đã đánh dấu tất cả thông báo là đã đọc!');
                } else {
                    alert('Có lỗi xảy ra khi đánh dấu thông báo.');
                }
            }).fail(function() {
                console.error('Lỗi khi gọi API mark-all-read');
                alert('Có lỗi xảy ra. Vui lòng thử lại.');
            });
        });

        // Đánh dấu từng thông báo đã đọc khi nhấp
        $('.notify-item').on('click', function(e) {
            const link = $(this).find('a').attr('href');
            const id = $(this).data('id');
            if (link !== '#' && id) {
                $.post('/notifications/mark-read', {
                    id: id
                }, function(response) {
                    if (response.success) {
                        $(`.notify-item[data-id="${id}"]`).removeClass('unread');
                        updateNotificationCount();
                    }
                }).fail(function() {
                    console.error('Lỗi khi đánh dấu thông báo đã đọc');
                });
            }
        });

        // Cập nhật số lượng thông báo chưa đọc
        function updateNotificationCount() {
            $.get('/notifications/unread-count', {
                user_id: <?php echo $data['user']['id']; ?>
            }, function(response) {
                if (response.success) {
                    const count = response.count;
                    $('#notify-count').text(count);
                    if (count == 0) {
                        $('#notify-count').hide();
                    } else {
                        $('#notify-count').show();
                    }
                }
            }).fail(function() {
                console.error('Lỗi khi lấy số lượng thông báo chưa đọc');
            });
        }

        // Gọi lần đầu để đồng bộ số lượng
        updateNotificationCount();
    });
</script>
<script>
    // Biểu đồ doanh thu theo ngày
    const dailyRevenueCtx = document.getElementById('dailyRevenueChart').getContext('2d');
    const dailyRevenueChart = new Chart(dailyRevenueCtx, {
        type: 'bar',
        data: {
            labels: [<?php echo empty($data['revenue_by_period']['daily']) ? "'Không có dữ liệu'" : "'" . implode("','", array_column($data['revenue_by_period']['daily'], 'period')) . "'"; ?>],
            datasets: [{
                label: 'Doanh thu (VNĐ)',
                data: [<?php echo empty($data['revenue_by_period']['daily']) ? "0" : implode(',', array_column($data['revenue_by_period']['daily'], 'total_revenue')); ?>],
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return value.toLocaleString('vi-VN') + ' VNĐ';
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });

    // Biểu đồ doanh thu theo tháng
    const monthlyRevenueCtx = document.getElementById('monthlyRevenueChart').getContext('2d');
    const monthlyRevenueChart = new Chart(monthlyRevenueCtx, {
        type: 'bar',
        data: {
            labels: [<?php echo empty($data['revenue_by_period']['monthly']) ? "'Không có dữ liệu'" : "'" . implode("','", array_column($data['revenue_by_period']['monthly'], 'period')) . "'"; ?>],
            datasets: [{
                label: 'Doanh thu (VNĐ)',
                data: [<?php echo empty($data['revenue_by_period']['monthly']) ? "0" : implode(',', array_column($data['revenue_by_period']['monthly'], 'total_revenue')); ?>],
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return value.toLocaleString('vi-VN') + ' VNĐ';
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });

    // Biểu đồ doanh thu theo năm
    const yearlyRevenueCtx = document.getElementById('yearlyRevenueChart').getContext('2d');
    const yearlyRevenueChart = new Chart(yearlyRevenueCtx, {
        type: 'bar',
        data: {
            labels: [<?php echo empty($data['revenue_by_period']['yearly']) ? "'Không có dữ liệu'" : "'" . implode("','", array_column($data['revenue_by_period']['yearly'], 'period')) . "'"; ?>],
            datasets: [{
                label: 'Doanh thu (VNĐ)',
                data: [<?php echo empty($data['revenue_by_period']['yearly']) ? "0" : implode(',', array_column($data['revenue_by_period']['yearly'], 'total_revenue')); ?>],
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                borderColor: 'rgba(255, 99, 132, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return value.toLocaleString('vi-VN') + ' VNĐ';
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });

    // Biểu đồ dự đoán sản phẩm có tiềm năng
    const potentialProductsCtx = document.getElementById('potentialProductsChart').getContext('2d');
    const potentialProductsChart = new Chart(potentialProductsCtx, {
        type: 'bar',
        data: {
            labels: [<?php echo empty($data['potential_products']) ? "'Không có dữ liệu'" : "'" . implode("','", array_column($data['potential_products'], 'title')) . "'"; ?>],
            datasets: [{
                label: 'Điểm tiềm năng',
                data: [<?php echo empty($data['potential_products']) ? "0" : implode(',', array_column($data['potential_products'], 'potential_score')); ?>],
                backgroundColor: 'rgba(153, 102, 255, 0.2)',
                borderColor: 'rgba(153, 102, 255, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    ticks: {
                        callback: function(value) {
                            return value + '%';
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.label + ': ' + context.raw + '%';
                        }
                    }
                }
            }
        }
    });
</script>

<?php
require_once __DIR__ . '/layouts/footer.php';
?>