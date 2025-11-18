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
    $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch(\PDO::FETCH_ASSOC);

    // 1. Không tìm thấy user
    if (!$user) {
        return false;
    }

    // 2. Sai mật khẩu
    if (!password_verify($password, $user['password'])) {
        return false;
    }

    // 3. Chưa xác minh email (chưa nhập OTP đúng)
    if ($user['is_active'] == 0) {
        return 'not_verified';  // ← RẤT QUAN TRỌNG
    }

    // 4. Bị admin khóa (tùy bạn định nghĩa: 2 = khóa, hoặc thêm cột is_banned)
    if ($user['is_active'] == 2) {  // hoặc bạn dùng cột riêng: is_banned = 1
        return 'locked';
    }

    // 5. Thành công → trả về user
    return $user;
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
            // Lưu OTP khi đăng ký
    public function setVerificationCode($email, $code)
{
    $expiresAt = date('Y-m-d H:i:s', strtotime('+5 minutes'));

    $stmt = $this->db->prepare("
        UPDATE users 
        SET verification_code = ?, 
            verification_expires = ? 
        WHERE email = ?
    ");

    // Thêm dòng này để debug (xem có lỗi SQL không)
    $success = $stmt->execute([$code, $expiresAt, $email]);

    // Nếu lỗi → ghi log để biết
    if (!$success) {
        error_log("Lỗi lưu OTP cho email: $email | Error: " . implode(' | ', $stmt->errorInfo()));
    }

    return $success;
}

    // Kiểm tra OTP có đúng và còn hạn không
    public function verifyCode($email, $code)
    {
        $stmt = $this->db->prepare("
            SELECT * FROM users 
            WHERE email = ? 
            AND verification_code = ? 
            AND verification_expires > NOW()
        ");
        $stmt->execute([$email, $code]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($user) {
            // XÓA OTP + KÍCH HOẠT TÀI KHOẢN
            $this->db->prepare("
                UPDATE users 
                SET verification_code = NULL, 
                    verification_expires = NULL, 
                    is_active = 1 
                WHERE id = ?
            ")->execute([$user['id']]);

            return $user;
        }
        return false;
    }

    // Kiểm tra tài khoản đã xác minh chưa (dùng khi đăng nhập)
    public function isVerified($userId)
    {
        $stmt = $this->db->prepare("SELECT is_active FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $user && $user['is_active'] == 1;
    }
        public function findByUsername($username)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        return $stmt->fetch(\PDO::FETCH_ASSOC); // trả về false nếu không tìm thấy
    }
        public function getBankAccount($userId)
    {
        $sql = "SELECT * FROM bank_accounts WHERE user_id = ? AND is_default = 1 LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetch(); // trả về mảng hoặc null
    }

    public function saveBankAccount($data)
    {
        // Xóa cũ
        $this->db->prepare("DELETE FROM bank_accounts WHERE user_id = ?")->execute([$data['user_id']]);

        // Thêm mới
        $sql = "INSERT INTO bank_accounts 
                (user_id, bank_name, bank_short_name, account_number, account_holder, branch, logo, bank_code, is_default, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, NOW())";
        $this->db->prepare($sql)->execute([
            $data['user_id'],
            $data['bank_name'],
            $data['bank_short_name'] ?? $data['bank_name'],
            $data['account_number'],
            $data['account_holder'],
            $data['branch'] ?? '',
            $data['logo'] ?? '',
            $data['bank_code'] ?? ''
        ]);
    }
}
