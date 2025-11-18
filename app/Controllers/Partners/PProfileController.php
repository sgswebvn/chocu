<?php

namespace App\Controllers\Partners;

use App\Helpers\Session;
use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\WebSocket\NotificationServer;

class PProfileController
{
    private $userModel;
    private $productModel;
    private $orderModel;
    private $db;

    public function __construct()
    {
        $this->userModel = new User();
        $this->productModel = new Product();
        $this->orderModel = new Order();
        $this->db = (new \App\Config\Database())->getConnection();


    }
    public function updateProfile()
    {
        if (!Session::get('user')) {
            Session::set('error', 'Vui lòng đăng nhập để cập nhật hồ sơ!');
            header('Location: /login');
            exit;
        }

        $user = Session::get('user');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');

            // Validate
            if (empty($username)) {
                $response = ['success' => false, 'message' => 'Vui lòng nhập tên người dùng!'];
            } elseif (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $response = ['success' => false, 'message' => 'Vui lòng nhập email hợp lệ!'];
            } else {
                if ($this->userModel->updateProfile($user['id'], $username, $email)) {
                    $updatedUser = $this->userModel->findById($user['id']);
                    Session::set('user', $updatedUser);
                    NotificationServer::sendNotification(
                        $user['id'],
                        'profile',
                        [
                            'title' => 'Cập nhật hồ sơ',
                            'message' => 'Hồ sơ của bạn đã được cập nhật thành công!',
                            'link' => '/partners/profile'
                        ]
                    );
                    $response = ['success' => true, 'message' => 'Cập nhật hồ sơ thành công!', 'redirect' => '/partners/profile'];
                } else {
                    $response = ['success' => false, 'message' => 'Cập nhật hồ sơ thất bại! Email hoặc tên người dùng đã tồn tại.'];
                }
            }

            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode($response);
                exit;
            }

            Session::set($response['success'] ? 'success' : 'error', $response['message']);
            header('Location: /partners/profile');
            exit;
        }

        require_once __DIR__ . '/../../Views/a-partner/profile/edit.php';
    }

public function index()
{
    if (!Session::get('user')) {
        Session::set('error', 'Vui lòng đăng nhập!');
        header('Location: /login'); exit;
    }

    $user = Session::get('user');
    if ($user['role'] !== 'partners' || !$user['is_partner_paid']) {
        Session::set('error', 'Bạn cần nâng cấp tài khoản đối tác!');
        header('Location: /upgrade'); exit;
    }

    $userId = $user['id'];

    // LẤY NGÂN HÀNG

    $stmt = $this->db->prepare("SELECT * FROM bank_accounts WHERE user_id = ? LIMIT 1");
$stmt->bindValue(1, (int)$userId, PDO::PARAM_INT);
$stmt->execute();
$bankAccount = $stmt->fetch(PDO::FETCH_ASSOC);


    require_once __DIR__ . '/../../Views/a-partner/profile/index.php';
}

public function saveBank()
{
    if (!Session::get('user')) {
        header('Location: /login'); exit;
    }

    $userId = Session::get('user')['id'];

    $account_number  = trim($_POST['account_number'] ?? '');
    $account_holder  = trim($_POST['account_holder'] ?? '');
    $bank_name       = trim($_POST['bank_name'] ?? '');
    $branch          = trim($_POST['branch'] ?? '');
    $logo            = trim($_POST['logo'] ?? '');
    $bank_code       = trim($_POST['bank_code'] ?? '');
    $bank_short_name = trim($_POST['bank_short_name'] ?? $bank_name);

    if (empty($account_number) || empty($account_holder) || empty($bank_name)) {
        Session::set('error', 'Vui lòng điền đầy đủ thông tin ngân hàng!');
        header('Location: /partners/profile');
        exit;
    }

    try {
        // DÙNG $this->db → ĐÃ CÓ SẴN TỪ __construct()
        $this->db->prepare("DELETE FROM bank_accounts WHERE user_id = ?")->execute([$userId]);

        $sql = "INSERT INTO bank_accounts 
                (user_id, bank_name, bank_short_name, account_number, account_holder, branch, logo, bank_code, is_default, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, NOW())";

        $this->db->prepare($sql)->execute([
            $userId,
            $bank_name,
            $bank_short_name,
            $account_number,
            $account_holder,
            $branch,
            $logo,
            $bank_code
        ]);

        Session::set('success', 'Liên kết ngân hàng thành công! Bạn đã có thể rút tiền.');
    } catch (Exception $e) {
        Session::set('error', 'Lỗi hệ thống: ' . $e->getMessage());
        error_log($e->getMessage());
    }

    header('Location: /partners/profile');
    exit;
}
}
