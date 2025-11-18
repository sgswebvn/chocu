<?php

namespace App\Controllers;

use App\Models\Admin;
use App\Helpers\Session;
use App\Models\Category;
use App\Models\Contact;
use App\Models\Product;
use App\Models\Report;
use App\Models\User;
use App\Helpers\EmailService;

class AdminController
{
    private $adminModel;
    private $categoryModel;
    private $productModel;
    private $contactModel;
    private $reportModel;
    private $userModel;
    private $emailService;
    
    public function __construct()
    {
        $this->adminModel = new Admin();
        $this->categoryModel = new Category();
        $this->productModel = new Product();
        $this->reportModel = new Report();
        $this->contactModel = new Contact();
        $this->userModel = new User();
        if (!Session::get('user') || Session::get('user')['role'] !== 'admin') {
            $this->redirect('/login');
        }
        $this->emailService = new EmailService();
    }

    private function redirect($url)
    {
        if (!headers_sent()) {
            header('Location: ' . $url);
            exit;
        }
    }

    public function index()
    {
        $categories = $this->categoryModel->getAll();
        require_once __DIR__ . '/../Views/admin/categories/index.php';
    }

    public function createCategory()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            if (empty($name)) {
                Session::set('error', 'Vui lòng nhập tên danh mục!');
                $this->redirect('/admin/categories/create');
            }
            try {
                if ($this->categoryModel->create($name)) {
                    Session::set('success', 'Thêm danh mục thành công!');
                    $this->redirect('/admin/categories');
                } else {
                    Session::set('error', 'Thêm danh mục thất bại!');
                }
            } catch (\Exception $e) {
                Session::set('error', 'Lỗi: ' . $e->getMessage());
            }
        }
        require_once __DIR__ . '/../Views/admin/categories/create.php';
    }

    public function editCategory($id)
    {
        $category = $this->categoryModel->find($id);
        if (!$category) {
            Session::set('error', 'Danh mục không tồn tại!');
            $this->redirect('/admin/categories');
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            if (empty($name)) {
                Session::set('error', 'Vui lòng nhập tên danh mục!');
                $this->redirect('/admin/categories/edit/' . $id);
            }
            try {
                if ($this->categoryModel->update($id, $name)) {
                    Session::set('success', 'Cập nhật danh mục thành công!');
                    $this->redirect('/admin/categories');
                } else {
                    Session::set('error', 'Cập nhật danh mục thất bại!');
                }
            } catch (\Exception $e) {
                Session::set('error', 'Lỗi: ' . $e->getMessage());
            }
        }
        require_once __DIR__ . '/../Views/admin/categories/edit.php';
    }

    public function deleteCategory($id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $category = $this->categoryModel->find($id);
                if (!$category) {
                    $response = ['success' => false, 'message' => 'Danh mục không tồn tại!'];
                } elseif ($this->categoryModel->delete($id)) {
                    $response = ['success' => true, 'message' => 'Xóa danh mục thành công!'];
                } else {
                    $response = ['success' => false, 'message' => 'Xóa danh mục thất bại!'];
                }
                if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                    header('Content-Type: application/json');
                    echo json_encode($response);
                    exit;
                } else {
                    Session::set($response['success'] ? 'success' : 'error', $response['message']);
                    $this->redirect('/admin/categories');
                }
            } catch (\Exception $e) {
                $response = ['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()];
                if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                    header('Content-Type: application/json');
                    echo json_encode($response);
                    exit;
                } else {
                    Session::set('error', $response['message']);
                    $this->redirect('/admin/categories');
                }
            }
        }
        Session::set('error', 'Yêu cầu không hợp lệ!');
        $this->redirect('/admin/categories');
    }

    public function dashboard()
    {
        $stats = $this->adminModel->getStats();
        $revenueDay = $this->adminModel->getRevenueByPeriod('day');
        $revenueMonth = $this->adminModel->getRevenueByPeriod('month');
        $revenueYear = $this->adminModel->getRevenueByPeriod('year');
        $topRevenueSellers = $this->adminModel->getSellerComparisons('revenue');
        $topGrowthSellers = $this->adminModel->getSellerComparisons('growth');
        $topRatingSellers = $this->adminModel->getSellerComparisons('ratings');
        $topCancelSellers = $this->adminModel->getSellerComparisons('cancellations');
        $topProducts = $this->adminModel->getTopSellingProducts(10);
        $topCategories = $this->adminModel->getTopSellingCategories(5);
        require_once __DIR__ . '/../Views/admin/dashboard.php';
    }

    public function potentialSellers()
    {
        $potential = $this->adminModel->detectPotentialSellers();
        require_once __DIR__ . '/../Views/admin/potential_sellers.php';
    }

    public function violatingSellers()
    {
        $violating = $this->adminModel->detectViolatingSellers();
        require_once __DIR__ . '/../Views/admin/violating_sellers.php';
    }

    public function products()
    {
        $products = $this->adminModel->getAllProducts();
        require_once __DIR__ . '/../Views/admin/products.php';
    }

  public function searchProducts()
{
    $keyword = $_GET['keyword'] ?? '';
    $status = $_GET['status'] ?? '';
    $products = $this->adminModel->searchProducts($keyword, $status);
    require_once __DIR__ . '/../Views/admin/products.php';
}

    public function updateProductStatus($id, $status)
    {
        $response = ['success' => false, 'message' => ''];
        try {
            if (!in_array($status, ['pending', 'approved', 'rejected'])) {
                $response['message'] = 'Trạng thái không hợp lệ!';
            } elseif ($this->adminModel->updateProductStatus($id, $status)) {
                $response['success'] = true;
                $response['message'] = 'Cập nhật trạng thái sản phẩm thành công!';
            } else {
                $response['message'] = 'Cập nhật trạng thái thất bại!';
            }
        } catch (\Exception $e) {
            $response['message'] = 'Lỗi: ' . $e->getMessage();
        }
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode($response);
            exit;
        } else {
            Session::set($response['success'] ? 'success' : 'error', $response['message']);
            $this->redirect('/admin/products');
        }
    }
    public function users()
    {
        $users = $this->adminModel->getAllUsers();
        require_once __DIR__ . '/../Views/admin/users.php';
    }

    public function searchUsers()
    {
        $keyword = $_GET['keyword'] ?? '';
        $users = $this->adminModel->searchUsers($keyword);
        require_once __DIR__ . '/../Views/admin/users.php';
    }

   public function toggleUserStatus($id, $action)
    {
        $is_active = $action === 'activate' ? 0 : 1; // 0: kích hoạt, 1: tạm khóa
        try {
            // Lấy thông tin người dùng để lấy email và username
            $user = $this->adminModel->getUserById($id);
            if (!$user) {
                Session::set('error', 'Không tìm thấy người dùng!');
                $this->redirect('/admin/users');
                return;
            }
            
            if ($this->adminModel->toggleUserStatus($id, $is_active)) {
                // Gửi email thông báo
                $this->emailService->sendActivationEmail(
                    $user['email'],
                    $user['username'],
                    $is_active === 0 // true nếu kích hoạt, false nếu tạm khóa
                );
                
                Session::set('success', 'Cập nhật trạng thái người dùng thành công!');
            } else {
                Session::set('error', 'Cập nhật trạng thái thất bại!');
            }
        } catch (\Exception $e) {
            Session::set('error', 'Lỗi: ' . $e->getMessage());
        }
        $this->redirect('/admin/users');
    }
    

    public function deleteReport($id)
    {
        try {
            if ($this->adminModel->deleteReport($id)) {
                Session::set('success', 'Xóa báo cáo thành công!');
            } else {
                Session::set('error', 'Xóa báo cáo thất bại!');
            }
        } catch (\Exception $e) {
            Session::set('error', 'Lỗi: ' . $e->getMessage());
        }
        $this->redirect('/admin/reports');
    }
    public function contacts()
    {
        $contacts = $this->contactModel->getAll();
        require_once __DIR__ . '/../Views/admin/contacts.php';
    }

    public function reports()
    {
        $reports = $this->reportModel->getAll();
        require_once __DIR__ . '/../Views/admin/reports.php';
    }
    public function view_user($id)
    {
        $user = $this->userModel->findById($id);
        if (!$user) {
            Session::set('error', 'Người dùng không tồn tại!');
            $this->redirect('/admin/reports');
        }
        $reports = $this->reportModel->getReportsByUserId($id);
        $products = $this->productModel->getProductsByUserId($id);
        require_once __DIR__ . '/../Views/admin/view_user.php';
    }
    public function view_product($id)
    {
        $product = $this->productModel->find2($id);
        if (!$product) {
            Session::set('error', 'Sản phẩm không tồn tại!');
            $this->redirect('/admin/products');
        }
        require_once __DIR__ . '/../Views/admin/view_product.php';
        }
    public function create_accountant()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Chỉ xử lý POST + AJAX
            header('Content-Type: application/json');

            $username = trim($_POST['username'] ?? '');
            $email    = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            if (empty($username) || empty($email) || empty($password)) {
                echo json_encode(['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin']);
                exit;
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                echo json_encode(['success' => false, 'message' => 'Email không hợp lệ']);
                exit;
            }

            if (strlen($password) < 6) {
                echo json_encode(['success' => false, 'message' => 'Mật khẩu phải từ 6 ký tự']);
                exit;
            }

            // Kiểm tra email đã tồn tại chưa
            $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                echo json_encode(['success' => false, 'message' => 'Email đã được sử dụng']);
                exit;
            }

            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $this->db->prepare("INSERT INTO users (username, email, password, role, created_at) VALUES (?, ?, ?, 'accountant', NOW())");
            $result = $stmt->execute([$username, $email, $hashed]);

            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Tạo tài khoản kế toán thành công!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống, vui lòng thử lại']);
            }
            exit;
        }
    }
    public function accountantsList() {

        require_once __DIR__ . '/../Views/admin/accountant/create.php';

    }

    public function deleteAccountant()
    {
        header('Content-Type: application/json');
        $id = $_POST['id'] ?? 0;

        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID không hợp lệ']);
            exit;
        }

        // Không cho xóa chính mình hoặc admin
        $user = Session::get('user');
        if ($id == $user['id']) {
            echo json_encode(['success' => false, 'message' => 'Không thể tự xóa chính mình']);
            exit;
        }

        $stmt = $this->db->prepare("DELETE FROM users WHERE id = ? AND role = 'accountant'");
        $result = $stmt->execute([$id]);

        echo json_encode(['success' => $result, 'message' => $result ? 'Đã xóa tài khoản' : 'Lỗi khi xóa']);
        exit;
    }

    public function resetAccountantPassword()
    {
        header('Content-Type: application/json');
        $id = $_POST['id'] ?? 0;

        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID không hợp lệ']);
            exit;
        }

        $newPass = '123456'; // hoặc random
        $hashed = password_hash($newPass, PASSWORD_DEFAULT);

        $stmt = $this->db->prepare("UPDATE users SET password = ? WHERE id = ? AND role = 'accountant'");
        $result = $stmt->execute([$hashed, $id]);

        echo json_encode([
            'success' => $result,
            'message' => $result ? "Đã reset mật khẩu thành: 123456" : 'Lỗi khi reset'
        ]);
        exit;
    }
}
