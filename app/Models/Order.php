<?php

namespace App\Models;

use App\Config\Database;

class Order
{
    private $db;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
    }

    public function find($id)
    {
        $stmt = $this->db->prepare("
            SELECT o.*, p.title, p.image, u.username AS seller_name, u.is_partner_paid
            FROM orders o 
            JOIN products p ON o.product_id = p.id 
            JOIN users u ON o.seller_id = u.id 
            WHERE o.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function getBySeller($sellerId)
    {
        $stmt = $this->db->prepare("
            SELECT o.*, p.title, p.image, u.username AS buyer_name, us.is_partner_paid
            FROM orders o 
            JOIN products p ON o.product_id = p.id 
            JOIN users u ON o.buyer_id = u.id 
            JOIN users us ON o.seller_id = us.id
            WHERE o.seller_id = ? AND us.is_partner_paid = 1
            ORDER BY o.created_at DESC
        ");
        $stmt->execute([$sellerId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getOrdersBySellerId($sellerId)
    {
        $stmt = $this->db->prepare("
            SELECT o.*, p.title, p.image, u.username AS buyer_name, us.is_partner_paid
            FROM orders o 
            JOIN products p ON o.product_id = p.id 
            JOIN users u ON o.buyer_id = u.id 
            JOIN users us ON o.seller_id = us.id
            WHERE o.seller_id = ? 
            ORDER BY o.created_at DESC
        ");
        $stmt->execute([$sellerId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getOrdersByBuyerId($buyerId)
    {
        $stmt = $this->db->prepare("
            SELECT o.*, p.title, p.image, u.username AS seller_name
            FROM orders o 
            JOIN products p ON o.product_id = p.id 
            JOIN users u ON o.seller_id = u.id 
            WHERE o.buyer_id = ?
            ORDER BY o.created_at DESC
        ");
        $stmt->execute([$buyerId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getOrderById($id)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT o.*, p.title, p.image, u.username AS buyer_name, us.username AS seller_name, pm.payment_method, pm.status AS payment_status
                FROM orders o 
                LEFT JOIN products p ON o.product_id = p.id 
                LEFT JOIN users u ON o.buyer_id = u.id
                LEFT JOIN users us ON o.seller_id = us.id
                LEFT JOIN payment pm ON o.id = pm.order_id
                WHERE o.id = ?
            ");
            $stmt->execute([$id]);
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error fetching order: " . $e->getMessage());
            return false;
        }
    }
public function getAdminOrderCount()
    {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) AS total 
                FROM orders o
                JOIN products p ON o.product_id = p.id
                JOIN users u ON p.seller_id = u.id
                WHERE u.role = 'admin'
            ");
            $stmt->execute();
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            return (int)($result['total'] ?? 0);
        } catch (\PDOException $e) {
            error_log("Error counting admin orders: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Lấy tổng doanh thu từ các đơn hàng mà admin là người bán
     * (bao gồm tất cả trạng thái hoặc chỉ delivered tùy bạn chọn)
     */
    public function getAdminRevenue($onlyDelivered = true)
    {
        try {
            $sql = "
                SELECT COALESCE(SUM(o.total_price), 0) AS revenue
                FROM orders o
                JOIN products p ON o.product_id = p.id
                JOIN users u ON p.seller_id = u.id
                WHERE u.role = 'admin'
            ";

            // Nếu chỉ tính đơn đã giao thành công (đúng thực tế doanh thu thực nhận)
            if ($onlyDelivered) {
                $sql .= " AND o.status = 'delivered'";
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            return (float)$result['revenue'];
        } catch (\PDOException $e) {
            error_log("Error calculating admin revenue: " . $e->getMessage());
            return 0.0;
        }
    }
    public function create($userId, $sellerId, $productId, $quantity, $totalPrice)
    {
        try {
            $this->db->beginTransaction();
            $stmt = $this->db->prepare("
                INSERT INTO orders (buyer_id, seller_id, product_id, quantity, total_price, status, created_at)
                VALUES (?, ?, ?, ?, ?, 'pending', NOW())
            ");
            $stmt->execute([$userId, $sellerId, $productId, $quantity, $totalPrice]);
            $orderId = (int)$this->db->lastInsertId();
            $this->db->commit();
            return $orderId;
        } catch (\PDOException $e) {
            $this->db->rollBack();
            error_log("Error creating order: " . $e->getMessage());
            return false;
        }
    }

    public function addDetail($orderId, $details)
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO order_detail (order_id, fullname, phone, pincode, state, town_city, house_no, road_name)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            return $stmt->execute([
                $orderId,
                $details['fullname'],
                $details['phone'],
                $details['pincode'],
                $details['state'],
                $details['town_city'],
                $details['house_no'],
                $details['road_name'],
            ]);
        } catch (\PDOException $e) {
            error_log("Error adding order detail: " . $e->getMessage());
            return false;
        }
    }


    public function createPayment($orderId, $paymentMethod, $amount, $transactionId = null)
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO payment (order_id, payment_method, transaction_id, amount, status)
                VALUES (?, ?, ?, ?, ?)
            ");
            return $stmt->execute([$orderId, $paymentMethod, $transactionId, $amount, 'pending']);
        } catch (\PDOException $e) {
            error_log("Error creating payment: " . $e->getMessage());
            return false;
        }
    }

    public function updatePaymentStatus($orderId, $status, $transactionId = null)
    {
        try {
            $stmt = $this->db->prepare("
                UPDATE payment 
                SET status = ?, transaction_id = ?, updated_at = NOW()
                WHERE order_id = ?
            ");
            return $stmt->execute([$status, $transactionId, $orderId]);
        } catch (\PDOException $e) {
            error_log("Error updating payment status: " . $e->getMessage());
            return false;
        }
    }

    public function updateStatus($orderId, $status, $trackingNumber = null, $carrier = null)
    {
        try {
            $stmt = $this->db->prepare("
                UPDATE orders 
                SET status = ?, tracking_number = ?, carrier = ?, updated_at = NOW()
                WHERE id = ?
            ");
            return $stmt->execute([$status, $trackingNumber, $carrier, $orderId]);
        } catch (\PDOException $e) {
            error_log("Error updating order status: " . $e->getMessage());
            return false;
        }
    }

    public function countOrdersBySellerId($sellerId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) AS total 
                FROM orders o
                JOIN users u ON o.seller_id = u.id
                WHERE o.seller_id = ? AND u.is_partner_paid = 1
            ");
            $stmt->execute([$sellerId]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $result['total'] ?? 0;
        } catch (\PDOException $e) {
            error_log("Error counting orders: " . $e->getMessage());
            return 0;
        }
    }

    public function canAccessOrder($orderId, $userId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT 1
                FROM orders o
                JOIN users u ON o.seller_id = u.id
                WHERE o.id = ? AND (o.buyer_id = ? OR (o.seller_id = ? AND u.is_partner_paid = 1))
            ");
            $stmt->execute([$orderId, $userId, $userId]);
            return $stmt->fetch() !== false;
        } catch (\PDOException $e) {
            error_log("Error checking order access: " . $e->getMessage());
            return false;
        }
    }

    public function hasPurchased($userId, $productId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as total
                FROM orders o
                WHERE o.buyer_id = ? AND o.product_id = ? AND o.status = 'delivered'
            ");
            $stmt->execute([$userId, $productId]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $result['total'] > 0;
        } catch (\PDOException $e) {
            error_log("Error checking purchase status: " . $e->getMessage());
            return false;
        }
    }

    public function getTopSellingProducts($sellerId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT p.id, p.title, SUM(o.quantity) as total_quantity
                FROM products p
                JOIN orders o ON p.id = o.product_id
                WHERE p.user_id = ? AND o.status = 'delivered'
                GROUP BY p.id, p.title
                ORDER BY total_quantity DESC
                LIMIT 5
            ");
            $stmt->execute([$sellerId]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error fetching top selling products for seller ID: $sellerId - " . $e->getMessage());
            return [];
        }
    }

    public function getRevenueByProduct($sellerId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT p.id, p.title, SUM(o.total_price) as total_revenue
                FROM products p
                JOIN orders o ON p.id = o.product_id
                WHERE p.user_id = ? AND o.status = 'delivered'
                GROUP BY p.id, p.title
                ORDER BY total_revenue DESC
                LIMIT 5
            ");
            $stmt->execute([$sellerId]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error fetching revenue by product for seller ID: $sellerId - " . $e->getMessage());
            return [];
        }
    }

    public function getRevenueByCategory($sellerId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT c.name as category_name, SUM(o.total_price) as total_revenue
                FROM products p
                JOIN orders o ON p.id = o.product_id
                JOIN categories c ON p.category_id = c.id
                WHERE p.user_id = ? AND o.status = 'delivered'
                GROUP BY c.id, c.name
                ORDER BY total_revenue DESC
            ");
            $stmt->execute([$sellerId]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error fetching revenue by category for seller ID: $sellerId - " . $e->getMessage());
            return [];
        }
    }

    public function getRevenueByPeriod($sellerId)
    {
        try {
            $daily = $this->db->prepare("
                SELECT DATE(o.created_at) as period, SUM(o.total_price) as total_revenue
                FROM orders o
                JOIN products p ON o.product_id = p.id
                WHERE p.user_id = ? AND o.status = 'delivered'
                GROUP BY DATE(o.created_at)
                ORDER BY period DESC
                LIMIT 7
            ");
            $daily->execute([$sellerId]);
            $dailyRevenue = $daily->fetchAll(\PDO::FETCH_ASSOC);

            $monthly = $this->db->prepare("
                SELECT DATE_FORMAT(o.created_at, '%Y-%m') as period, SUM(o.total_price) as total_revenue
                FROM orders o
                JOIN products p ON o.product_id = p.id
                WHERE p.user_id = ? AND o.status = 'delivered'
                GROUP BY DATE_FORMAT(o.created_at, '%Y-%m')
                ORDER BY period DESC
                LIMIT 12
            ");
            $monthly->execute([$sellerId]);
            $monthlyRevenue = $monthly->fetchAll(\PDO::FETCH_ASSOC);

            $yearly = $this->db->prepare("
                SELECT YEAR(o.created_at) as period, SUM(o.total_price) as total_revenue
                FROM orders o
                JOIN products p ON o.product_id = p.id
                WHERE p.user_id = ? AND o.status = 'delivered'
                GROUP BY YEAR(o.created_at)
                ORDER BY period DESC
                LIMIT 5
            ");
            $yearly->execute([$sellerId]);
            $yearlyRevenue = $yearly->fetchAll(\PDO::FETCH_ASSOC);

            return [
                'daily' => $dailyRevenue,
                'monthly' => $monthlyRevenue,
                'yearly' => $yearlyRevenue
            ];
        } catch (\PDOException $e) {
            error_log("Error fetching revenue by period for seller ID: $sellerId - " . $e->getMessage());
            return ['daily' => [], 'monthly' => [], 'yearly' => []];
        }
    }

    public function getCancellationRate($sellerId)
    {
        try {
            $totalStmt = $this->db->prepare("
                SELECT COUNT(*) as total
                FROM orders o
                JOIN products p ON o.product_id = p.id
                WHERE p.user_id = ?
            ");
            $totalStmt->execute([$sellerId]);
            $totalOrders = $totalStmt->fetch(\PDO::FETCH_ASSOC)['total'] ?? 0;

            $cancelledStmt = $this->db->prepare("
                SELECT COUNT(*) as cancelled
                FROM orders o
                JOIN products p ON o.product_id = p.id
                WHERE p.user_id = ? AND o.status = 'cancelled'
            ");
            $cancelledStmt->execute([$sellerId]);
            $cancelledOrders = $cancelledStmt->fetch(\PDO::FETCH_ASSOC)['cancelled'] ?? 0;

            if ($totalOrders == 0) {
                return 0;
            }

            return round(($cancelledOrders / $totalOrders) * 100, 2);
        } catch (\PDOException $e) {
            error_log("Error calculating cancellation rate for seller ID: $sellerId - " . $e->getMessage());
            return 0;
        }
    }
}
