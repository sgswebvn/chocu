<?php

namespace App\Models;

use App\Config\Database;

class Notification
{
    private $db;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
    }

    public function create($userId, $type, $title, $message, $link = null)
    {
        $stmt = $this->db->prepare("
            INSERT INTO notifications (user_id, type, title, message, link, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        return $stmt->execute([$userId, $type, $title, $message, $link]);
    }

    public function getByUser($userId, $limit = 50, $offset = 0)
    {
        $limit = (int) $limit;
        $offset = (int) $offset;

        $sql = "
        SELECT id, type, title, message, link, is_read, created_at
        FROM notifications
        WHERE user_id = ?
        ORDER BY created_at DESC
        LIMIT $limit OFFSET $offset
    ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }


    public function getUnreadCount($userId)
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count
            FROM notifications
            WHERE user_id = ? AND is_read = 0
        ");
        $stmt->execute([$userId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC)['count'];
    }

    public function markAsRead($id, $userId)
    {
        $stmt = $this->db->prepare("
            UPDATE notifications
            SET is_read = 1
            WHERE id = ? AND user_id = ?
        ");
        return $stmt->execute([$id, $userId]);
    }

    public function markAllAsRead($userId)
    {
        $stmt = $this->db->prepare("
            UPDATE notifications
            SET is_read = 1
            WHERE user_id = ? AND is_read = 0
        ");
        return $stmt->execute([$userId]);
    }
    public function findByOrderId($orderId, $userId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM notifications 
                WHERE user_id = ? AND order_id = ?
                LIMIT 1
            ");
            $stmt->execute([$userId, $orderId]);
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error checking notification by order_id: " . $e->getMessage());
            return false;
        }
    }
}
