<?php

namespace App\Models;

use App\Config\Database;

class OrderDetail
{
    private $db;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
    }

    public function getByOrderId($orderId)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM order_detail WHERE order_id = ?");
            $stmt->execute([$orderId]);
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error fetching order detail: " . $e->getMessage());
            return false;
        }
    }
}
