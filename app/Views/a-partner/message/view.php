<?php

use App\Helpers\Session;
use App\Models\User;

$userModel = new User();
$seller = $userModel->findById($product['seller_id']);
$product['is_partner_paid'] = $seller['is_partner_paid'] ?? 0;

if (!$product || !$product_name) {
    Session::set('error', 'Sản phẩm không tồn tại!');
    header('Location: /partners/message');
    exit;
}
?>

<?php require_once __DIR__ . '/../layouts/navbar.php'; ?>

<!-- [Page specific CSS] -->
<link rel="stylesheet" href="/assets2/css/plugins/dataTables.bootstrap5.min.css">
<!-- [Google Font] Family -->
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700&display=swap" id="main-font-link">
<!-- [Tabler Icons] -->
<link rel="stylesheet" href="/assets2/fonts/tabler-icons.min.css">
<!-- [Feather Icons] -->
<link rel="stylesheet" href="/assets2/fonts/feather.css">
<!-- [Font Awesome Icons] -->
<link rel="stylesheet" href="/assets2/fonts/fontawesome.css">
<!-- [Material Icons] -->
<link rel="stylesheet" href="/assets2/fonts/material.css">
<!-- [Template CSS Files] -->
<link rel="stylesheet" href="/assets2/css/style.css" id="main-style-link">
<link rel="stylesheet" href="/assets2/css/style-preset.css" id="preset-style-link">

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
                <li class="dropdown pc-h-item header-user-profile">
                    <a class="pc-head-link dropdown-toggle arrow-none me-0" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" data-bs-auto-close="outside" aria-expanded="false">
                        <img src="<?php echo htmlspecialchars($user['avatar'] ?? '/assets/images/user/avatar-2.jpg'); ?>" alt="user-image" class="user-avtar">
                        <span><?php echo htmlspecialchars($user['username']); ?></span>
                    </a>
                    <div class="dropdown-menu dropdown-user-profile dropdown-menu-end pc-h-dropdown">
                        <div class="dropdown-header">
                            <div class="d-flex mb-1">
                                <div class="flex-shrink-0">
                                    <img src="<?php echo htmlspecialchars($user['avatar'] ?? '/assets/images/user/avatar-2.jpg'); ?>" alt="user-image" class="user-avtar wid-35">
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-1"><?php echo htmlspecialchars($user['username']); ?></h6>
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
                            <h5 class="m-b-10">
                                Chat với <?php echo htmlspecialchars($messages[0]['sender_name'] ?? $messages[0]['receiver_name'] ?? 'Người dùng'); ?> -
                                <span class="fst-italic"><?php echo htmlspecialchars($product_name); ?></span>
                            </h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">

                            <img src="<?php echo htmlspecialchars($product['image']
                                            ? '/uploads/partners/' . $product['image']
                                            : '/assets/images/default-product.jpg'); ?>"
                                alt="product" width="50">
                            <a href="/products/<?php echo $product['id']; ?>" class="btn btn-sm btn-outline-primary ms-auto">
                                Xem sản phẩm
                            </a>
                        </div>
                        <div id="chat-messages" class="mb-3" style="height: 450px; overflow-y: auto; background-color: #f9f9fb; border-radius: 10px; padding: 1rem; display: flex; flex-direction: column;">
                            <?php foreach ($messages as $msg): ?>
                                <?php
                                $isSender = $msg['sender_id'] == $user['id'];
                                $class = $isSender ? 'bg-primary text-white ms-auto' : 'bg-light text-dark me-auto';
                                ?>
                                <div class="p-2 mb-2 rounded-3 <?php echo $class; ?>" style="max-width: 75%;">
                                    <strong><?php echo htmlspecialchars($isSender ? 'Bạn' : ($msg['sender_name'] ?? 'Người dùng')); ?>:</strong>
                                    <?php echo htmlspecialchars($msg['message']); ?>
                                    <small class="d-block mt-1 text-muted"><?php echo date('d/m/Y H:i', strtotime($msg['created_at'])); ?></small>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="d-flex align-items-center">
                            <input type="text" id="chat-input" class="form-control me-2" placeholder="Nhập tin nhắn...">
                            <button class="btn btn-primary" id="send-message">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="/assets2/js/plugins/popper.min.js"></script>
