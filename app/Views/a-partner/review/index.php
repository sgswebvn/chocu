<?php

use App\Helpers\Session;

require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';

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

    </div>
</header>

<div class="pc-container">
    <div class="pc-content">
        <div class="page-header">
            <div class="page-block">
                <div class="row align-items-center">
                    <div class="col-md-12">
                        <div class="page-header-title">
                            <h5 class="m-b-10">Quản lý đánh giá</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <h5>Danh sách đánh giá</h5>
                        <div class="table-responsive">
                            <table class="table table-hover table-borderless mb-0">
                                <thead>
                                    <tr>
                                        <th>SẢN PHẨM</th>
                                        <th>ĐÁNH GIÁ</th>
                                        <th>BÌNH LUẬN</th>
                                        <th>TRẢ LỜI</th>
                                        <th>NGÀY</th>
                                        <th>HÀNH ĐỘNG</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($reviews as $review): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($review['username']); ?></td>
                                            <td><?php echo $review['rating']; ?>/5</td>
                                            <td><?php echo htmlspecialchars($review['comment']); ?></td>
                                            <td><?php echo htmlspecialchars($review['reply'] ?? 'Chưa trả lời'); ?></td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($review['created_at'])); ?></td>

                                            <td>
                                                <a href="/store/<?php echo $review['id']; ?>" class="btn btn-sm btn-info">Xem chi tiết</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../layouts/footer.php';
?>