<?php

use App\Helpers\Session;

$currentUserId = Session::get('user')['id'] ?? null;
$userName = Session::get('user')['name'] ?? 'Đối tác';
?>

<nav class="pc-sidebar">
    <div class="navbar-wrapper">
        <div class="m-header">
            <a href="/partners" class="b-brand text-primary">
                <h2>Dashboard</h2>
            </a>
        </div>
        <div class="navbar-content">
            <ul class="pc-navbar">
                <!-- Tổng quan -->
                <li class="pc-item">
                    <a href="/partners" class="pc-link">
                        <span class="pc-micon"><i class="ti ti-layout-dashboard"></i></span>
                        <span class="pc-mtext">Quản lý</span>
                    </a>
                </li>

                <!-- Caption -->
                <li class="pc-item pc-caption">
                    <label>Chức năng chính</label>
                    <i class="ti ti-apps"></i>
                </li>

                <!-- Sản phẩm -->
                <li class="pc-item">
                    <a href="/partners/product" class="pc-link">
                        <span class="pc-micon"><i class="ti ti-package"></i></span>
                        <span class="pc-mtext">Quản lý sản phẩm</span>
                    </a>
                </li>
                <li class="pc-item">
                    <a href="/partners/orders" class="pc-link">
                        <span class="pc-micon"><i class="ti ti-package"></i></span>
                        <span class="pc-mtext">Quản lý đơn hàng</span>
                    </a>
                </li>

                <!-- Tin nhắn -->
                <li class="pc-item">
                    <a href="/partners/message" class="pc-link">
                        <span class="pc-micon"><i class="ti ti-message-circle"></i></span>
                        <span class="pc-mtext">Quản lý tin nhắn</span>
                    </a>
                </li>

                <!-- Đánh giá -->
                <li class="pc-item">
                    <a href="/partners/review" class="pc-link">
                        <span class="pc-micon"><i class="ti ti-star"></i></span>
                        <span class="pc-mtext">Quản lý đánh giá shop</span>
                    </a>
                </li>

                <!-- Giao dịch -->
                <li class="pc-item">
                    <a href="/partners/transactions" class="pc-link">
                        <span class="pc-micon"><i class="ti ti-history"></i></span>
                        <span class="pc-mtext">Lịch sử giao dịch</span>
                    </a>
                </li>

                <!-- Caption -->
                <li class="pc-item pc-caption">
                    <label>Khác</label>
                    <i class="ti ti-settings"></i>
                </li>

                <!-- Xem gian hàng -->
                <li class="pc-item">
                    <a href="/store/<?php echo $currentUserId; ?>" class="pc-link" target="_blank">
                        <span class="pc-micon"><i class="ti ti-eye"></i></span>
                        <span class="pc-mtext">Xem gian hàng</span>
                    </a>
                </li>
                <li class="pc-item">
                    <a href="/partners/profile" class="pc-link">
                        <span class="pc-micon"><i class="ti ti-user"></i></span>
                        <span class="pc-mtext">Thông tin cá nhân </span>
                    </a>
                </li>

                <!-- Đăng xuất -->
                <li class="pc-item">
                    <a href="/logout" class="pc-link">
                        <span class="pc-micon"><i class="ti ti-logout"></i></span>
                        <span class="pc-mtext">Đăng xuất</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>