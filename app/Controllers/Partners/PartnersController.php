<?php

namespace App\Controllers\Partners;

use App\Helpers\Session;
use App\Models\User;
use App\Models\Notification;
use App\Models\Order;
use App\Config\Database;
use App\WebSocket\NotificationServer;

class PartnersController
{
    private $userModel;
    private $db;
    private $notificationModel;
    private $orderModel;

    public function __construct()
    {
        $this->userModel = new User();
        $this->db = (new Database())->getConnection();
        $this->notificationModel = new Notification();
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
        if ($user['role'] !== 'partners' || !isset($user['is_partner_paid']) || !$user['is_partner_paid']) {
            Session::set('error', 'Bạn cần nâng cấp tài khoản đối tác để truy cập!');
            header('Location: /upgrade');
            exit;
        }

        $totalProducts = $this->userModel->countAllByUser($user['id']);
        $totalTransactions = $this->countTransactions($user['id']);
        $totalReviews = $this->countReviews($user['id']);
        $notifications = $this->notificationModel->getByUser($user['id'], 10, 0);
        $topSellingProducts = $this->orderModel->getTopSellingProducts($user['id']);
        $revenueByProduct = $this->orderModel->getRevenueByProduct($user['id']);
        $revenueByCategory = $this->orderModel->getRevenueByCategory($user['id']);
        $revenueByPeriod = $this->orderModel->getRevenueByPeriod($user['id']);
        $cancellationRate = $this->orderModel->getCancellationRate($user['id']);
        $totalRevenue = $this->getTotalRevenue($user['id']);
        $completedOrders = $this->getCompletedOrders($user['id']);
        $storeViews = $this->getStoreViews($user['id']);
        $potentialProducts = $this->getPotentialProducts($user['id']);

        $data = [
            'user' => $user,
            'total_products' => $totalProducts,
            'total_transactions' => $totalTransactions,
            'total_reviews' => $totalReviews,
            'notifications' => $notifications,
            'top_selling_products' => $topSellingProducts,
            'revenue_by_product' => $revenueByProduct,
            'revenue_by_category' => $revenueByCategory,
            'revenue_by_period' => $revenueByPeriod,
            'cancellation_rate' => $cancellationRate,
            'total_revenue' => $totalRevenue,
            'completed_orders' => $completedOrders,
            'store_views' => $storeViews,
            'potential_products' => $potentialProducts
        ];

        require_once __DIR__ . '/../../Views/a-partner/index.php';
    }

    public function updateProfile()
    {
        if (!Session::get('user')) {
            Session::set('error', 'Vui lòng đăng nhập để cập nhật hồ sơ!');
            header('Location: /login');
            exit;
        }

        $user = Session::get('user');
        if ($user['role'] !== 'partners' || !isset($user['is_partner_paid']) || !$user['is_partner_paid']) {
            Session::set('error', 'Bạn cần nâng cấp tài khoản đối tác để cập nhật hồ sơ!');
            header('Location: /upgrade');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Session::set('error', 'Yêu cầu không hợp lệ!');
            header('Location: /partners');
            exit;
        }

        $username = $_POST['username'] ?? '';
        $email = $_POST['email'] ?? '';

        if (empty($username) || empty($email)) {
            Session::set('error', 'Vui lòng điền đầy đủ thông tin!');
            header('Location: /partners');
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Session::set('error', 'Email không hợp lệ!');
            header('Location: /partners');
            exit;
        }

        if ($this->userModel->updateProfile($user['id'], $username, $email)) {
            $updatedUser = $this->userModel->findById($user['id']);
            Session::set('user', $updatedUser);
            $this->notificationModel->create(
                $user['id'],
                'auth',
                'Cập nhật hồ sơ',
                'Hồ sơ của bạn đã được cập nhật thành công!',
                '/partners'
            );
            NotificationServer::sendNotification(
                $user['id'],
                'auth',
                [
                    'title' => 'Cập nhật hồ sơ',
                    'message' => 'Hồ sơ của bạn đã được cập nhật thành công!',
                    'link' => '/partners'
                ]
            );
            Session::set('success', 'Cập nhật hồ sơ thành công!');
        } else {
            Session::set('error', 'Cập nhật hồ sơ thất bại!');
        }

        header('Location: /partners');
        exit;
    }

    private function countTransactions($userId)
    {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM transactions WHERE user_id = ?");
            $stmt->execute([$userId]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $result['total'] ?? 0;
        } catch (\PDOException $e) {
            error_log("Error counting transactions for user ID: $userId - " . $e->getMessage());
            return 0;
        }
    }

