<?php

namespace App\Controllers;

use App\Models\Admin;
use App\Helpers\Session;
use App\Models\Category;
use App\Models\Contact;
use App\Models\Product;
use App\Models\Report;
use App\Models\User;

class AdminController
{
    private $adminModel;
    private $categoryModel;
    private $productModel;
    private $contactModel;
    private $reportModel;
    private $userModel;

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
        $products = $this->adminModel->searchProducts($keyword);
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
        $is_active = $action === 'activate' ? 1 : 0;
        try {
            if ($this->adminModel->toggleUserStatus($id, $is_active)) {
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
}
