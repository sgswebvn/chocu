<?php

namespace App\Controllers;

use App\Models\Favorite;
use App\Helpers\Session;
use App\WebSocket\NotificationServer;

class FavoriteController
{
    private $favoriteModel;

    public function __construct()
    {
        $this->favoriteModel = new Favorite();
    }

    public function add()
    {
        header('Content-Type: application/json');

        if (!Session::get('user')) {
            echo json_encode([
                'success' => false,
                'message' => 'Vui lòng đăng nhập để thêm sản phẩm vào danh sách yêu thích!'
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

        if (!$productId) {
            echo json_encode([
                'success' => false,
                'message' => 'Thông tin sản phẩm không hợp lệ!'
            ]);
            return;
        }

        $userId = Session::get('user')['id'];

        if ($this->favoriteModel->add($userId, $productId)) {
            NotificationServer::sendNotification(
                $userId,
                'favorite',
                [
                    'title' => 'Thêm vào yêu thích',
                    'message' => "Sản phẩm #$productId đã được thêm vào danh sách yêu thích!",
                    'link' => '/favorites'
                ]
            );
            echo json_encode([
                'success' => true,
                'message' => 'Sản phẩm đã được thêm vào danh sách yêu thích!'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Sản phẩm đã có trong danh sách yêu thích!'
            ]);
        }
    }


    public function index()
    {
        // Logic hiển thị danh sách yêu thích
        $userId = Session::get('user')['id'];
        $favorites = $this->favoriteModel->getByUser($userId);
        require_once __DIR__ . '/../Views/favorite/index.php';
    }

    public function remove($id)
    {
        // Logic xóa sản phẩm khỏi danh sách yêu thích
        $userId = Session::get('user')['id'];
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        if ($this->favoriteModel->remove($userId, $id)) {
            $response = ['success' => true, 'message' => 'Sản phẩm đã được xóa khỏi danh sách yêu thích!'];
            Session::set('success', $response['message']);
        } else {
            $response = ['success' => false, 'message' => 'Không thể xóa sản phẩm khỏi danh sách yêu thích!'];
            Session::set('error', $response['message']);
        }

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode($response);
            exit;
        }

        header('Location: /favorites');
        exit;
    }
}
