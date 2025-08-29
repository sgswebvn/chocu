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
        // Logic hiển thị giỏ hàng
        $userId = Session::get('user')['id'];
        $cartItems = $this->cartModel->getByUser($userId);
        require_once __DIR__ . '/../Views/cart/index.php';
    }

    public function remove($id)
    {
        // Logic xóa sản phẩm khỏi giỏ hàng
        $userId = Session::get('user')['id'];
        if ($this->cartModel->remove($userId, $id)) {
            Session::set('success', 'Sản phẩm đã được xóa khỏi giỏ hàng!');
        } else {
            Session::set('error', 'Không thể xóa sản phẩm khỏi giỏ hàng!');
        }
        header('Location: /cart');
        exit;
    }
}
