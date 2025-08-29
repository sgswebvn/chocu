<?php

namespace App\Models;

use App\Config\Database;

class ChatModel
{
    private $db;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
    }

    public function getChats($productId, $userId, $sellerId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT c.*, 
                       u.username AS sender_name, 
                       u2.username AS receiver_name,
                       CASE 
                           WHEN c.sender_id = p.seller_id THEN 'seller'
                           ELSE 'buyer'
                       END AS sender_role,
                       CASE 
                           WHEN c.receiver_id = p.seller_id THEN 'seller'
                           ELSE 'buyer'
                       END AS receiver_role
                FROM chats c 
                LEFT JOIN users u ON c.sender_id = u.id 
                LEFT JOIN users u2 ON c.receiver_id = u2.id 
                LEFT JOIN products p ON c.product_id = p.id
                WHERE c.product_id = ? AND 
                      ((c.sender_id = ? AND c.receiver_id = ?) OR 
                       (c.sender_id = ? AND c.receiver_id = ?)) 
                ORDER BY c.created_at ASC
            ");
            $stmt->execute([$productId, $userId, $sellerId, $sellerId, $userId]);
            $chats = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $sellerIdFromDb = $this->getSellerIdByProductId($productId);
            if ($sellerIdFromDb != $sellerId) {
                error_log("Mismatch seller_id: expected {$sellerId}, got {$sellerIdFromDb} for product {$productId}");
            }

            return $chats;
        } catch (\PDOException $e) {
            error_log("Lỗi khi lấy lịch sử chat: " . $e->getMessage());
            throw new \Exception("Lỗi khi lấy lịch sử chat: " . $e->getMessage());
        }
    }

    public function getChatHistoryForUser($viewerId, $participantId, $productId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT c.*, u.username AS sender_name, u2.username AS receiver_name 
                FROM chats c 
                LEFT JOIN users u ON c.sender_id = u.id 
                LEFT JOIN users u2 ON c.receiver_id = u2.id 
                WHERE c.product_id = ? AND 
                      ((c.sender_id = ? AND c.receiver_id = ?) OR 
                       (c.sender_id = ? AND c.receiver_id = ?)) 
                ORDER BY c.created_at ASC
            ");
            $stmt->execute([$productId, $viewerId, $participantId, $participantId, $viewerId]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Lỗi khi lấy lịch sử chat cho người dùng: " . $e->getMessage());
            throw new \Exception("Lỗi khi lấy lịch sử chat cho người dùng: " . $e->getMessage());
        }
    }

    public function getUserConversations($userId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT DISTINCT 
                    CASE 
                        WHEN c.sender_id = ? THEN c.receiver_id 
                        ELSE c.sender_id 
                    END AS other_user_id,
                    u.username AS other_user_name,
                    c.product_id,
                    p.title AS product_name,
                    p.image AS product_image,
                    us.is_partner_paid,
                    MAX(c.created_at) AS last_message_time
                FROM chats c
                LEFT JOIN users u ON u.id = CASE 
                    WHEN c.sender_id = ? THEN c.receiver_id 
                    ELSE c.sender_id 
                END
                LEFT JOIN products p ON c.product_id = p.id
                LEFT JOIN users us ON p.seller_id = us.id
                WHERE c.sender_id = ? OR c.receiver_id = ?
                GROUP BY 
                    CASE 
                        WHEN c.sender_id = ? THEN c.receiver_id 
                        ELSE c.sender_id 
                    END,
                    u.username,
                    c.product_id,
                    p.title,
                    p.image,
                    us.is_partner_paid
                ORDER BY last_message_time DESC
            ");
            $stmt->execute([$userId, $userId, $userId, $userId, $userId]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Lỗi khi lấy danh sách cuộc trò chuyện: " . $e->getMessage());
            throw new \Exception("Lỗi khi lấy danh sách cuộc trò chuyện: " . $e->getMessage());
        }
    }

    public function saveChat($senderId, $receiverId, $productId, $message)
    {
        try {
            // Kiểm tra is_partner_paid của shop
            $stmt = $this->db->prepare("
                SELECT u.is_partner_paid 
                FROM products p 
                LEFT JOIN users u ON p.seller_id = u.id 
                WHERE p.id = ?
            ");
            $stmt->execute([$productId]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($result && !$result['is_partner_paid'] && $senderId != $result['seller_id']) {
                return ['success' => false, 'message' => 'Shop chưa nâng cấp tài khoản đối tác!'];
            }

            $stmt = $this->db->prepare("
                INSERT INTO chats (sender_id, receiver_id, product_id, message, created_at)
                VALUES (?, ?, ?, ?, NOW())
            ");
            $success = $stmt->execute([$senderId, $receiverId, $productId, $message]);
            return $success ? ['success' => true, 'timestamp' => date('Y-m-d H:i:s')] : ['success' => false, 'message' => 'Không thể lưu tin nhắn'];
        } catch (\PDOException $e) {
            error_log("Lỗi khi lưu tin nhắn: " . $e->getMessage());
            return ['success' => false, 'message' => 'Lỗi cơ sở dữ liệu: ' . $e->getMessage()];
        }
    }

    public function getSellerIdByProductId($productId)
    {
        try {
            $stmt = $this->db->prepare("SELECT seller_id FROM products WHERE id = ?");
            $stmt->execute([$productId]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $result['seller_id'] ?? null;
        } catch (\PDOException $e) {
            error_log("Lỗi khi lấy seller_id: " . $e->getMessage());
            return null;
        }
    }
}
