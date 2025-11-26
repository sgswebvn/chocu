<?php

namespace App\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Helpers\Session;
use App\Models\Notification;
use App\WebSocket\NotificationServer;

class ProductController
{
    private $productModel;
    private $categoryModel;
    private $notificationModel;
    private $db;

    public function __construct()
    {
        $this->productModel = new Product();
        $this->categoryModel = new Category();
        $this->notificationModel = new Notification();
        $this->db = (new \App\Config\Database())->getConnection();
    }

    public function index()
    {
        $keyword = $_GET['keyword'] ?? '';
        $sort = $_GET['sort'] ?? 'latest';
        $categoryId = $_GET['category_id'] ?? '';
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = 12;
        $offset = ($page - 1) * $limit;

        $products = $this->productModel->getAll($sort, $keyword, $limit, $offset, $categoryId);
        $totalProducts = $this->productModel->countAll($keyword, $categoryId);
        $totalPages = ceil($totalProducts / $limit);

        $categories = $this->categoryModel->getAll();

        require_once __DIR__ . '/../Views/products/index.php';
    }

    public function show($id)
    {
        $product = $this->productModel->find($id);
        if (!$product) {
            Session::set('error', 'Sản phẩm không tồn tại hoặc đã bị gỡ xuống!');
            header('Location: /products');
            exit;
        }
        $this->productModel->incrementViews($id);
        require_once __DIR__ . '/../Views/products/show.php';
    }

    public function create()
    {
        if (!Session::get('user')) {
            Session::set('error', 'Vui lòng đăng nhập để đăng sản phẩm!');
            header('Location: /login');
            exit;
        }

        // NGĂN USER BỊ KHÓA ĐĂNG SẢN PHẨM
        if (Session::get('user')['is_active'] != 1) {
            Session::set('error', 'Tài khoản của bạn đã bị khóa. Không thể đăng sản phẩm!');
            header('Location: /profile');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = trim($_POST['title'] ?? '');
            $categoryId = $_POST['category_id'] ?? '';
            $description = trim($_POST['description'] ?? '');
            $price = floatval($_POST['price'] ?? 0);
            $userId = Session::get('user')['id'];
            $seller_id = $userId;

            // Validate
            if (empty($title) || empty($categoryId) || empty($description) || $price <= 0) {
                Session::set('error', 'Vui lòng điền đầy đủ và chính xác thông tin sản phẩm!');
                header('Location: /products/create');
                exit;
            }

            $image = $this->handleImageUpload();
            if (!$image && !empty($_FILES['image']['name'])) {
                Session::set('error', 'Tải hình ảnh thất bại!');
                header('Location: /products/create');
                exit;
            }

            if ($this->productModel->create($userId, $seller_id, $title, $description, $price, $image, $categoryId)) {
                NotificationServer::sendNotification($userId, 'product', [
                    'title' => 'Đăng sản phẩm thành công',
                    'message' => "Sản phẩm \"$title\" đã được gửi duyệt!",
                    'link' => '/profile/products'
                ]);

                $admin = $this->db->query("SELECT id FROM users WHERE role = 'admin' LIMIT 1")->fetch();
                if ($admin) {
                    NotificationServer::sendNotification($admin['id'], 'product', [
                        'title' => 'Sản phẩm mới chờ duyệt',
                        'message' => "Sản phẩm \"$title\" đang chờ duyệt.",
                        'link' => '/admin/products'
                    ]);
                }

                Session::set('success', 'Đăng sản phẩm thành công! Đang chờ duyệt.');
                header('Location: /products/create');
                exit;
            }
        }

        $categories = $this->categoryModel->getAll();
        require_once __DIR__ . '/../Views/products/create.php';
    }

    public function edit($id)
    {
        if (!Session::get('user') || Session::get('user')['is_active'] != 1) {
            Session::set('error', 'Tài khoản không hợp lệ hoặc đã bị khóa!');
            header('Location: /login');
            exit;
        }

        $product = $this->productModel->getByUser(Session::get('user')['id']);
        $product = array_filter($product, fn($p) => $p['id'] == $id);
        $product = reset($product);

        if (!$product) {
            Session::set('error', 'Không tìm thấy sản phẩm hoặc bạn không có quyền!');
            header('Location: /profile/products');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = trim($_POST['title'] ?? '');
            $categoryId = $_POST['category_id'] ?? '';
            $description = trim($_POST['description'] ?? '');
            $price = floatval($_POST['price'] ?? 0);

            if (empty($title) || empty($categoryId) || empty($description) || $price <= 0) {
                Session::set('error', 'Thông tin không hợp lệ!');
            } else {
                $image = $this->handleImageUpload() ?? $product['image'];
                if ($this->productModel->update($id, $title, $description, $price, $image, $categoryId)) {
                    Session::set('success', 'Chỉnh sửa thành công! Đang chờ duyệt lại.');
                } else {
                    Session::set('error', 'Cập nhật thất bại!');
                }
            }
        }

        $categories = $this->categoryModel->getAll();
        require_once __DIR__ . '/../Views/products/edit.php';
    }

    public function delete($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            exit(json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ']));
        }

        $userId = Session::get('user')['id'] ?? 0;
        $product = $this->productModel->getByUser($userId);
        $product = array_filter($product, fn($p) => $p['id'] == $id);
        if (!$product) {
            exit(json_encode(['success' => false, 'message' => 'Không có quyền xóa!']));
        }

        $success = $this->productModel->delete($id);
        exit(json_encode(['success' => $success, 'message' => $success ? 'Xóa thành công!' : 'Xóa thất bại!']));
    }

    private function handleImageUpload()
    {
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $targetDir = __DIR__ . '/../../public/uploads/partners/';
            if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);
            $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
            $targetFile = $targetDir . $fileName;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
                return $fileName;
            }
        }
        return null;
    }
}