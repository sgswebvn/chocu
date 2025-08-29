<?php

namespace App\Controllers\Partners;

use App\Helpers\Session;
use App\Models\ChatModel;
use App\Models\Partners\Product;
use App\WebSocket\NotificationServer;

class PMessageController
{
    private $chatModel;
    private $productModel;

    public function __construct()
    {
        $this->chatModel = new ChatModel();
        $this->productModel = new Product();
        if (!Session::get('user')) {
            Session::set('error', 'Vui lòng đăng nhập để quản lý tin nhắn!');
            header('Location: /login');
            exit;
        }
        $user = Session::get('user');
        if ($user['role'] !== 'partners' || !isset($user['is_partner_paid']) || !$user['is_partner_paid']) {
            Session::set('error', 'Bạn cần nâng cấp tài khoản đối tác để quản lý tin nhắn!');
            header('Location: /upgrade');
            exit;
        }
    }

    public function index()
    {
        $user = Session::get('user');
        $conversations = $this->chatModel->getUserConversations($user['id']);
        require_once __DIR__ . '/../../Views/a-partner/message/index.php';
    }

    public function view($productId, $receiverId)
    {
        $user = Session::get('user');
        $messages = $this->chatModel->getChatHistoryForUser($user['id'], $receiverId, $productId);
        $product = $this->productModel->find($productId);
        $product_name = $product['title'] ?? 'Không rõ tên sản phẩm';
        require_once __DIR__ . '/../../Views/a-partner/message/view.php';
    }

    public function send()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Yêu cầu không hợp lệ']);
            exit;
        }

        $user = Session::get('user');
        $senderId = $user['id'];
        $receiverId = $_POST['receiver_id'] ?? null;
        $productId = $_POST['product_id'] ?? null;
        $message = trim($_POST['message'] ?? '');

        if (!$receiverId || !$productId || !$message) {
            echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
            exit;
        }

        $result = $this->chatModel->saveChat($senderId, $receiverId, $productId, $message);
        if ($result['success']) {
            $sellerId = $this->chatModel->getSellerIdByProductId($productId);
            NotificationServer::sendChatMessage(
                $senderId,
                $receiverId,
                $productId,
                $message,
                [
                    'type' => 'chat',
                    'sender_id' => $senderId,
                    'receiver_id' => $receiverId,
                    'product_id' => $productId,
                    'message' => $message,
                    'timestamp' => $result['timestamp'],
                    'sender_role' => ($senderId == $sellerId) ? 'seller' : 'buyer',
                    'sender_name' => $user['username'] ?? 'Người dùng'
                ]
            );
            echo json_encode(['success' => true, 'timestamp' => $result['timestamp']]);
        } else {
            echo json_encode(['success' => false, 'message' => $result['message']]);
        }
        exit;
    }
}
