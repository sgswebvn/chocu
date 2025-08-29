<?php

namespace App\Controllers;

use App\Models\Order;
use App\Helpers\Session;
use App\WebSocket\NotificationServer;

class OrderController
{
    private $orderModel;

    public function __construct()
    {
        $this->orderModel = new Order();
    }

    public function track($id)
    {
        if (!Session::get('user')) {
            header('Location: /login');
            exit;
        }

        $order = $this->orderModel->find($id);

        if (!$order || $order['buyer_id'] != Session::get('user')['id']) {
            Session::set('error', 'Đơn hàng không tồn tại hoặc không thuộc về bạn!');
            header('Location: /orders');
            exit;
        }
        require_once __DIR__ . '/../Views/order/track.php';
    }

    public function updateOrder($id)
    {
        if (!Session::get('user')) {
            header('Location: /login');
            exit;
        }
        $order = $this->orderModel->find($id);
        if (!$order || $order['seller_id'] != Session::get('user')['id']) {
            Session::set('error', 'Đơn hàng không tồn tại hoặc không thuộc về bạn!');
            header('Location: /profile/orders');
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $status = $_POST['status'];
            $trackingNumber = $_POST['tracking_number'] ?? null;
            $carrier = $_POST['carrier'] ?? null;
            if ($this->orderModel->updateStatus($id, $status, $trackingNumber, $carrier)) {
                Session::set('success', 'Cập nhật đơn hàng thành công!');
                NotificationServer::sendNotification(
                    $order['buyer_id'],
                    'order',
                    [
                        'order_id' => $id,
                        'status' => $status,
                        'timestamp' => date('Y-m-d H:i:s'),
                        'link' => "/orders/{$id}"
                    ]
                );
            } else {
                Session::set('error', 'Cập nhật thất bại!');
            }
            header('Location: /profile/orders');
            exit;
        }
        require_once __DIR__ . '/../Views/order/update.php';
    }

    public function cancel($id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_SERVER['HTTP_REFERER']) || !str_contains($_SERVER['HTTP_REFERER'], $_SERVER['HTTP_HOST'])) {
                $response = ['success' => false, 'message' => 'Nguồn yêu cầu không hợp lệ!'];
                error_log("OrderController: Invalid request source for order ID: $id, user ID: " . (Session::get('user')['id'] ?? 'unknown'));
                header('Content-Type: application/json');
                echo json_encode($response);
                exit;
            }

            $order = $this->orderModel->getOrderById($id);
            if (!$order || $order['buyer_id'] !== Session::get('user')['id']) {
                $response = ['success' => false, 'message' => 'Đơn hàng không tồn tại hoặc bạn không có quyền hủy!'];
                error_log("OrderController: Cancel failed - Invalid order or user for order ID: $id, user ID: " . (Session::get('user')['id'] ?? 'unknown'));
            } elseif (!in_array($order['status'], ['pending', 'processing'])) {
                $response = ['success' => false, 'message' => 'Chỉ có thể hủy đơn hàng ở trạng thái Chờ xử lý hoặc Đang xử lý!'];
                error_log("OrderController: Cancel failed - Invalid status for order ID: $id, status: {$order['status']}");
            } elseif ($this->orderModel->updateStatus($id, 'cancelled')) {
                $response = ['success' => true, 'message' => 'Hủy đơn hàng thành công!'];
                NotificationServer::sendNotification(
                    $order['seller_id'],
                    'order',
                    [
                        'order_id' => $id,
                        'status' => 'cancelled',
                        'timestamp' => date('Y-m-d H:i:s'),
                        'link' => "/profile/orders/{$id}"
                    ]
                );
                error_log("OrderController: Order cancelled successfully for order ID: $id, user ID: " . Session::get('user')['id']);
            } else {
                $response = ['success' => false, 'message' => 'Hủy đơn hàng thất bại!'];
                error_log("OrderController: Cancel failed for order ID: $id, user ID: " . Session::get('user')['id']);
            }
            header('Content-Type: application/json');
            echo json_encode($response);
            exit;
        }
        Session::set('error', 'Yêu cầu không hợp lệ!');
        error_log("OrderController: Invalid request method for cancel order ID: $id");
        header('Location: /profile/my-orders');
        exit;
    }
}
