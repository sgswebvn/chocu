<?php

namespace App\Models;

use App\Config\Database;

class Report
{
    private $db;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
    }

    public function create($reportedUserId, $reason)
    {
        $stmt = $this->db->prepare("INSERT INTO reports (reported_user_id, reason) VALUES (?, ?)");
        return $stmt->execute([$reportedUserId, $reason]);
    }

    public function getAll()
    {
        $stmt = $this->db->query("SELECT r.*, u1.username AS reported_username, u2.username AS product_owner 
                                  FROM reports r 
                                  JOIN users u1 ON r.reported_user_id = u1.id 
                                  LEFT JOIN products p ON p.user_id = u1.id 
                                  LEFT JOIN users u2 ON p.user_id = u2.id 
                                  GROUP BY r.id");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    public function getReportsByUserId($userId)
    {
        $stmt = $this->db->prepare("
            SELECT r.id, r.reported_user_id, r.reason, r.created_at, u.username AS reported_username
            FROM reports r
            JOIN users u ON r.reported_user_id = u.id
            WHERE r.reported_user_id = ?
            ORDER BY r.created_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    public function getReportRateBySellerId($sellerId)
    {
        try {
            // Tổng số báo cáo
            $reportStmt = $this->db->prepare("SELECT COUNT(*) as report_count FROM reports WHERE reported_user_id = ?");
            $reportStmt->execute([$sellerId]);
            $reportCount = $reportStmt->fetch(\PDO::FETCH_ASSOC)['report_count'] ?? 0;

            // Tổng số đơn hàng của seller (giả sử qua Order.php)
            $orderModel = new Order();
            $orders = $orderModel->getOrdersBySellerId($sellerId);
            $totalOrders = count($orders);

            if ($totalOrders == 0) {
                return 0;
            }

            return round(($reportCount / $totalOrders) * 100, 2);
        } catch (\PDOException $e) {
            error_log("Error calculating report rate for seller ID: $sellerId - " . $e->getMessage());
            return 0;
        }
    }
}
