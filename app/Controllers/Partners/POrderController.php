<?php

namespace App\Controllers\Partners;

use App\Models\Order;
use App\Models\OrderDetail;
use App\Helpers\Session;
use App\WebSocket\NotificationServer;

class POrderController
{
    private $orderModel;
    private $orderDetailModel;

    public function __construct()
    {
        $this->orderModel = new Order();
        $this->orderDetailModel = new OrderDetail();
    }

    public function index()
    {
        if (!Session::get('user') || Session::get('user')['is_partner_paid'] != 1) {
            Session::set('error', 'Bạn cần là đối tác đã thanh toán để xem đơn hàng!');
            header('Location: /login');
            exit;
        }

        $userId = Session::get('user')['id'];
        $orders = $this->orderModel->getOrdersBySellerId($userId);
        require_once __DIR__ . '/../../Views/a-partner/orders/index.php';
    }

    public function show($orderId)
    {
        if (!Session::get('user') || Session::get('user')['is_partner_paid'] != 1) {
            Session::set('error', 'Bạn cần là đối tác đã thanh toán để xem chi tiết đơn hàng!');
            header('Location: /login');
            exit;
        }

        $userId = Session::get('user')['id'];
        $order = $this->orderModel->getOrderById($orderId);

        if (!$order || $order['seller_id'] != $userId || !$this->orderModel->canAccessOrder($orderId, $userId)) {
            Session::set('error', 'Bạn không có quyền xem đơn hàng này!');
            header('Location: /partners/orders');
            exit;
        }

        $orderDetails = $this->orderDetailModel->getByOrderId($orderId);
        require_once __DIR__ . '/../../Views/a-partner/orders/show.php';
    }

    public function update($orderId)
    {
        if (!Session::get('user') || Session::get('user')['is_partner_paid'] != 1) {
            Session::set('error', 'Bạn cần là đối tác đã thanh toán để cập nhật đơn hàng!');
            header('Location: /login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Session::set('error', 'Yêu cầu không hợp lệ!');
            header('Location: /partners/orders/' . $orderId);
            exit;
        }

        $userId = Session::get('user')['id'];
        $order = $this->orderModel->getOrderById($orderId);

        if (!$order || $order['seller_id'] != $userId || !$this->orderModel->canAccessOrder($orderId, $userId)) {
            Session::set('error', 'Bạn không có quyền cập nhật đơn hàng này!');
            header('Location: /partners/orders');
            exit;
        }

        $status = $_POST['status'] ?? '';
        $trackingNumber = $_POST['tracking_number'] ?? null;
        $carrier = $_POST['carrier'] ?? null;

        if (!in_array($status, ['pending', 'processing', 'shipped', 'delivered', 'cancelled'])) {
            Session::set('error', 'Trạng thái không hợp lệ!');
            header('Location: /partners/orders/' . $orderId);
            exit;
        }

        if ($status === 'shipped' && (empty($trackingNumber) || empty($carrier))) {
            Session::set('error', 'Vui lòng cung cấp mã vận đơn và đơn vị vận chuyển!');
            header('Location: /partners/orders/' . $orderId);
            exit;
        }

        if ($this->orderModel->updateStatus($orderId, $status, $trackingNumber, $carrier)) {
            $statusMessages = [
                'pending' => 'Đơn hàng đang chờ xử lý',
                'processing' => 'Đơn hàng đang được xử lý',
                'shipped' => 'Đơn hàng đã được giao cho vận chuyển',
                'delivered' => 'Đơn hàng đã được giao thành công',
                'cancelled' => 'Đơn hàng đã bị hủy'
            ];

            $this->sendOrderNotification(
                $order['buyer_id'],
                $orderId,
                'Cập nhật đơn hàng',
                "Đơn hàng #$orderId đã được cập nhật trạng thái: {$statusMessages[$status]}.",
                "/order/confirmation/$orderId"
            );

            Session::set('success', 'Cập nhật trạng thái đơn hàng thành công!');
        } else {
            Session::set('error', 'Không thể cập nhật trạng thái đơn hàng!');
        }

        header('Location: /partners/orders/' . $orderId);
        exit;
    }

    private function sendOrderNotification($userId, $orderId, $title, $message, $link)
    {
        $notificationModel = new \App\Models\Notification();
        $existingNotification = $notificationModel->findByOrderId($orderId, $userId);
        if (!$existingNotification) {
            $notificationModel->create($userId, 'order', $title, $message, $link, $orderId);
            // NotificationServer::sendNotification(
            //     $userId,
            //     'order',
            //     [
            //         'order_id' => $orderId,
            //         'title' => $title,
            //         'message' => $message,
            //         'status' => 'pending',
            //         'timestamp' => date('Y-m-d H:i:s'),
            //         'link' => $link
            //     ]
            // );
        }
    }
}
