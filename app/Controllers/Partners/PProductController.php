<?php

namespace App\Controllers\Partners;

use App\Helpers\Session;
use App\Models\Partners\Product;
use App\Models\Category;
use App\Models\Notification;
use App\Models\User;
use App\WebSocket\NotificationServer;

class PProductController
{
    private $productModel;
    private $categoryModel;
    private $notificationModel;
    private $userModel;

    public function __construct()
    {
        $this->productModel = new Product();
        $this->categoryModel = new Category();
        $this->notificationModel = new Notification();
        $this->userModel = new User();
        if (!Session::get('user')) {
            Session::set('error', 'Vui lòng đăng nhập để quản lý sản phẩm!');
            header('Location: /login');
            exit;
        }
        $user = Session::get('user');
        if ($user['role'] !== 'partners' || !isset($user['is_partner_paid']) || !$user['is_partner_paid']) {
            Session::set('error', 'Bạn cần nâng cấp tài khoản đối tác để quản lý sản phẩm!');
            header('Location: /upgrade');
            exit;
        }
    }

    public function index()
    {
        $user = Session::get('user');
        $products = $this->productModel->getByUser($user['id']);
        require_once __DIR__ . '/../../Views/a-partner/product/index.php';
    }

    public function create()
    {
        $categories = $this->categoryModel->getAll();
        require_once __DIR__ . '/../../Views/a-partner/product/create.php';
    }

    public function store()
    {
        $user = Session::get('user');

        // Chỉ xử lý nếu là phương thức POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /partners/product/create');
            exit;
        }

        // Lấy dữ liệu từ form
        $title = trim($_POST['title'] ?? '');
        $categoryId = $_POST['category_id'] ?? '';
        $description = trim($_POST['description'] ?? '');
        $price = floatval($_POST['price'] ?? 0);
        $userId = $user['id'];
        $sellerId = $user['id'];

        // Kiểm tra dữ liệu đầu vào
        if ($title === '') {
            return $this->redirectBackWithError('Vui lòng nhập tiêu đề sản phẩm!');
        }
        if ($categoryId === '') {
            return $this->redirectBackWithError('Vui lòng chọn danh mục!');
        }
        if ($description === '') {
            return $this->redirectBackWithError('Vui lòng nhập mô tả sản phẩm!');
        }
        if ($price <= 0) {
            return $this->redirectBackWithError('Vui lòng nhập giá hợp lệ (lớn hơn 0)!');
        }

        // Xử lý hình ảnh (nếu có)
        $image = $this->handleImageUpload($userId);
        if (!$image && !empty($_FILES['image']['name'])) {
            return $this->redirectBackWithError('Tải hình ảnh thất bại!');
        }

        // Thêm sản phẩm vào database
        $success = $this->productModel->create(
            $userId,
            $sellerId,
            $title,
            $description,
            $price,
            $image,
            $categoryId
        );

        if ($success) {
            // Tạo thông báo
            $message = "Sản phẩm \"$title\" đã được đăng thành công!";
            $link = '/store/' . $userId;

            $this->notificationModel->create($userId, 'product', 'Sản phẩm mới', $message, $link);
            NotificationServer::sendNotification($userId, 'product', [
                'title' => 'Sản phẩm mới',
                'message' => $message,
                'link' => $link
            ]);

            Session::set('success', 'Đăng sản phẩm thành công!');
            header('Location: /partners/product');
            exit;
        }

