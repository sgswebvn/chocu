<?php

use App\Helpers\Session;
use App\Models\Notification;

Session::start();

// Check if user is admin 
$user = Session::get('user');
if (!$user || $user['role'] !== 'admin') {
    Session::set('error', 'Vui lòng đăng nhập với tài khoản admin!');
    header('Location: /login');
    exit;
}

$currentUserId = $user['id'] ?? null;
$notificationModel = new Notification();
$notifications = $currentUserId ? $notificationModel->getByUser($currentUserId, 10, 0) : [];
$unreadCount = $currentUserId ? $notificationModel->getUnreadCount($currentUserId) : 0;
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top shadow-sm">
    <div class="container-fluid">
        <a class="navbar-brand ms-3 fw-bold" href="/admin">C2C Admin</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarOffcanvas" aria-controls="sidebarOffcanvas">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="ms-auto d-flex align-items-center">
            <!-- Dropdown Thông báo -->
            <div class="dropdown me-3">
                <a class="nav-link text-light dropdown-toggle position-relative" href="#" id="notificationDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-bell"></i>
                    <?php if ($unreadCount > 0): ?>
                        <span class="badge bg-danger rounded-pill position-absolute top-0 start-100 translate-middle">
                            <?php echo $unreadCount; ?>
                        </span>
                    <?php endif; ?>
                </a>

                <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="notificationDropdown" style="min-width: 320px; max-height: 400px; overflow-y: auto;">
                    <li class="dropdown-header bg-dark text-light p-2 border-bottom">
                        <h6 class="m-0">Thông báo</h6>
                    </li>

                    <?php if (empty($notifications)): ?>
                        <li class="p-3 text-center text-muted">Không có thông báo</li>
                    <?php else: ?>
                        <?php foreach ($notifications as $notification): ?>
                            <li>
                                <div class="dropdown-item <?php echo $notification['is_read'] ? '' : 'bg-light'; ?>" data-id="<?php echo $notification['id']; ?>">
                                    <a href="<?php echo htmlspecialchars($notification['link'] ?? '#'); ?>" class="text-decoration-none text-dark d-block">
                                        <strong><?php echo htmlspecialchars($notification['title']); ?></strong>
                                        <p class="mb-1 small"><?php echo htmlspecialchars($notification['message']); ?></p>
                                        <small class="text-muted"><?php echo date('d/m/Y H:i', strtotime($notification['created_at'])); ?></small>
                                    </a>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <li class="dropdown-footer bg-light p-2 border-top">
                        <a href="/admin/notifications" class="btn btn-primary btn-sm w-100">Xem tất cả</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>

<!-- Sidebar cho desktop -->
<div class="d-none d-lg-block bg-dark text-white position-fixed h-100 shadow-sm" style="width: 250px; top: 56px; left: 0; z-index: 1030;">
    <div class="p-3">
        <h5 class="fw-bold">Menu Quản lý</h5>
        <ul class="nav flex-column mt-3">
            <li class="nav-item"><a class="nav-link text-light px-3 py-2" href="/admin">Tổng quan</a></li>
            <li class="nav-item"><a class="nav-link text-light px-3 py-2" href="/admin/categories">Danh mục</a></li>
            <li class="nav-item"><a class="nav-link text-light px-3 py-2" href="/admin/products">Sản phẩm</a></li>
            <li class="nav-item"><a class="nav-link text-light px-3 py-2" href="/admin/users">Người dùng</a></li>
            <li class="nav-item"><a class="nav-link text-light px-3 py-2" href="/admin/reports">Báo cáo</a></li>
            <li class="nav-item"><a class="nav-link text-light px-3 py-2" href="/admin/contacts">Liên hệ</a></li>
        </ul>
    </div>
</div>

<!-- Offcanvas cho mobile -->
<div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="sidebarOffcanvas" aria-labelledby="sidebarOffcanvasLabel" style="background-color: #212529;">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title text-white fw-bold" id="sidebarOffcanvasLabel">Menu Quản lý</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <ul class="nav flex-column">
            <li class="nav-item"><a class="nav-link text-light px-3 py-2" href="/admin">Tổng quan</a></li>
            <li class="nav-item"><a class="nav-link text-light px-3 py-2" href="/admin/categories">Danh mục</a></li>
            <li class="nav-item"><a class="nav-link text-light px-3 py-2" href="/admin/products">Sản phẩm</a></li>
            <li class="nav-item"><a class="nav-link text-light px-3 py-2" href="/admin/users">Người dùng</a></li>
            <li class="nav-item"><a class="nav-link text-light px-3 py-2" href="/admin/reports">Báo cáo</a></li>
            <li class="nav-item"><a class="nav-link text-light px-3 py-2" href="/admin/contacts">Liên hệ</a></li>
        </ul>
    </div>
</div>

<!-- CSS -->
<style>
    @media (min-width: 992px) {
        body {
            margin-left: 250px;
        }
    }

    .dropdown-menu {
        z-index: 2000 !important;
        /* ép dropdown nổi lên trên sidebar */
    }

    .dropdown-item:hover {
        background-color: #e9ecef;
    }

    .badge.bg-danger {
        font-size: 0.7rem;
        padding: 0.3rem 0.5rem;
    }
</style>

<!-- Scripts -->
<script>
    $(document).ready(function() {
        // Đánh dấu thông báo đã đọc
        $('.dropdown-item').on('click', function(e) {
            const link = $(this).find('a').attr('href');
            const id = $(this).data('id');
            if (link !== '#' && id) {
                $.post('/notifications/mark-read', {
                    id: id
                }, function(response) {
                    if (response.success) {
                        $(`.dropdown-item[data-id="${id}"]`).removeClass('bg-light');
                        updateNotificationCount();
                    }
                });
            }
        });

        function updateNotificationCount() {
            $.get('/notifications/unread-count', {
                user_id: <?php echo json_encode($currentUserId); ?>
            }, function(response) {
                if (response.success) {
                    const count = response.count;
                    if (count > 0) {
                        $('.badge.bg-danger').text(count).show();
                    } else {
                        $('.badge.bg-danger').hide();
                    }
                }
            });
        }

        // WebSocket nhận thông báo mới
        const userId = <?php echo json_encode($currentUserId); ?>;
        if (userId) {
            let ws = new WebSocket('ws://localhost:9000?user_id=' + userId);
            const notificationSound = new Audio('/assets/sounds/notification.mp3');

            ws.onmessage = function(event) {
                try {
                    const data = JSON.parse(event.data);
                    if (!data || !data.type || !data.title || !data.message) return;

                    const notifyItem = `
                    <li>
                        <div class="dropdown-item bg-light text-dark" data-id="${data.id || ''}">
                            <a href="${data.link || '#'}" class="text-decoration-none text-dark d-block">
                                <strong>${data.title}</strong>
                                <p class="mb-1 small">${data.message}</p>
                                <small class="text-muted">${data.timestamp || new Date().toLocaleString()}</small>
                            </a>
                        </div>
                    </li>
                `;
                    $('.dropdown-menu').prepend(notifyItem);
                    updateNotificationCount();
                    notificationSound.play().catch(err => console.error('Audio error:', err));
                } catch (e) {
                    console.error('Lỗi xử lý WebSocket:', e);
                }
            };
        }
    });
</script>