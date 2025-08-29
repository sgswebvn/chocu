<?php

namespace App\Models;

use App\Config\Database;

class User
{
    private $db;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
    }

    public function registerUser($username, $email, $password, $is_active = 0, $role = 'user')
    {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("INSERT INTO users (username, email, password, is_active, role, is_partner_paid) VALUES (?, ?, ?, ?, ?, 0)");
        return $stmt->execute([$username, $email, $hashedPassword, $is_active, $role]);
    }

    public function login($email, $password)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            if ($user['is_active'] == 1) {
                return 'locked';
            }
            return $user;
        }
        return false;
    }

    public function loginWithGoogle($email, $googleId, $username)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ? OR google_id = ?");
        $stmt->execute([$email, $googleId]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($user) {
            if ($user['is_active'] == 1) {
                return 'locked';
            }
            if ($user['role'] === 'partners') {
                return 'partner_not_allowed';
            }
            $stmt = $this->db->prepare("UPDATE users SET google_id = ? WHERE id = ?");
            $stmt->execute([$googleId, $user['id']]);
            return $this->findById($user['id']);
        } else {
            $stmt = $this->db->prepare("INSERT INTO users (username, email, google_id, is_active, role, is_partner_paid) VALUES (?, ?, ?, 1, 'user', 0)");
            $stmt->execute([$username, $email, $googleId]);
            return $this->findById($this->db->lastInsertId());
        }
    }

    public function findByEmail($email)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function findById($id)
    {
        $stmt = $this->db->prepare("SELECT id, username, email, role, is_active, is_partner_paid, google_id, reset_token, reset_token_expires, images FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function updatePassword($userId, $newPassword)
    {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("UPDATE users SET password = ? WHERE id = ?");
        return $stmt->execute([$hashedPassword, $userId]);
    }

    public function saveResetToken($userId, $token)
    {
        $stmt = $this->db->prepare("UPDATE users SET reset_token = ?, reset_token_expires = ? WHERE id = ?");
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
        return $stmt->execute([$token, $expiry, $userId]);
    }

    public function findByResetToken($token)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE reset_token = ? AND reset_token_expires > NOW()");
        $stmt->execute([$token]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function clearResetToken($userId)
    {
        $stmt = $this->db->prepare("UPDATE users SET reset_token = NULL, reset_token_expires = NULL WHERE id = ?");
        return $stmt->execute([$userId]);
    }

    public function updatePartnerStatus($userId, $status)
    {
        $stmt = $this->db->prepare("UPDATE users SET is_partner_paid = ? WHERE id = ?");
        return $stmt->execute([$status, $userId]);
    }

    public function updateProfile($userId, $username, $email)
    {
        $stmt = $this->db->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
        return $stmt->execute([$username, $email, $userId]);
    }

    public function countAllByUser($userId)
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM products WHERE user_id = ?");
        $stmt->execute([$userId]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }
    public function getProductsByUser($userId)
    {
        try {
            // Kiểm tra xem user có phải shop không
            $user = $this->findById($userId);
            if (!$user) {
                error_log("User ID: $userId not found, no products fetched");
                return [];
            }
            if ($user['is_partner_paid'] != 1) {
                error_log("User ID: $userId is not a partner shop, no products fetched");
                return [];
            }

            $stmt = $this->db->prepare("
                SELECT p.*, u.is_partner_paid
                FROM products p
                JOIN users u ON p.user_id = u.id
                WHERE p.user_id = ? AND p.status = 'approved'
                ORDER BY p.created_at DESC
            ");
            $stmt->execute([$userId]);
            $products = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            error_log("Fetched " . count($products) . " active products for shop ID: $userId");
            return $products;
        } catch (\PDOException $e) {
            error_log("Error fetching products for user ID: $userId - " . $e->getMessage());
            return [];
        }
    }
}
