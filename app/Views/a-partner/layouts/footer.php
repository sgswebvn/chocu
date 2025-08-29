<?php

use App\Helpers\Session;

$currentUserId = Session::get('user')['id'] ?? null;
?>

<footer class="footer mt-auto py-3 bg-light">
    <div class="container text-center">
        <span>Mantis ♥ crafted by Team Codedthemes Distributed by ThemeWagon.</span>
        <div class="mt-2">
            <a href="#" class="text-muted mx-2">Home</a>
            <a href="#" class="text-muted mx-2">Documentation</a>
            <a href="#" class="text-muted mx-2">Support</a>
        </div>
    </div>
</footer>

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
            console.log('Raw WebSocket data:', event.data);
            try {
                const data = JSON.parse(event.data);
                console.log('Parsed data:', data);
                console.log('Validation check:', {
                    message: typeof data.message,
                    sender_id: typeof data.sender_id,
                    receiver_id: typeof data.receiver_id,
                    product_id: typeof data.product_id
                });

                if (data.type === 'chat') {
                    const isSender = Number(data.sender_id) === Number(userId);
                    console.log('Is sender:', isSender);
                    const className = isSender ? 'user' : 'seller';
                    const label = isSender ? 'Bạn' : 'Người bán';
                    $('#chat-messages').append(
                        `<div class="message ${className}">
                            <strong>${label}:</strong> ${data.message}
                            <small>${data.timestamp}</small>
                        </div>`
                    );
                    console.log('Chat message appended to DOM');
                    scrollToBottom();

                    if (!isSender && data.message) {
                        console.log('Calling showNotification:', {
                            type: 'message',
                            title: 'Tin nhắn mới từ ' + (data.sender_name || 'Người dùng'),
                            message: data.message,
                            link: `/chat/${data.product_id}/${data.sender_id}`
                        });
                        showNotification(
                            'message',
                            'Tin nhắn mới từ ' + (data.sender_name || 'Người dùng'),
                            data.message,
                            `/chat/${data.product_id}/${data.sender_id}`
                        );
                    }
                } else {
                    console.log('Handling other notification types:', data);
                    showNotification(
                        data.type,
                        data.title || 'Thông báo mới',
                        data.message || 'Không có nội dung',
                        data.link || '#'
                    );
                }
            } catch (e) {
                console.error('Error processing WebSocket message:', e);
            }
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

        function scrollToBottom() {
            const chatMessages = $('#chat-messages');
            chatMessages.scrollTop(chatMessages[0].scrollHeight);
        }
    });
</script>
</body>

</html>