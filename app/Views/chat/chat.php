<?php

use App\Helpers\Session;
use App\Models\Product;

$currentUserId = Session::get('user')['id'] ?? null;
$productId = $product_id ?? null; // Lấy từ tham số route
$sellerId = $seller_id ?? null;   // Lấy từ tham số route
$productModel = new Product();
$product = $productModel->find($productId);
$product_name = $product['title'] ?? 'Không rõ tên sản phẩm';

if (!$currentUserId || !$productId || !$sellerId) {
    Session::set('error', 'Vui lòng đăng nhập và kiểm tra thông tin!');
    header('Location: /login');
    exit;
}
?>

<title>Chat với người bán - <?= htmlspecialchars($product_name) ?></title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
<style>
    .chat-section {
        max-width: 800px;
        margin: auto;
        padding: 1.5rem;
        background-color: #fff;
        border-radius: 15px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
    }

    .chat-header {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 1rem;
        border-bottom: 1px solid #e0e0e0;
    }

    .chat-header h4 {
        margin: 0;
        font-weight: 600;
    }

    #chat-messages {
        height: 450px;
        overflow-y: auto;
        padding: 1rem;
        background-color: #f9f9fb;
        border-radius: 10px;
        margin-bottom: 1rem;
        display: flex;
        flex-direction: column;
    }

    .message {
        max-width: 75%;
        padding: 0.75rem 1rem;
        margin: 0.4rem 0;
        border-radius: 20px;
        font-size: 0.95rem;
        line-height: 1.4;
        position: relative;
        display: inline-block;
        word-wrap: break-word;
    }

    .message small {
        display: block;
        margin-top: 5px;
        font-size: 0.75rem;
        color: black;
    }

    .message.user {
        align-self: flex-end;
        background-color: #007bff;
        color: white;
        border-bottom-right-radius: 4px;
    }

    .message.seller {
        align-self: flex-start;
        background-color: #e4e6eb;
        color: #000;
        border-bottom-left-radius: 4px;
    }

    .input-area {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    #chat-input {
        flex: 1;
        border-radius: 20px;
        padding: 0.6rem 1rem;
        border: 1px solid #ccc;
    }

    #send-message {
        border-radius: 30%;
        width: 45px;
        height: 45px;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: #007bff;
        color: white;
        border: none;
    }

    #send-message i {
        font-size: 18px;
    }

    @media (max-width: 576px) {
        .chat-section {
            padding: 1rem;
            border-radius: 0;
        }

        #chat-messages {
            height: 350px;
        }

        .message {
            max-width: 85%;
        }

        #send-message {
            width: 40px;
            height: 40px;
        }
    }
</style>

</head>
<?php include __DIR__ . '/../layouts/header.php'; ?>
<?php include __DIR__ . '/../products/linkcss.php'; ?>

<main class="pt-5">
    <div class="container chat-section">
        <div class="chat-header">
            <h4 class="fw-bold mb-0">Chat với người bán - <span class="fst-italic"><?= htmlspecialchars($product_name) ?></span></h4>
        </div>
        <div id="chat-messages" class="mb-3">
            <?php
            $chatModel = new \App\Models\ChatModel();
            $chatHistory = $chatModel->getChats($productId, $currentUserId, $sellerId);
            foreach ($chatHistory as $msg) {
                $isSender = $msg['sender_id'] == $currentUserId;
                $class = $isSender ? 'user' : 'seller';
                $label = $isSender ? 'Bạn' : 'Người bán';
            ?>
                <div class="message <?php echo $class; ?>">
                    <strong><?php echo htmlspecialchars($label); ?>:</strong> <?php echo htmlspecialchars($msg['message']); ?>
                    <small><?php echo $msg['created_at']; ?></small>
                </div>
            <?php
            }
            ?>
        </div>
        <div class="input-group">
            <input type="text" id="chat-input" class="form-control" placeholder="Nhập tin nhắn...">
            <button class="btn btn-primary" id="send-message">
                <i class="fas fa-paper-plane me-1"></i> Gửi
            </button>
        </div>
    </div>
</main>

<?php include __DIR__ . '/../layouts/footer.php'; ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    $(document).ready(function() {
        const productId = <?php echo json_encode($productId); ?>;
        const userId = <?php echo json_encode($currentUserId); ?>;
        const sellerId = <?php echo json_encode($sellerId); ?>;

        if (!userId) {
            alert('Vui lòng đăng nhập để sử dụng chat!');
            window.location.href = '/login';
            return;
        }

        let ws = null;

        function connectWebSocket() {
            if (!ws || ws.readyState !== WebSocket.OPEN) {
                ws = new WebSocket('ws://localhost:9000?user_id=' + userId);
                ws.onopen = function() {
                    retryCount = 0;
                };
                ws.onmessage = function(event) {
                    console.log('Raw WebSocket data:', event.data);
                    try {
                        const data = JSON.parse(event.data);
                        console.log('Parsed data:', data);
                        console.log('User ID:', userId);
                        console.log('Validation check:', {
                            message: typeof data.message,
                            sender_id: typeof data.sender_id,
                            receiver_id: typeof data.receiver_id,
                            product_id: typeof data.product_id
                        });

                        // Tạm bỏ validation để kiểm tra
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
                    const delay = Math.min(1000 * Math.pow(2, retryCount), 30000);
                    setTimeout(() => connectWebSocket(retryCount + 1), delay);
                };
                ws.onerror = function(error) {
                    console.error('WebSocket error:', error);
                };
            }
        }
        connectWebSocket();

        function scrollToBottom() {
            const chatMessages = $('#chat-messages');
            chatMessages.scrollTop(chatMessages[0].scrollHeight);
        }

        scrollToBottom();

        $('#send-message').on('click', function() {
            const message = $('#chat-input').val().trim();
            if (message && ws.readyState === WebSocket.OPEN) {
                const messageData = {
                    type: 'chat',
                    sender_id: userId,
                    receiver_id: sellerId,
                    product_id: productId,
                    message: message,
                    sender_name: '<?php echo Session::get('user')['name'] ?? 'Người dùng'; ?>'
                };
                console.log('Sending message:', messageData);
                ws.send(JSON.stringify(messageData));
                $('#chat-input').val('');
            } else {
                alert('Không thể gửi tin nhắn: Kết nối WebSocket không hoạt động hoặc tin nhắn trống');
            }
        });

        $('#chat-input').on('keypress', function(e) {
            if (e.which === 13 && $(this).val().trim()) {
                $('#send-message').click();
            }
        });
    });
</script>