        return $this->redirectBackWithError('Đăng sản phẩm thất bại!');
    }

    private function redirectBackWithError($message)
    {
        Session::set('error', $message);
        header('Location: /partners/product/create');
        exit;
    }

    public function edit($id)
    {
        $user = Session::get('user');
        $product = $this->productModel->find($id);

        if (!$product || $product['user_id'] != $user['id']) {
            return $this->redirectWithError('/partners/product', 'Bạn không có quyền chỉnh sửa sản phẩm này!');
        }

        $categories = $this->categoryModel->getAll();
        require_once __DIR__ . '/../../Views/a-partner/product/edit.php';
    }

    public function update($id)
    {
        $user = Session::get('user');
        $product = $this->productModel->find($id);

        if (!$product || $product['user_id'] != $user['id']) {
            return $this->redirectWithError('/partners/product', 'Bạn không có quyền cập nhật sản phẩm này!');
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->redirectWithError("/partners/product/edit/$id", 'Phương thức không hợp lệ!');
        }

        $title = trim($_POST['title'] ?? '');
        $categoryId = $_POST['category_id'] ?? '';
        $description = trim($_POST['description'] ?? '');
        $price = floatval($_POST['price'] ?? 0);

        // Validate dữ liệu
        if ($title === '') {
            return $this->redirectWithError("/partners/product/edit/$id", 'Vui lòng nhập tiêu đề sản phẩm!');
        }
        if ($categoryId === '') {
            return $this->redirectWithError("/partners/product/edit/$id", 'Vui lòng chọn danh mục!');
        }
        if ($description === '') {
            return $this->redirectWithError("/partners/product/edit/$id", 'Vui lòng nhập mô tả sản phẩm!');
        }
        if ($price <= 0) {
            return $this->redirectWithError("/partners/product/edit/$id", 'Vui lòng nhập giá hợp lệ (lớn hơn 0)!');
        }

        // Ảnh mới hoặc giữ ảnh cũ
        $image = $this->handleImageUpload($user['id']) ?? $product['image'];

        $success = $this->productModel->update($id, $title, $description, $price, $image, $categoryId);

        if ($success) {
            $message = "Sản phẩm \"$title\" đã được cập nhật!";
            $link = '/store/' . $user['id'];

            $this->notificationModel->create($user['id'], 'product', 'Chỉnh sửa sản phẩm', $message, $link);
            NotificationServer::sendNotification($user['id'], 'product', [
                'title' => 'Chỉnh sửa sản phẩm',
                'message' => $message,
                'link' => $link
            ]);

            Session::set('success', 'Cập nhật sản phẩm thành công!');
            header('Location: /partners/product');
            exit;
        }

        return $this->redirectWithError("/partners/product/edit/$id", 'Cập nhật sản phẩm thất bại!');
    }

    private function redirectWithError($url, $message)
    {
        Session::set('error', $message);
        header("Location: $url");
        exit;
    }

    public function delete($id)
    {
        $user = Session::get('user');
        $product = $this->productModel->find($id);
        if (!$product || $product['user_id'] != $user['id']) {
            Session::set('error', 'Bạn không có quyền xóa sản phẩm này!');
            header('Location: /partners/product');
            exit;
        }
        if ($this->productModel->delete($id)) {
            $this->notificationModel->create(
                $user['id'],
                'product',
                'Xóa sản phẩm',
                "Sản phẩm \"$product[title]\" đã được xóa!",
                '/partners/product'
            );
            NotificationServer::sendNotification(
                $user['id'],
                'product',
                [
                    'title' => 'Xóa sản phẩm',
                    'message' => "Sản phẩm \"$product[title]\" đã được xóa!",
                    'link' => '/partners/product'
                ]
            );
            Session::set('success', 'Xóa sản phẩm thành công!');
        } else {
            Session::set('error', 'Xóa sản phẩm thất bại!');
        }
        header('Location: /partners/product');
        exit;
    }

    private function handleImageUpload($userId)
    {
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $user = $this->userModel->findById($userId);
            $targetDir = $user['is_partner_paid'] == 1 ?
                __DIR__ . '/../../../public/uploads/partners/' :
                __DIR__ . '/../../../public/Uploads/';
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0755, true);
            }
            $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
            $targetFile = $targetDir . $fileName;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
                return $fileName;
            }
        }
        return null;
    }
}
