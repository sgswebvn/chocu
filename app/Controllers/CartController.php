<?php

namespace App\Controllers;

use App\Models\Cart;
use App\Helpers\Session;
use App\Models\Product;
use App\WebSocket\NotificationServer;

class CartController
{
    private $cartModel;

    public function __construct()
    {
        $this->cartModel = new Cart();
    }

    public function add()
    {
        header('Content-Type: application/json');

        if (!Session::get('user')) {
            echo json_encode([
                'success' => false,
                'message' => 'Vui lòng đăng nhập để thêm sản phẩm vào giỏ hàng!'
            ]);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode([
                'success' => false,
                'message' => 'Yêu cầu không hợp lệ!'
            ]);
            return;
        }

        $productId = $_POST['product_id'] ?? null;
        $quantity = $_POST['quantity'] ?? 1;

        if (!$productId || $quantity < 1) {
            echo json_encode([
                'success' => false,
                'message' => 'Thông tin sản phẩm không hợp lệ!'
            ]);
            return;
        }

        $productModel = new \App\Models\Product();
        $product = $productModel->find($productId);
        $title = $product['title'] ?? 'Sản phẩm';

        $userId = Session::get('user')['id'];

        if ($this->cartModel->add($userId, $productId, $quantity)) {
            NotificationServer::sendNotification(
                $userId,
                'cart',
                [
                    'title' => 'Thêm vào giỏ hàng',
                    'message' => "$title đã được thêm vào giỏ hàng!",
                    'link' => '/cart'
                ]
            );
            echo json_encode([
                'success' => true,
                'message' => 'Sản phẩm đã được thêm vào giỏ hàng!'
            ]);
        }
    }



    public function index()
    {
      if (!Session::get('user')) {
        Session::set('error', 'Vui lòng đăng nhập để thanh toán!');
        header('Location: /login');
        exit;
    }

    $userId = Session::get('user')['id'];
    $selectedIds = $_GET['selected_items'] ?? [];

    $cartModel = new \App\Models\Cart();

    if (empty($selectedIds)) {
        // Nếu không chọn gì → lấy tất cả
        $cartItems = $cartModel->getByUser($userId);
    } else {
        // Chỉ lấy những sản phẩm được chọn
        $cartItems = $cartModel->getSelectedItems($userId, $selectedIds);
    }
        require_once __DIR__ . '/../Views/cart/index.php';
    }

    public function remove($id)
{
    header('Content-Type: application/json'); // BẮT BUỘC PHẢI CÓ DÒNG NÀY!

    if (!Session::get('user')) {
        echo json_encode([
            'success' => false,
            'message' => 'Bạn cần đăng nhập!'
        ]);
        exit;
    }

    $userId = Session::get('user')['id'];

    if ($this->cartModel->remove($userId, $id)) {
        echo json_encode([
            'success' => true,
            'message' => 'Đã xóa sản phẩm khỏi giỏ hàng!'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Không thể xóa sản phẩm!'
        ]);
    }
    exit;
}
    public function getSelectedItems($userId, array $productIds)
{
    if (empty($productIds)) {
        return $this->getByUser($userId);
    }

    $placeholders = str_repeat('?,', count($productIds) - 1) . '?';
    $sql = "SELECT c.*, p.title, p.image, p.price 
            FROM cart c 
            JOIN products p ON c.product_id = p.id 
            WHERE c.user_id = ? AND c.product_id IN ($placeholders)";

    $stmt = $this->db->prepare($sql);
    $params = array_merge([$userId], $productIds);
    $stmt->execute($params);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
}
