<?php

namespace App\Models;

use App\Config\Database;
use App\Models\Order;

class Admin
{
    private $db;
    private $orderModel;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
        $this->orderModel = new Order();

    }
// Thêm vào class Admin
public function getTotalRevenueByPeriod($period = 'day')
{
    $sql = "";
    $dateFormat = "";

    if ($period === 'day') {
        $sql = "SELECT DATE(o.created_at) as period, COALESCE(SUM(o.total_price), 0) as revenue
                FROM orders o
                WHERE o.status = 'delivered'
                  AND o.created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                GROUP BY DATE(o.created_at)
                ORDER BY period ASC";
    } elseif ($period === 'month') {
        $sql = "SELECT DATE_FORMAT(o.created_at, '%Y-%m') as period, COALESCE(SUM(o.total_price), 0) as revenue
                FROM orders o
                WHERE o.status = 'delivered'
                  AND o.created_at >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                GROUP BY DATE_FORMAT(o.created_at, '%Y-%m')
                ORDER BY period ASC";
    } elseif ($period === 'year') {
        $sql = "SELECT YEAR(o.created_at) as period, COALESCE(SUM(o.total_price), 0) as revenue
                FROM orders o
                WHERE o.status = 'delivered'
                  AND o.created_at >= DATE_SUB(CURDATE(), INTERVAL 5 YEAR)
                GROUP BY YEAR(o.created_at)
                ORDER BY period ASC";
    }

    $stmt = $this->db->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(\PDO::FETCH_ASSOC);
}
public function getTotalSystemRevenue()
{
    $stmt = $this->db->query("SELECT COALESCE(SUM(total_price), 0) FROM orders WHERE status = 'delivered'");
    return (float)$stmt->fetchColumn();
}
    public function getStats()
    {
    $orderModel = new Order();
        $stats = [];
        $stats = [
            'admin_total_orders'   => $orderModel->getAdminOrderCount(),
            'admin_total_revenue'  => $orderModel->getAdminRevenue(true), 
            'admin_pending_orders' => $orderModel->getAdminRevenue(false) - $orderModel->getAdminRevenue(true), // tạm tính
        ];
        $stats['products'] = $this->db->query("SELECT COUNT(*) as count FROM products")->fetch(\PDO::FETCH_ASSOC)['count'];
        $stats['users'] = $this->db->query("SELECT COUNT(*) as count FROM users")->fetch(\PDO::FETCH_ASSOC)['count'];
        $stats['orders'] = $this->db->query("SELECT COUNT(*) as count FROM orders")->fetch(\PDO::FETCH_ASSOC)['count'];
        $stats['revenue'] = $this->db->query("SELECT SUM(amount) as total FROM transactions WHERE status = 'completed'")->fetch(\PDO::FETCH_ASSOC)['total'] ?? 0;
        return $stats;
    }

    public function searchProducts($keyword, $status = '')
{
    $params = [];
    $query = "SELECT * FROM products WHERE 1=1"; // 1=1 để dễ nối điều kiện

    // Lọc theo từ khóa
    if (!empty($keyword)) {
        $query .= " AND (title LIKE ? OR description LIKE ?)";
        $params[] = "%$keyword%";
        $params[] = "%$keyword%";
    }

    // Lọc theo trạng thái
    if (!empty($status)) {
        $query .= " AND status = ?";
        $params[] = $status;
    }

    $stmt = $this->db->prepare($query);
    $stmt->execute($params);
    return $stmt->fetchAll(\PDO::FETCH_ASSOC);
}

    public function searchUsers($keyword)
    {
        $keyword = "%$keyword%";
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username LIKE ? OR email LIKE ?");
        $stmt->execute([$keyword, $keyword]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getAllProducts()
    {
        $stmt = $this->db->query("SELECT * FROM products WHERE deleted_at IS NULL");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function updateProductStatus($id, $status)
    {
        if (!in_array($status, ['pending', 'approved', 'rejected'])) {
            return false;
        }
        $stmt = $this->db->prepare("UPDATE products SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }

   public function getAllUsers($keyword = '', $status = '')
{
    $sql = "SELECT id, username, email, role, is_active, created_at FROM users WHERE 1=1";
    $params = [];

    // 1. Tìm kiếm từ khóa
    if (!empty($keyword)) {
        $sql .= " AND (username LIKE ? OR email LIKE ?)";
        $params[] = "%$keyword%";
        $params[] = "%$keyword%";
    }

    // 2. Lọc trạng thái - QUAN TRỌNG: admin luôn được hiển thị dù lọc gì
    if ($status === 'active') {
        $sql .= " AND (role = 'admin' OR (role != 'admin' AND is_active = 1))";
    } elseif ($status === 'banned') {
        $sql .= " AND (role = 'admin' OR (role != 'admin' AND is_active = 0))";
    }
    // Nếu $status == '' → không thêm gì → lấy hết (bao gồm cả admin)

    $sql .= " ORDER BY 
                CASE WHEN role = 'admin' THEN 0 ELSE 1 END, 
                created_at DESC";

    $stmt = $this->db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(\PDO::FETCH_ASSOC);
}

    public function toggleUserStatus($id, $is_active)
    {
        $stmt = $this->db->prepare("UPDATE users SET is_active = ? WHERE id = ?");
        return $stmt->execute([$is_active, $id]);
    }
    public function getUserById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        return $stmt->execute([$id]) ? $stmt->fetch(\PDO::FETCH_ASSOC) : false;
     
    }

    public function getAllReports()
    {
        $stmt = $this->db->query("SELECT r.*, p.title, u.username FROM reports r JOIN products p ON r.product_id = p.id JOIN users u ON r.user_id = u.id");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function deleteReport($id)
    {
        $stmt = $this->db->prepare("DELETE FROM reports WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getRevenueByPeriod($period = 'day')
    {
        $query = "";
        switch ($period) {
            case 'day':
                $query = "SELECT DATE(created_at) as period, COALESCE(SUM(amount), 0) as revenue 
                          FROM transactions 
                          WHERE status = 'completed' 
                          GROUP BY period 
                          ORDER BY period DESC LIMIT 30";
                break;
            case 'month':
                $query = "SELECT DATE_FORMAT(created_at, '%Y-%m') as period, COALESCE(SUM(amount), 0) as revenue 
                          FROM transactions 
                          WHERE status = 'completed' 
                          GROUP BY period 
                          ORDER BY period DESC LIMIT 12";
                break;
            case 'year':
                $query = "SELECT YEAR(created_at) as period, COALESCE(SUM(amount), 0) as revenue 
                          FROM transactions 
                          WHERE status = 'completed' 
                          GROUP BY period 
                          ORDER BY period DESC LIMIT 5";
                break;
        }
        $stmt = $this->db->query($query);
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        error_log("RevenueByPeriod Query: $query, Result: " . print_r($result, true));
        return $result;
    }

    public function getSellerComparisons($criteria = 'revenue', $limit = 10)
    {
        $query = "SELECT u.id, u.username, 
              COALESCE(SUM(t.amount), 0) as revenue,
              COALESCE(
                  ((SELECT COALESCE(SUM(t2.amount), 0) FROM transactions t2 JOIN orders o2 ON t2.order_id = o2.id WHERE o2.seller_id = u.id AND t2.status = 'completed' AND DATE_FORMAT(t2.created_at, '%Y-%m') = DATE_FORMAT(NOW(), '%Y-%m'))
                   -
                   (SELECT COALESCE(SUM(t3.amount), 0) FROM transactions t3 JOIN orders o3 ON t3.order_id = o3.id WHERE o3.seller_id = u.id AND t3.status = 'completed' AND DATE_FORMAT(t3.created_at, '%Y-%m') = DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 MONTH), '%Y-%m'))
                  ) * 100.0 / NULLIF((SELECT COALESCE(SUM(t3.amount), 0) FROM transactions t3 JOIN orders o3 ON t3.order_id = o3.id WHERE o3.seller_id = u.id AND t3.status = 'completed' AND DATE_FORMAT(t3.created_at, '%Y-%m') = DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 MONTH), '%Y-%m')), 0),
                  0
              ) as revenue_growth,
              COALESCE(AVG(ur.rating), 0) as avg_rating,
              COALESCE(
                  (SELECT COUNT(o2.id) FROM orders o2 WHERE o2.seller_id = u.id AND o2.status = 'cancelled') * 100.0 / 
                  NULLIF((SELECT COUNT(o3.id) FROM orders o3 WHERE o3.seller_id = u.id), 0),
                  0
              ) as cancel_rate
              FROM users u 
              LEFT JOIN orders o ON o.seller_id = u.id 
              LEFT JOIN transactions t ON t.order_id = o.id AND t.status = 'completed'
              LEFT JOIN userrating ur ON ur.rated_id = u.id
              WHERE u.role = 'user'
              GROUP BY u.id, u.username
              HAVING revenue > 0
              ORDER BY ";

        switch ($criteria) {
            case 'revenue':
                $query .= "revenue DESC";
                break;
            case 'revenue_growth':
                $query .= "revenue_growth DESC";
                break;
            case 'ratings':
                $query .= "avg_rating DESC";
                break;
            case 'cancellations':
                $query .= "cancel_rate DESC";
                break;
            default:
                $query .= "revenue DESC";
                break;
        }
        $query .= " LIMIT :limit";

        error_log("getSellerComparisons Query: $query");
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        error_log("getSellerComparisons Result: " . print_r($result, true));
        return $result;
    }

   public function detectPotentialSellers($limit = 10)
{
    $query = "
        SELECT 
            u.id,
            u.username,
            -- Doanh thu CHỈ trong 30 ngày gần nhất
            COALESCE(SUM(CASE 
                WHEN o.status = 'delivered' 
                AND o.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) 
                THEN o.total_price ELSE 0 
            END), 0) AS revenue_30d,

            -- Số đơn hoàn thành trong 30 ngày gần nhất
            COUNT(CASE WHEN o.status = 'delivered' 
                AND o.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) 
                THEN 1 END) AS orders_30d,

            -- Tổng đơn hoàn thành (để kiểm tra có hoạt động không)
            COUNT(CASE WHEN o.status = 'delivered' THEN 1 END) AS total_delivered,

            -- Đánh giá trung bình
            COALESCE(AVG(sr.rating), 5.0) AS avg_rating,

            -- Tỷ lệ hủy chính xác 100% (chỉ tính đơn delivered + cancelled)
            ROUND(
                COALESCE(
                    SUM(CASE WHEN o.status = 'cancelled' THEN 1 ELSE 0 END) * 100.0 /
                    NULLIF(
                        SUM(CASE WHEN o.status IN ('delivered', 'cancelled') THEN 1 ELSE 0 END), 0
                    ), 0
                ), 2
            ) AS cancel_rate

        FROM users u
        LEFT JOIN orders o 
            ON o.seller_id = u.id 
            AND o.status IN ('delivered', 'cancelled')
        LEFT JOIN seller_ratings sr ON sr.seller_id = u.id
        WHERE u.role IN ('user', 'partners')
          AND u.is_active = 1
        GROUP BY u.id, u.username
        HAVING 
            revenue_30d >= 5000000                    -- ≥ 5 triệu trong 30 ngày gần nhất
            AND orders_30d >= 3                        -- ít nhất 3 đơn trong 30 ngày
            AND avg_rating >= 4.5                      -- đánh giá cao
            AND cancel_rate <= 25                      -- hủy không quá 25%
        ORDER BY revenue_30d DESC, orders_30d DESC
        LIMIT :limit
    ";

    $stmt = $this->db->prepare($query);
    $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(\PDO::FETCH_ASSOC);
}

    public function detectViolatingSellers($limit = null)
{
    $query = "
        SELECT 
            u.id, 
            u.username,
            u.role,
            COALESCE(AVG(sr.rating), 5.0) AS avg_rating,
            
            -- Đếm đúng số đơn (không DISTINCT)
            COUNT(o.id) AS total_orders,
            
            -- Số đơn hủy
            COALESCE(SUM(CASE WHEN o.status = 'cancelled' THEN 1 ELSE 0 END), 0) AS cancellations,
            
            -- ĐẾM ĐÚNG SỐ BÁO CÁO CỦA SELLER (có thể >1)
            (SELECT COUNT(*) FROM reports r2 WHERE r2.reported_user_id = u.id) AS reports_count

        FROM users u
        LEFT JOIN seller_ratings sr ON sr.seller_id = u.id
        LEFT JOIN orders o ON o.seller_id = u.id 
            AND o.status IN ('delivered', 'cancelled', 'pending', 'shipped')
        WHERE u.role IN ('user', 'partners')
          AND u.is_active = 1
        GROUP BY u.id, u.username, u.role
        HAVING 
            (SELECT COUNT(*) FROM reports r2 WHERE r2.reported_user_id = u.id) >= 1      -- có ít nhất 1 báo cáo
            OR cancellations >= 3
            OR (total_orders > 0 AND avg_rating < 4.0)
        ORDER BY 
            reports_count DESC, 
            cancellations DESC, 
            avg_rating ASC
    ";

    if ($limit !== null) {
        $query .= " LIMIT :limit";
    }

    $stmt = $this->db->prepare($query);
    if ($limit !== null) {
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
    }
    $stmt->execute();

    $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

    foreach ($results as &$seller) {
        $delivered   = $seller['total_orders'] - $seller['cancellations'];
        $relevant    = $delivered + $seller['cancellations'];
        $seller['cancel_rate'] = $relevant > 0 
            ? round(($seller['cancellations'] / $relevant) * 100, 2)
            : 0;
    }
    unset($seller);

    return $results;
}
   public function getTopSellingProducts($limit = 10)
    {
        $query = "SELECT p.id, p.title, p.image, u.is_partner_paid, c.name AS category_name, u.username AS seller_name, 
                         COALESCE(SUM(o.quantity), 0) AS total_sold, 
                         COALESCE(SUM(o.quantity * o.total_price), 0) AS total_revenue
                  FROM products p
                  LEFT JOIN categories c ON p.category_id = c.id
                  LEFT JOIN users u ON p.seller_id = u.id
                  LEFT JOIN orders o ON p.id = o.product_id AND o.status = 'delivered'
                  WHERE p.status = 'approved'
                  GROUP BY p.id, p.title, p.image, u.is_partner_paid, c.name, u.username
                  ORDER BY total_sold DESC, total_revenue DESC
                  LIMIT :limit";

        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        error_log("getTopSellingProducts Query: $query, Result: " . print_r($result, true));
        return $result;
    }

    public function getTopSellingCategories($limit = 5)
    {
        $query = "SELECT c.id, c.name AS category_name, 
                         COALESCE(SUM(o.quantity), 0) AS total_sold, 
                         COALESCE(SUM(o.quantity * o.total_price), 0) AS total_revenue
                  FROM categories c
                  LEFT JOIN products p ON c.id = p.category_id
                  LEFT JOIN orders o ON p.id = o.product_id AND o.status = 'delivered'
                  WHERE p.status = 'approved'
                  GROUP BY c.id, c.name
                  ORDER BY total_sold DESC, total_revenue DESC
                  LIMIT :limit";

        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        error_log("getTopSellingCategories Query: $query, Result: " . print_r($result, true));
        return $result;
    }
    public function getBestSellingInCategory($categoryId, $currentProductId, $limit = 4)
    {
        $query = "SELECT p.id, p.title, p.image, p.price, u.username, u.is_partner_paid, c.name AS category_name, 
                         COALESCE(SUM(o.quantity), 0) AS total_sold
                  FROM products p 
                  LEFT JOIN users u ON p.seller_id = u.id 
                  LEFT JOIN categories c ON p.category_id = c.id 
                  LEFT JOIN orders o ON p.id = o.product_id AND o.status = 'delivered'
                  WHERE p.category_id = :category_id 
                  AND p.id != :current_product_id 
                  AND p.status = 'approved'
                  GROUP BY p.id, p.title, p.image, p.price, u.username, u.is_partner_paid, c.name
                  ORDER BY total_sold DESC, p.views DESC
                  LIMIT :limit";

        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':category_id', $categoryId, \PDO::PARAM_INT);
        $stmt->bindValue(':current_product_id', $currentProductId, \PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        error_log("getBestSellingInCategory Query: $query, Result: " . print_r($result, true));
        return $result;
    }
}
