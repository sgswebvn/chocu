<?php

namespace App\Models;

use App\Config\Database;

class Admin
{
    private $db;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
    }

    public function getStats()
    {
        $stats = [];
        $stats['products'] = $this->db->query("SELECT COUNT(*) as count FROM products")->fetch(\PDO::FETCH_ASSOC)['count'];
        $stats['users'] = $this->db->query("SELECT COUNT(*) as count FROM users")->fetch(\PDO::FETCH_ASSOC)['count'];
        $stats['orders'] = $this->db->query("SELECT COUNT(*) as count FROM orders")->fetch(\PDO::FETCH_ASSOC)['count'];
        $stats['revenue'] = $this->db->query("SELECT SUM(amount) as total FROM transactions WHERE status = 'completed'")->fetch(\PDO::FETCH_ASSOC)['total'] ?? 0;
        return $stats;
    }

    public function searchProducts($keyword)
    {
        $keyword = "%$keyword%";
        $stmt = $this->db->prepare("SELECT * FROM products WHERE title LIKE ? OR description LIKE ?");
        $stmt->execute([$keyword, $keyword]);
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
        $stmt = $this->db->query("SELECT * FROM products");
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

    public function getAllUsers()
    {
        $stmt = $this->db->query("SELECT * FROM users");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function toggleUserStatus($id, $is_active)
    {
        $stmt = $this->db->prepare("UPDATE users SET is_active = ? WHERE id = ?");
        return $stmt->execute([$is_active, $id]);
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
        $query = "SELECT u.id, u.username, 
                  COALESCE(SUM(t.amount), 0) as revenue, 
                  COALESCE(AVG(ur.rating), 0) as avg_rating, 
                  COALESCE(
                      (SELECT COUNT(o2.id) FROM orders o2 WHERE o2.seller_id = u.id AND o2.status = 'cancelled') * 100.0 / 
                      NULLIF((SELECT COUNT(o3.id) FROM orders o3 WHERE o3.seller_id = u.id), 0),
                      0
                  ) as cancel_rate,
                  COALESCE(
                      ((SELECT COALESCE(SUM(t2.amount), 0) FROM transactions t2 JOIN orders o2 ON t2.order_id = o2.id WHERE o2.seller_id = u.id AND t2.status = 'completed' AND DATE_FORMAT(t2.created_at, '%Y-%m') = DATE_FORMAT(NOW(), '%Y-%m'))
                       -
                       (SELECT COALESCE(SUM(t3.amount), 0) FROM transactions t3 JOIN orders o3 ON t3.order_id = o3.id WHERE o3.seller_id = u.id AND t3.status = 'completed' AND DATE_FORMAT(t3.created_at, '%Y-%m') = DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 MONTH), '%Y-%m'))
                      ) * 100.0 / NULLIF((SELECT COALESCE(SUM(t3.amount), 0) FROM transactions t3 JOIN orders o3 ON t3.order_id = o3.id WHERE o3.seller_id = u.id AND t3.status = 'completed' AND DATE_FORMAT(t3.created_at, '%Y-%m') = DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 MONTH), '%Y-%m')), 0),
                      0
                  ) as growth
                  FROM users u 
                  LEFT JOIN orders o ON o.seller_id = u.id 
                  LEFT JOIN transactions t ON t.order_id = o.id AND t.status = 'completed'
                  LEFT JOIN userrating ur ON ur.rated_id = u.id
                  WHERE u.role = 'user' 
                  GROUP BY u.id, u.username
                  HAVING revenue > 0 AND avg_rating >= 0 AND cancel_rate < 100 AND growth >= 0
                  ORDER BY revenue DESC 
                  LIMIT :limit";

        error_log("detectPotentialSellers Query: $query");
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        error_log("detectPotentialSellers Result: " . print_r($result, true));
        return $result;
    }

    public function detectViolatingSellers($limit = 10)
    {
        $query = "SELECT u.id, u.username, 
              COALESCE(AVG(ur.rating), 0) as avg_rating, 
              COALESCE((SELECT COUNT(o2.id) FROM orders o2 WHERE o2.seller_id = u.id AND o2.status = 'cancelled'), 0) as cancellations,
              COALESCE((SELECT COUNT(r.id) FROM reports r WHERE r.reported_user_id = u.id), 0) as reports,
              COALESCE(
                  (SELECT COUNT(r2.id) * 100.0 / NULLIF((SELECT COUNT(o3.id) FROM orders o3 WHERE o3.seller_id = u.id), 0)
                   FROM reports r2 
                   WHERE r2.reported_user_id = u.id),
                  0
              ) as report_rate
              FROM users u 
              LEFT JOIN userrating ur ON ur.rated_id = u.id
              LEFT JOIN reports r ON r.reported_user_id = u.id
              WHERE u.role = 'user'
              GROUP BY u.id, u.username 
              HAVING reports > 0 OR cancellations > 0 OR avg_rating < 4.5
              ORDER BY reports DESC 
              LIMIT :limit";

        error_log("detectViolatingSellers Query: $query");
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        error_log("detectViolatingSellers Result: " . print_r($result, true));
        return $result;
    }
}
