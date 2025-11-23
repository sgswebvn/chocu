<?php

namespace App\Models;

use App\Config\Database;

class Cart
{
    private $db;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
    }

    public function add($userId, $productId, $quantity)
    {
        $stmt = $this->db->prepare("SELECT id FROM products WHERE id = ? AND status = 'approved'");
        $stmt->execute([$productId]);
        if (!$stmt->fetch()) {
            return false;
        }

        $stmt = $this->db->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$userId, $productId]);
        $cartItem = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($cartItem) {
            $newQuantity = $cartItem['quantity'] + $quantity;
            $stmt = $this->db->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
            return $stmt->execute([$newQuantity, $cartItem['id']]);
        } else {
            $stmt = $this->db->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
            return $stmt->execute([$userId, $productId, $quantity]);
        }
    }

    public function getByUser($userId)
    {
        $stmt = $this->db->prepare("
            SELECT c.*, p.title, p.price, p.image 
            FROM cart c 
            JOIN products p ON c.product_id = p.id 
            WHERE c.user_id = ?
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function remove($userId, $productId)
    {
        $stmt = $this->db->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
        return $stmt->execute([$userId, $productId]);
    }

    public function clear($userId)
    {
        $stmt = $this->db->prepare("DELETE FROM cart WHERE user_id = ?");
        return $stmt->execute([$userId]);
    }
        public function getSelectedItems($userId, $productIds)
    {
        if (empty($productIds) || !is_array($productIds)) {
            return [];
        }

        $placeholders = str_repeat('?,', count($productIds) - 1) . '?';
        $sql = "SELECT c.*, p.title, p.price, p.image 
                FROM cart c 
                JOIN products p ON c.product_id = p.id 
                WHERE c.user_id = ? AND c.product_id IN ($placeholders)";

        $stmt = $this->db->prepare($sql);
        $params = array_merge([$userId], $productIds);
        $stmt->execute($params);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
