<?php

namespace App\Controllers;

use App\Config\Database;
use App\Models\Product;
use App\Models\Order;
use App\Helpers\Session;
use App\Models\User;
use App\WebSocket\NotificationServer;

class ProfileController
{
    private $productModel;
    private $orderModel;
    private $userModel;
    private $db;

    public function __construct()
    {
        $this->productModel = new Product();
        $this->orderModel = new Order();
        $this->userModel = new User();
        $this->db = (new Database())->getConnection();
    }

    // Trang Tổng quan
    public function index()
    {
        if (!Session::get('user')) {
            Session::set('error', 'Vui lòng đăng nhập để xem hồ sơ!');
            header('Location: /login');
            exit;
        }
        require_once __DIR__ . '/../Views/profile/index.php';
    }

    // Trang Quản lý đơn hàng
    public function orders()
    {
        if (!Session::get('user')) {
            Session::set('error', 'Vui lòng đăng nhập để xem đơn hàng!');
            header('Location: /login');
            exit;
        }
        $userId = Session::get('user')['id'];
        $orders = $this->orderModel->getOrdersBySellerId($userId);
        require_once __DIR__ . '/../Views/profile/orders.php';
    }
    // Trang Đơn hàng của tôi (đã mua)
    public function myOrders()
    {
        if (!Session::get('user')) {
            Session::set('error', 'Vui lòng đăng nhập để xem đơn hàng!');
            header('Location: /login');
            exit;
        }
        $userId = Session::get('user')['id'];
        $orders = $this->orderModel->getOrdersByBuyerId($userId);
        require_once __DIR__ . '/../Views/profile/my-orders.php';
    }

    // Trang Quản lý sản phẩm
    public function products()
    {
        if (!Session::get('user')) {
            Session::set('error', 'Vui lòng đăng nhập để xem sản phẩm!');
            header('Location: /login');
            exit;
        }
        $userId = Session::get('user')['id'];
        $products = $this->productModel->getProductsByUserId($userId);
        require_once __DIR__ . '/../Views/profile/products.php';
    }

    // Trang Chi tiết tài khoản
    public function accountDetails()
    {
        if (!Session::get('user')) {
            Session::set('error', 'Vui lòng đăng nhập để xem chi tiết tài khoản!');
            header('Location: /login');
            exit;
        }
        require_once __DIR__ . '/../Views/profile/account-details.php';
    }
    public function updateAccountDetails()
    {
        if (!Session::get('user')) {
            Session::set('error', 'Vui lòng đăng nhập để cập nhật hồ sơ!');
            header('Location: /login');
            exit;
        }

        $user = Session::get('user');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Session::set('error', 'Yêu cầu không hợp lệ!');
            header('Location: /profile/account-details');
            exit;
        }

        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $imagePath = $user['images'] ?? '';

        if (empty($username) || empty($email)) {
            Session::set('error', 'Vui lòng điền đầy đủ thông tin!');
            header('Location: /profile/account-details');
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Session::set('error', 'Email không hợp lệ!');
            header('Location: /profile/account-details');
            exit;
        }

        // Xử lý tải lên ảnh
        $newImage = $this->handleImageUpload($user['id']);
        if ($newImage === null && !empty($_FILES['image']['name'])) {
            Session::set('error', 'Tải lên ảnh thất bại!');
            header('Location: /profile/account-details');
            exit;
        }
        $imagePath = $newImage ?: ($user['images'] ?? '');

        // Cập nhật thông tin người dùng
        $stmt = $this->db->prepare("UPDATE users SET username = ?, email = ?, images = ? WHERE id = ?");
        if (!$stmt->execute([$username, $email, $imagePath, $user['id']])) {
            $errorInfo = $stmt->errorInfo();
            error_log("SQL Error: " . print_r($errorInfo, true));
            Session::set('error', 'Cập nhật hồ sơ thất bại! Lỗi: ' . $errorInfo[2]);
            header('Location: /profile/account-details');
            exit;
        }

        $updatedUser = $this->userModel->findById($user['id']);
        error_log("Updated user data: " . print_r($updatedUser, true));
        Session::set('user', $updatedUser);

        try {
            NotificationServer::sendNotification(
                $user['id'],
                'auth',
                [
                    'title' => 'Cập nhật hồ sơ',
                    'message' => 'Hồ sơ của bạn đã được cập nhật thành công!',
                    'link' => '/profile/account-details'
                ]
            );
        } catch (\Exception $e) {
            error_log("WebSocket notification failed: " . $e->getMessage());
        }

        Session::set('success', 'Cập nhật hồ sơ thành công!');
        header('Location: /profile/account-details');
        exit;
    }

    private function handleImageUpload($userId)
    {
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $user = $this->userModel->findById($userId);
            $targetDir = $user['is_partner_paid'] == 1 ?
                $_SERVER['DOCUMENT_ROOT'] . '/Uploads/partners/' :
                $_SERVER['DOCUMENT_ROOT'] . '/Uploads/';
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0755, true);
                error_log("Created directory: $targetDir");
            }
            if (!is_writable($targetDir)) {
                error_log("Directory is not writable: $targetDir");
                return null;
            }
            $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
            $targetFile = $targetDir . $fileName;
            error_log("Attempting to upload image to: $targetFile");
            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
                error_log("Image uploaded successfully: $targetFile");
                return $fileName;
            } else {
                error_log("Failed to upload image: " . $_FILES['image']['error']);
                return null;
            }
        } else {
            error_log("No file uploaded or upload error: " . ($_FILES['image']['error'] ?? 'No file'));
            return null;
        }
    }
}