    private function countReviews($userId)
    {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM seller_ratings WHERE seller_id = ?");
            $stmt->execute([$userId]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            error_log("Counted " . ($result['total'] ?? 0) . " seller reviews for seller ID: $userId");
            return $result['total'] ?? 0;
        } catch (\PDOException $e) {
            error_log("Error counting seller reviews for user ID: $userId - " . $e->getMessage());
            return 0;
        }
    }
    public function getPotentialProducts($sellerId)
    {
        $stmt = $this->db->prepare("
        SELECT title, potential_score
        FROM products
        WHERE seller_id = ?
        ORDER BY potential_score DESC
        LIMIT 5
    ");
        $stmt->execute([$sellerId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    private function getTotalRevenue($userId)
    {
        try {
            $stmt = $this->db->prepare("
            SELECT COALESCE(SUM(total_price), 0) as total_revenue
            FROM orders
            WHERE seller_id = ? AND status = 'delivered'
        ");
            $stmt->execute([$userId]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $result['total_revenue'] ?? 0;
        } catch (\PDOException $e) {
            error_log("Error getting total revenue for user ID: $userId - " . $e->getMessage());
            return 0;
        }
    }
    private function getCompletedOrders($userId)
    {
        try {
            $stmt = $this->db->prepare("
            SELECT COUNT(*) as completed_orders
            FROM orders
            WHERE seller_id = ? AND status = 'delivered'
        ");
            $stmt->execute([$userId]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $result['completed_orders'] ?? 0;
        } catch (\PDOException $e) {
            error_log("Error getting completed orders for user ID: $userId - " . $e->getMessage());
            return 0;
        }
    }
    private function getStoreViews($userId)
    {
        try {
            $stmt = $this->db->prepare("
            SELECT COALESCE(SUM(views), 0) as store_views
            FROM products
            WHERE seller_id = ?
        ");
            $stmt->execute([$userId]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $result['store_views'] ?? 0;
        } catch (\PDOException $e) {
            error_log("Error getting store views for user ID: $userId - " . $e->getMessage());
            return 0;
        }
    }

    public function personalInfo()
    {
        if (!Session::get('user')) {
            Session::set('error', 'Vui lòng đăng nhập để xem hồ sơ!');
            header('Location: /login');
            exit;
        }

        $user = Session::get('user');
        if ($user['role'] !== 'partners' || !isset($user['is_partner_paid']) || !$user['is_partner_paid']) {
            Session::set('error', 'Bạn cần nâng cấp tài khoản đối tác để truy cập!');
            header('Location: /upgrade');
            exit;
        }

        $data = [
            'user' => $user,
            'notifications' => $this->notificationModel->getByUser($user['id'], 10, 0)
        ];

        require_once __DIR__ . '/../../Views/a-partner/profile/index.php';
    }

    public function updateProfilePartner()
    {
        if (!Session::get('user')) {
            Session::set('error', 'Vui lòng đăng nhập để cập nhật hồ sơ!');
            header('Location: /login');
            exit;
        }

        $user = Session::get('user');
        if ($user['role'] !== 'partners' || !isset($user['is_partner_paid']) || !$user['is_partner_paid']) {
            Session::set('error', 'Bạn cần nâng cấp tài khoản đối tác để cập nhật hồ sơ!');
            header('Location: /upgrade');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Session::set('error', 'Yêu cầu không hợp lệ!');
            header('Location: /partners/profile');
            exit;
        }

        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $imagePath = $user['images'] ?? '';

        if (empty($username) || empty($email)) {
            Session::set('error', 'Vui lòng điền đầy đủ thông tin!');
            header('Location: /partners/profile');
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Session::set('error', 'Email không hợp lệ!');
            header('Location: /partners/profile');
            exit;
        }

        // Xử lý tải lên ảnh
        $newImage = $this->handleImageUpload($user['id']);
        if ($newImage === null && !empty($_FILES['image']['name'])) {
            Session::set('error', 'Tải lên ảnh thất bại!');
            header('Location: /partners/profile');
            exit;
        }
        $imagePath = $newImage ?: ($user['images'] ?? '');

        // Cập nhật thông tin người dùng
        $stmt = $this->db->prepare("UPDATE users SET username = ?, email = ?, images = ? WHERE id = ?");
        if (!$stmt->execute([$username, $email, $imagePath, $user['id']])) {
            $errorInfo = $stmt->errorInfo();
            error_log("SQL Error: " . print_r($errorInfo, true));
            Session::set('error', 'Cập nhật hồ sơ thất bại! Lỗi: ' . $errorInfo[2]);
            header('Location: /partners/profile');
            exit;
        }

        $updatedUser = $this->userModel->findById($user['id']);
        error_log("Updated user data: " . print_r($updatedUser, true));
        Session::set('user', $updatedUser);

        $notificationResult = $this->notificationModel->create(
            $user['id'],
            'auth',
            'Cập nhật hồ sơ',
            'Hồ sơ của bạn đã được cập nhật thành công!',
            '/partners/profile'
        );
        error_log("Notification creation result: " . ($notificationResult ? 'Success' : 'Failed'));

        try {
            NotificationServer::sendNotification(
                $user['id'],
                'auth',
                [
                    'title' => 'Cập nhật hồ sơ',
                    'message' => 'Hồ sơ của bạn đã được cập nhật thành công!',
                    'link' => '/partners/profile'
                ]
            );
        } catch (\Exception $e) {
            error_log("WebSocket notification failed: " . $e->getMessage());
        }

        Session::set('success', 'Cập nhật hồ sơ thành công!');
        header('Location: /partners/profile');
        exit;
    }

    private function handleImageUpload($userId)
    {
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $user = $this->userModel->findById($userId);
            $targetDir = $user['is_partner_paid'] == 1 ?
                $_SERVER['DOCUMENT_ROOT'] . '/uploads/partners/' :
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
