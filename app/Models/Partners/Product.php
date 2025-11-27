<?php

namespace App\Models\Partners;

use App\Config\Database;

class Product
{
    private $db;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
    }

    public function getAll($filter = 'latest', $keyword = '', $limit = 12, $offset = 0)
    {
        $sql = "SELECT p.*, u.username, c.name as category_name 
                FROM products p 
                LEFT JOIN users u ON p.user_id = u.id 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.status = 'approved'";
        $params = [];

        if (!empty($keyword)) {
            $sql .= " AND (p.title LIKE :keyword OR p.description LIKE :keyword)";
            $params[':keyword'] = '%' . $keyword . '%';
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

    public function getByUser($userId)
    {
        $stmt = $this->db->prepare("SELECT p.*, u.username, c.name as category_name 
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
        $stmt = $this->db->prepare("SELECT p.*, u.username, c.name as category_name 
                                    FROM products p 
                                    LEFT JOIN users u ON p.user_id = u.id 
                                    LEFT JOIN categories c ON p.category_id = c.id 
                                    WHERE p.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function create($userId, $sellerId, $title, $description, $price,  $image, $categoryId)
    {
        $stmt = $this->db->prepare("INSERT INTO products (user_id, seller_id, category_id, title, description, price,  image, status) 
                                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'approved')");
        return $stmt->execute([$userId, $sellerId, $categoryId, $title, $description, $price,  $image]);
    }

    public function update($id, $title, $description, $price,  $image, $categoryId)
    {
        if ($image) {
            $stmt = $this->db->prepare("UPDATE products SET category_id = ?, title = ?, description = ?, price = ?,  image = ?, status = 'approved' WHERE id = ?");
            return $stmt->execute([$categoryId, $title, $description, $price,  $image, $id]);
        } else {
            $stmt = $this->db->prepare("UPDATE products SET category_id = ?, title = ?, description = ?, price = ?,  status = 'approved' WHERE id = ?");
            return $stmt->execute([$categoryId, $title, $description, $price,  $id]);
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
}