<script src="/assets2/js/plugins/simplebar.min.js"></script>
<script src="/assets2/js/plugins/bootstrap.min.js"></script>
<script src="/assets2/js/fonts/custom-font.js"></script>
<script src="/assets2/js/pcoded.js"></script>
<script src="/assets2/js/plugins/feather.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        const productId = <?php echo json_encode($product['id']); ?>;
        const userId = <?php echo json_encode($user['id']); ?>;
        const receiverId = <?php echo json_encode($receiverId); ?>;
        let ws = null;
        let retryCount = 0;

        if (!userId) {
            Swal.fire({
                icon: 'error',
                title: 'Lỗi',
                text: 'Vui lòng đăng nhập để sử dụng chat!',
                timer: 2000,
                showConfirmButton: false
            }).then(() => {
                window.location.href = '/login';
            });
            return;
        }

        function connectWebSocket() {
            if (ws && ws.readyState === WebSocket.OPEN) {
                ws.close();
            }
            ws = new WebSocket('ws://localhost:9000?user_id=' + userId);
            ws.onopen = function() {
                console.log('WebSocket connected');
                retryCount = 0;
            };
            ws.onmessage = function(event) {
                console.log('WebSocket message received:', event.data);
                try {
                    const data = JSON.parse(event.data);
                    if (data.type === 'chat' && Number(data.product_id) === Number(productId) &&
                        Number(data.sender_id) === Number(receiverId) && Number(data.receiver_id) === Number(userId)) {
                        const className = 'bg-light text-dark me-auto';
                        const label = data.sender_name || 'Người dùng';
                        $('#chat-messages').append(
                            `<div class="p-2 mb-2 rounded-3 ${className}" style="max-width: 75%;">
                                <strong>${label}:</strong> ${data.message}
                                <small class="d-block mt-1 text-muted">${new Date(data.timestamp).toLocaleString('vi-VN')}</small>
                            </div>`
                        );
                        scrollToBottom();

                    }
                } catch (e) {
                    console.error('Error processing WebSocket message:', e);
                }
            };
            ws.onclose = function() {
                console.log('WebSocket closed, retrying...');
                const delay = Math.min(1000 * Math.pow(2, retryCount), 30000);
                setTimeout(() => connectWebSocket(), delay);
                retryCount++;
            };
            ws.onerror = function(error) {
                console.error('WebSocket error:', error);
            };
        }
        connectWebSocket();

        function scrollToBottom() {
            const chatMessages = $('#chat-messages');
            chatMessages.scrollTop(chatMessages[0].scrollHeight);
        }

        scrollToBottom();

        $('#send-message').on('click', function() {
            const message = $('#chat-input').val().trim();
            if (message && ws && ws.readyState === WebSocket.OPEN) {
                $.ajax({
                    url: '/partners/message/send',
                    method: 'POST',
                    data: {
                        receiver_id: receiverId,
                        product_id: productId,
                        message: message
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#chat-messages').append(
                                `<div class="p-2 mb-2 rounded-3 bg-primary text-white ms-auto" style="max-width: 75%;">
                                    <strong>Bạn:</strong> ${message}
                                    <small class="d-block mt-1 text-muted">${new Date(response.timestamp).toLocaleString('vi-VN')}</small>
                                </div>`
                            );
                            scrollToBottom();
                            $('#chat-input').val('');
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Lỗi',
                                text: response.message || 'Gửi tin nhắn thất bại!',
                                timer: 2000,
                                showConfirmButton: false
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Lỗi',
                            text: 'Lỗi kết nối server!',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    }
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi',
                    text: 'Không thể gửi tin nhắn: Kết nối WebSocket không hoạt động hoặc tin nhắn trống!',
                    timer: 2000,
                    showConfirmButton: false
                });
            }
        });

        $('#chat-input').on('keypress', function(e) {
            if (e.which === 13 && $(this).val().trim()) {
                $('#send-message').click();
            }
        });
    });
</script>