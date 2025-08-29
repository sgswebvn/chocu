<?php

namespace App\Controllers;

use App\Helpers\Session;
use App\Models\ChatModel;
use App\Models\Product;
use App\WebSocket\NotificationServer;

class ChatController
{
    private $chatModel;

    public function __construct()
    {
        $this->chatModel = new ChatModel();
    }

    public function GetChat($product_id, $seller_id)
    {
        $currentUserId = Session::get('user')['id'] ?? null;

        if (!$currentUserId || !$product_id || !$seller_id) {
            Session::set('error', 'Vui lòng đăng nhập và kiểm tra thông tin sản phẩm!');
            header('Location: /login');
            exit;
        }

        $messages = $this->chatModel->getChats($product_id, $currentUserId, $seller_id);
        $productModel = new Product();
        $product = $productModel->find($product_id);
        $product_name = $product['title'] ?? 'Không rõ tên sản phẩm';
        require_once __DIR__ . '/../Views/chat/chat.php';
    }

    public function GetConversations()
    {
        $currentUserId = Session::get('user')['id'] ?? null;

        if (!$currentUserId) {
            Session::set('error', 'Vui lòng đăng nhập để xem danh sách cuộc trò chuyện!');
            header('Location: /login');
            exit;
        }

        $conversations = $this->chatModel->getUserConversations($currentUserId);
        require_once __DIR__ . '/../Views/chat/conversations.php';
    }

    public function save()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Yêu cầu không hợp lệ']);
            exit;
        }

        $senderId = $_POST['sender_id'] ?? null;
        $receiverId = $_POST['receiver_id'] ?? null;
        $productId = $_POST['product_id'] ?? null;
        $message = trim($_POST['message'] ?? '');

        $currentUserId = Session::get('user')['id'] ?? null;
        if ($senderId != $currentUserId) {
            echo json_encode(['success' => false, 'message' => 'Sender ID không hợp lệ']);
            exit;
        }

        if ($senderId && $receiverId && $productId && $message) {
            $result = $this->chatModel->saveChat($senderId, $receiverId, $productId, $message);
            if ($result['success']) {
                $productModel = new Product();
                $product = $productModel->find($productId);
                $sellerId = $product['seller_id'] ?? null;

                if (!$sellerId) {
                    echo json_encode(['success' => false, 'message' => 'Không tìm thấy người bán cho sản phẩm']);
                    exit;
                }

                $messageData = [
                    'type' => 'chat',
                    'sender_id' => (int)$senderId,
                    'receiver_id' => (int)$receiverId,
                    'product_id' => (int)$productId,
                    'message' => $message,
                    'timestamp' => $result['timestamp'],
                    'sender_role' => ($senderId == $sellerId) ? 'seller' : 'buyer',
                    'sender_name' => Session::get('user')['username'] ?? 'Người dùng'
                ];

                NotificationServer::sendChatMessage($senderId, $receiverId, $productId, $message, $messageData);

                echo json_encode($result);
            } else {
                echo json_encode(['success' => false, 'message' => $result['message']]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
        }
        exit;
    }
}
