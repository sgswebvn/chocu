<?php

use App\Helpers\Session;

Session::start();
$currentUserId = Session::get('user')['id'] ?? null;

if (!$currentUserId) {
    Session::set('error', 'Vui lòng đăng nhập để nhận thông báo!');
    header('Location: /login');
    exit;
}
?>

<div id="notification-container" class="position-fixed top-0 end-0 p-3" style="z-index: 1050;"></div>

<style>
    .notification-toast {
        min-width: 300px;
        max-width: 400px;
        padding: 1rem;
        border-radius: 10px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        margin-bottom: 1rem;
        transition: all 0.3s ease-in-out;
        opacity: 0;
        transform: translateX(100%);
        cursor: pointer;
    }

    .notification-toast.show {
        opacity: 1;
        transform: translateX(0);
    }

    .notification-toast.message {
        background-color: #e7f3ff;
        border-left: 5px solid #007bff;
    }

    .notification-toast.order {
        background-color: #e6ffed;
        border-left: 5px solid #28a745;
    }

    .notification-toast.review {
        background-color: #fff3e0;
        border-left: 5px solid #ffc107;
    }

    .notification-toast.report {
        background-color: #f8d7da;
        border-left: 5px solid #dc3545;
    }

    .notification-toast.cart {
        background-color: #e6f7ff;
        border-left: 5px solid #17a2b8;
    }

    .notification-toast.favorite {
        background-color: #ffe6f0;
        border-left: 5px solid #e83e8c;
    }

    .notification-toast.product {
        background-color: #f3e7ff;
        border-left: 5px solid #6f42c1;
    }

    .notification-toast.auth {
        background-color: #e7f9e7;
        border-left: 5px solid #28a745;
    }

    .notification-icon {
        font-size: 1.5rem;
        margin-right: 0.75rem;
        vertical-align: middle;
    }

    .notification-title {
        font-weight: 600;
        font-size: 1rem;
        margin-bottom: 0.25rem;
    }

    .notification-body {
        font-size: 0.9rem;
        color: #333;
    }

    .notification-close {
        position: absolute;
        top: 10px;
        right: 10px;
        cursor: pointer;
        color: #666;
        font-size: 1rem;
    }

    @media (max-width: 576px) {
        .notification-toast {
            min-width: 90%;
            max-width: 95%;
        }
    }
</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        const userId = <?php echo json_encode($currentUserId); ?>;
        if (!userId) return;

        let ws = new WebSocket('ws://localhost:9000?user_id=' + userId);
        const notificationSound = new Audio('/assets/sounds/notification.mp3');

        function showNotification(type, title, message, link = '#') {
            console.log('showNotification called:', {
                type,
                title,
                message,
                link
            });
            const toastId = 'toast-' + Date.now();
            const iconMap = {
                message: 'fas fa-envelope',
                order: 'fas fa-shopping-cart',
                review: 'fas fa-star',
                report: 'fas fa-exclamation-triangle',
                cart: 'fas fa-cart-plus',
                favorite: 'fas fa-heart',
                product: 'fas fa-box',
                auth: 'fas fa-user'
            };
            const toastHtml = `
        <div id="${toastId}" class="notification-toast ${type} show" data-link="${link}">
            <i class="${iconMap[type] || 'fas fa-bell'} notification-icon"></i>
            <div>
                <div class="notification-title">${title}</div>
                <div class="notification-body">${message}</div>
            </div>
            <i class="fas fa-times notification-close" onclick="$('#${toastId}').remove();"></i>
        </div>
    `;
            $('#notification-container').append(toastHtml);
            console.log('Notification appended to DOM');
            notificationSound.play().catch(err => console.log('Audio error:', err));
            setTimeout(() => {
                $(`#${toastId}`).removeClass('show').css('transform', 'translateX(100%)');
                setTimeout(() => $(`#${toastId}`).remove(), 300);
            }, 5000);
        }

        ws.onopen = function() {
            console.log('WebSocket connection established at <?php echo date('h:i A T, l, F d, Y'); ?>');
        };

        ws.onmessage = function(event) {
            const data = JSON.parse(event.data);
            if (!data.type || !data.title || !data.message) return;

            showNotification(data.type, data.title, data.message, data.link);
            // Cập nhật danh sách thông báo trong header.php
            const notifyItem = `
                <div class="notify-item unread" data-id="${data.id || Date.now()}">
                    <a href="${data.link || '#'}">
                        <div class="notification-title">${data.title}</div>
                        <div class="notification-body">${data.message}</div>
                        <div class="notification-time">${data.timestamp}</div>
                    </a>
                </div>
            `;
            $('#notify-list').prepend(notifyItem);
            $.get('/notifications/unread-count', function(response) {
                if (response.success) {
                    $('#notify-count').text(response.count);
                }
            });
        };

        ws.onclose = function() {
            console.log('WebSocket closed, reconnecting...');
            setTimeout(() => {
                ws = new WebSocket('ws://localhost:9000?user_id=' + userId);
            }, 5000);
        };

        ws.onerror = function(error) {
            console.error('WebSocket error:', error);
        };

        $('#notification-container').on('click', '.notification-toast', function() {
            const link = $(this).data('link');
            if (link && link !== '#') {
                window.location.href = link;
            }
        });
    });
</script>