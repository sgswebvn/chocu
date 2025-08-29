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

    public function __construct()
    {
        $this->userModel = new User();
        $this->productModel = new Product();
        $this->orderModel = new Order();
    }

    public function index()
    {
        if (!Session::get('user')) {
            Session::set('error', 'Vui lòng đăng nhập để xem hồ sơ!');
            header('Location: /login');
            exit;
        }

        $user = Session::get('user');
        if ($user['role'] !== 'partners' || !$user['is_partner_paid']) {
            Session::set('error', 'Bạn cần nâng cấp tài khoản đối tác để truy cập!');
            header('Location: /upgrade');
            exit;
        }

        $userId = $user['id'];
        $productCount = $this->productModel->countAllByUser($userId);
        $orderCount = $this->orderModel->countOrdersBySellerId($userId);
        require_once __DIR__ . '/../../Views/a-partner/profile/index.php';
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
}
