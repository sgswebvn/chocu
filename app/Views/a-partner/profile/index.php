<?php
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
                            <h5 class="m-b-10">Hồ sơ cá nhân</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 col-xl-6">
                <div class="card">
                    <div class="card-body">
                        <h6 class="mb-2 f-w-400 text-muted">Thông tin cá nhân</h6>
                        <form action="/partners/profile" method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="username" class="form-label">Tên người dùng</label>
                                <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($data['user']['username']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($data['user']['email']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label d-block">Ảnh đại diện</label>
                                <div class="position-relative d-inline-block">
                                    <img id="avatar-preview"
                                        src="<?php echo $avatar; ?>"
                                        alt="avatar hiện tại"
                                        class="rounded-circle border"
                                        style="width: 100px; height: 100px; object-fit: cover; cursor: pointer;">
                                    <label for="image" class="position-absolute bottom-0 end-0 bg-dark text-white rounded-circle p-1"
                                        style="cursor: pointer;">
                                        <i class="ti ti-camera"></i>
                                    </label>
                                </div>
                                <input type="file" id="image" name="image" accept="image/*" hidden>
                                <small class="form-text text-muted d-block mt-2">Chỉ chấp nhận file JPG, JPEG, PNG, GIF.</small>
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
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    $(document).ready(function() {
        // Đánh dấu tất cả đã đọc khi nhấp vào nút
        $('a[href="/notifications/mark-all-read"]').on('click', function(e) {
            e.preventDefault();
            $.post('/notifications/mark-all-read', {
                user_id: <?php echo $data['user']['id']; ?>
            }, function(response) {
                if (response.success) {
                    $('.notify-item.unread').removeClass('unread');
                    $('#notify-count').text('0').hide();
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

        // Xem trước ảnh đại diện khi chọn file
        $('#image').on('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#avatar-preview').attr('src', e.target.result);
                };
                reader.readAsDataURL(file);
            }
        });
    });
</script>

<?php
require_once __DIR__ . '/../layouts/footer.php';
?>