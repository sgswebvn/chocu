<?php

namespace App\Models;

use App\Config\Database;

class Product
{
    private $db;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
    }

    public function getAll($filter = 'latest', $keyword = '', $limit = 12, $offset = 0, $categoryId = '')
    {
        $sql = "SELECT p.*, u.username, u.is_partner_paid, c.name as category_name 
                FROM products p 
                LEFT JOIN users u ON p.user_id = u.id 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.status = 'approved'";

        $params = [];

        if (!empty($keyword)) {
            $sql .= " AND (p.title LIKE :keyword OR p.description LIKE :keyword)";
            $params[':keyword'] = '%' . $keyword . '%';
        }

        if (!empty($categoryId)) {
            $sql .= " AND p.category_id = :category_id";
            $params[':category_id'] = $categoryId;
        }

        if ($filter === 'featured') {
            $sql .= " AND p.is_featured = 1 ORDER BY p.created_at DESC";
        } elseif ($filter === 'popular') {
            $sql .= " ORDER BY p.views DESC";
        } else {
            $sql .= " ORDER BY p.created_at DESC";
        }

        $sql .= " LIMIT :limit OFFSET :offset";
        $stmt = $this->db->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', (int)$limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, \PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function countAll($keyword = '')
    {
        $sql = "SELECT COUNT(*) as total FROM products WHERE status = 'approved'";
        $params = [];

        if (!empty($keyword)) {
            $sql .= " AND (title LIKE :keyword OR description LIKE :keyword)";
            $params[':keyword'] = '%' . $keyword . '%';
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    public function getProductById($id)
    {
        $stmt = $this->db->prepare("SELECT p.*, u.username, u.is_partner_paid, c.name as category_name 
                                    FROM products p 
                                    LEFT JOIN users u ON p.user_id = u.id 
                                    LEFT JOIN categories c ON p.category_id = c.id 
                                    WHERE p.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function getByUser($userId)
    {
        $stmt = $this->db->prepare("SELECT p.*, u.username, u.is_partner_paid, c.name as category_name 
                                    FROM products p 
                                    LEFT JOIN users u ON p.user_id = u.id 
                                    LEFT JOIN categories c ON p.category_id = c.id 
                                    WHERE p.user_id = ? 
                                    ORDER BY p.created_at DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function find($id)
    {
        $stmt = $this->db->prepare("SELECT p.*, u.username, u.is_partner_paid, c.name as category_name 
                                    FROM products p 
                                    LEFT JOIN users u ON p.user_id = u.id 
                                    LEFT JOIN categories c ON p.category_id = c.id 
                                    WHERE p.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function find2($id)
    {
        $stmt = $this->db->prepare("SELECT p.*, u.username, u.is_partner_paid, c.name as category_name 
                                    FROM products p 
                                    LEFT JOIN users u ON p.user_id = u.id 
                                    LEFT JOIN categories c ON p.category_id = c.id 
                                    WHERE p.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function search($keyword)
    {
        $keyword = "%$keyword%";
        $stmt = $this->db->prepare("SELECT p.*, u.username, u.is_partner_paid, c.name as category_name 
                                    FROM products p 
                                    LEFT JOIN users u ON p.user_id = u.id 
                                    LEFT JOIN categories c ON p.category_id = c.id 
                                    WHERE p.status = 'approved' 
                                    AND (p.title LIKE ? OR p.description LIKE ?)");
        $stmt->execute([$keyword, $keyword]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function create($userId, $seller_id, $title, $description, $price, $image, $categoryId)
    {
        $stmt = $this->db->prepare("INSERT INTO products (user_id, seller_id, category_id, title, description, price, image, status) 
                                    VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')");
        return $stmt->execute([$userId, $seller_id, $categoryId, $title, $description, $price, $image]);
    }

    public function getHotDeals()
    {
        $stmt = $this->db->prepare("SELECT p.*, u.username, u.is_partner_paid, c.name as category_name 
                                    FROM products p 
                                    LEFT JOIN users u ON p.user_id = u.id 
                                    LEFT JOIN categories c ON p.category_id = c.id 
                                    WHERE p.status = 'approved' 
                                    ORDER BY p.views DESC LIMIT 8");
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function update($id, $title, $description, $price, $image, $categoryId)
    {
        if ($image) {
            $stmt = $this->db->prepare("UPDATE products SET category_id = ?, title = ?, description = ?, price = ?, image = ?, status = 'pending' WHERE id = ?");
            return $stmt->execute([$categoryId, $title, $description, $price, $image, $id]);
        } else {
            $stmt = $this->db->prepare("UPDATE products SET category_id = ?, title = ?, description = ?, price = ?, status = 'pending' WHERE id = ?");
            return $stmt->execute([$categoryId, $title, $description, $price, $id]);
        }
    }

    public function delete($id)
    {
        $stmt = $this->db->prepare("DELETE FROM products WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function incrementViews($id)
    {
        $stmt = $this->db->prepare("UPDATE products SET views = views + 1 WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getProductsByUserId($userId)
    {
        $stmt = $this->db->prepare("SELECT p.*, u.username, u.is_partner_paid, c.name as category_name 
                                    FROM products p 
                                    LEFT JOIN users u ON p.user_id = u.id 
                                    LEFT JOIN categories c ON p.category_id = c.id 
                                    WHERE p.user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
