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
            Session::set('error', 'Sản phẩm không tồn tại!');
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
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = trim($_POST['title'] ?? '');
            $categoryId = $_POST['category_id'] ?? '';
            $description = trim($_POST['description'] ?? '');
            $price = floatval($_POST['price'] ?? 0);
            $userId = Session::get('user')['id'];
            $seller_id = Session::get('user')['id'];

            // Validate
            if (empty($title)) {
                Session::set('error', 'Vui lòng nhập tiêu đề sản phẩm!');
                header('Location: /products/create');
                exit;
            }
            if (empty($categoryId)) {
                Session::set('error', 'Vui lòng chọn danh mục!');
                header('Location: /products/create');
                exit;
            }
            if (empty($description)) {
                Session::set('error', 'Vui lòng nhập mô tả sản phẩm!');
                header('Location: /products/create');
                exit;
            }
            if ($price <= 0) {
                Session::set('error', 'Vui lòng nhập giá hợp lệ (lớn hơn 0)!');
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
                // Thông báo cho người dùng
                NotificationServer::sendNotification(
                    $userId,
                    'product',
                    [
                        'title' => 'Đăng sản phẩm',
                        'message' => "Sản phẩm \"$title\" đã được đăng và đang chờ duyệt!",
                        'link' => '/profile/products'
                    ]
                );

                // Lấy ID admin
                $stmt = $this->db->prepare("SELECT id FROM users WHERE role = 'admin' LIMIT 1");
                $stmt->execute();
                $admin = $stmt->fetch(\PDO::FETCH_ASSOC);

                if ($admin) {
                    $adminId = $admin['id'];
                    $this->notificationModel->create(
                        $adminId,
                        'product',
                        'Sản phẩm mới chờ duyệt',
                        "Sản phẩm \"$title\" từ người dùng ID $userId đang chờ duyệt.",
                        '/admin/products'
                    );
                    NotificationServer::sendNotification(
                        $adminId,
                        'product',
                        [
                            'title' => 'Sản phẩm mới chờ duyệt',
                            'message' => "Sản phẩm \"$title\" từ người dùng ID $userId đang chờ duyệt.",
                            'link' => '/admin/products'
                        ]
                    );
                }

                Session::set('success', 'Đăng sản phẩm thành công! Đang chờ duyệt.');
                header('Location: /products/create');
                exit;
            } else {
                Session::set('error', 'Đăng sản phẩm thất bại!');
            }
        }
        $categories = $this->categoryModel->getAll();
        require_once __DIR__ . '/../Views/products/create.php';
    }

    public function edit($id)
    {
        if (!Session::get('user')) {
            Session::set('error', 'Vui lòng đăng nhập để chỉnh sửa!');
            header('Location: /login');
            exit;
        }
        $product = $this->productModel->find($id);
        if (!$product || $product['user_id'] != Session::get('user')['id']) {
            Session::set('error', 'Bạn không có quyền chỉnh sửa sản phẩm này!');
            header('Location: /profile/products');
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = trim($_POST['title'] ?? '');
            $categoryId = $_POST['category_id'] ?? '';
            $description = trim($_POST['description'] ?? '');
            $price = floatval($_POST['price'] ?? 0);

            // Validate
            if (empty($title)) {
                Session::set('error', 'Vui lòng nhập tiêu đề sản phẩm!');
                header('Location: /products/edit/' . $id);
                exit;
            }
            if (empty($categoryId)) {
                Session::set('error', 'Vui lòng chọn danh mục!');
                header('Location: /products/edit/' . $id);
                exit;
            }
            if (empty($description)) {
                Session::set('error', 'Vui lòng nhập mô tả sản phẩm!');
                header('Location: /products/edit/' . $id);
                exit;
            }
            if ($price <= 0) {
                Session::set('error', 'Vui lòng nhập giá hợp lệ (lớn hơn 0)!');
                header('Location: /products/edit/' . $id);
                exit;
            }

            $image = $this->handleImageUpload() ?? $product['image'];
            if ($this->productModel->update($id, $title, $description, $price, $image, $categoryId)) {
                NotificationServer::sendNotification(
                    Session::get('user')['id'],
                    'product',
                    [
                        'title' => 'Chỉnh sửa sản phẩm',
                        'message' => "Sản phẩm \"$title\" đã được chỉnh sửa và đang chờ duyệt!",
                        'link' => '/profile/products'
                    ]
                );
                Session::set('success', 'Chỉnh sửa thành công! Đang chờ duyệt.');
            } else {
                Session::set('error', 'Chỉnh sửa thất bại!');
            }
        }
        $categories = $this->categoryModel->getAll();
        require_once __DIR__ . '/../Views/products/edit.php';
    }

    public function delete($id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $product = $this->productModel->find($id);
            if (!$product || $product['user_id'] != Session::get('user')['id']) {
                $response = ['success' => false, 'message' => 'Bạn không có quyền xóa sản phẩm này!'];
            } elseif ($this->productModel->delete($id)) {
                $response = ['success' => true, 'message' => 'Xóa sản phẩm thành công!'];
            } else {
                $response = ['success' => false, 'message' => 'Xóa sản phẩm thất bại!'];
            }
            header('Content-Type: application/json');
            echo json_encode($response);
            exit;
        }
        Session::set('error', 'Yêu cầu không hợp lệ!');
        header('Location: /profile/products');
        exit;
    }

    private function handleImageUpload()
    {
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $targetDir = __DIR__ . '/../../public/uploads/partners/';
            $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
            $targetFile = $targetDir . $fileName;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
                return $fileName;
            }
        }
        return null;
    }
}
