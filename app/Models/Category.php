<?php

namespace App\Models;

use App\Config\Database;

class Category
{
    private $db;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
    }

    public function getAll()
    {
        try {
            $stmt = $this->db->prepare("
                SELECT c.id, c.name, COUNT(p.id) as product_count
                FROM categories c
                LEFT JOIN products p ON c.id = p.category_id AND p.status = 'approved'
                GROUP BY c.id, c.name
                ORDER BY c.name ASC
            ");
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            throw new \Exception("Lỗi khi lấy danh sách danh mục: " . $e->getMessage());
        }
    }

    public function find($id)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM categories WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            throw new \Exception("Lỗi khi tìm danh mục: " . $e->getMessage());
        }
    }

    public function create($name)
    {
        try {
            // Kiểm tra trùng lặp tên danh mục
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM categories WHERE name = ?");
            $stmt->execute([$name]);
            if ($stmt->fetchColumn() > 0) {
                throw new \Exception("Tên danh mục đã tồn tại!");
            }

            $stmt = $this->db->prepare("INSERT INTO categories (name) VALUES (?)");
            return $stmt->execute([$name]);
        } catch (\PDOException $e) {
            throw new \Exception("Lỗi khi tạo danh mục: " . $e->getMessage());
        }
    }

    public function update($id, $name)
    {
        try {
            // Kiểm tra trùng lặp tên danh mục (trừ danh mục hiện tại)
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM categories WHERE name = ? AND id != ?");
            $stmt->execute([$name, $id]);
            if ($stmt->fetchColumn() > 0) {
                throw new \Exception("Tên danh mục đã tồn tại!");
            }

            $stmt = $this->db->prepare("UPDATE categories SET name = ?, updated_at = NOW() WHERE id = ?");
            return $stmt->execute([$name, $id]);
        } catch (\PDOException $e) {
            throw new \Exception("Lỗi khi cập nhật danh mục: " . $e->getMessage());
        }
    }

    public function delete($id)
    {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
            $stmt->execute([$id]);
            if ($stmt->fetchColumn() > 0) {
                throw new \Exception("Không thể xóa danh mục vì có sản phẩm liên kết!");
            }

            $stmt = $this->db->prepare("DELETE FROM categories WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (\PDOException $e) {
            throw new \Exception("Lỗi khi xóa danh mục: " . $e->getMessage());
        }
    }
}
