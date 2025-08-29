<?php

namespace App\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Helpers\Session;
use App\Config\Database;
use App\WebSocket\NotificationServer;
use App\Models\Notification;
use PayOS\PayOS;

class CheckoutController
{
    private $cartModel;
    private $orderModel;
    private $notificationModel;
    private $db;
    private $payOS;

    public function __construct()
    {
        $this->cartModel = new Cart();
        $this->orderModel = new Order();
        $this->notificationModel = new Notification();
        $this->db = (new Database())->getConnection();

        $config = require __DIR__ . '/../config/payos.php';
        $this->payOS = new PayOS(
            $config['client_id'],
            $config['api_key'],
            $config['checksum_key'],
            $config['partner_code'] ?? null
        );
    }

    public function index()
    {
        if (!Session::get('user')) {
            header('Location: /login');
            exit;
        }
        $cartItems = $this->cartModel->getByUser(Session::get('user')['id']);
        if (empty($cartItems)) {
            Session::set('error', 'Giỏ hàng trống!');
            header('Location: /cart');
            exit;
        }
        require_once __DIR__ . '/../Views/checkout/index.php';
    }

    public function process()
    {
        if (!Session::get('user')) {
            header('Location: /login');
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Session::set('error', 'Yêu cầu không hợp lệ!');
            header('Location: /checkout');
            exit;
        }

        $userId = Session::get('user')['id'];
        $cartItems = $this->cartModel->getByUser($userId);
        if (empty($cartItems)) {
            Session::set('error', 'Giỏ hàng trống!');
            header('Location: /cart');
            exit;
        }

        $details = [
            'fullname' => $_POST['fullname'] ?? '',
            'phone' => $_POST['phone'] ?? '',
            'pincode' => $_POST['pincode'] ?? '',
            'state' => $_POST['state'] ?? '',
            'town_city' => $_POST['town_city'] ?? '',
            'house_no' => $_POST['house_no'] ?? '',
            'road_name' => $_POST['road_name'] ?? '',
            'landmark' => $_POST['landmark'] ?? ''
        ];
        $paymentMethod = $_POST['payment_method'] ?? '';

        foreach ($details as $key => $value) {
            if (empty($value)) {
                Session::set('error', 'Vui lòng điền đầy đủ thông tin!');
                header('Location: /checkout');
                exit;
            }
        }
        if (!in_array($paymentMethod, ['cod', 'payos'])) {
            Session::set('error', 'Phương thức thanh toán không hợp lệ!');
            header('Location: /checkout');
            exit;
        }

        $orderIds = [];
        foreach ($cartItems as $item) {
            error_log("Processing product_id: " . $item['product_id']);

            $subtotal = $item['price'] * $item['quantity'];
            $vat = $subtotal * 0.1;
            $totalPrice = $subtotal + $vat;

            $stmt = $this->db->prepare("SELECT seller_id, title FROM products WHERE id = ?");
            $stmt->execute([$item['product_id']]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($stmt->errorCode() !== '00000') {
                $errorInfo = $stmt->errorInfo();
                error_log("Database query error for product_id {$item['product_id']}: " . $errorInfo[2]);
                Session::set('error', 'Lỗi truy vấn cơ sở dữ liệu: ' . $errorInfo[2]);
                header('Location: /checkout');
                exit;
            }

            if (!$result || !isset($result['seller_id']) || empty($result['seller_id'])) {
                error_log("Product {$item['product_id']} not found or has no seller_id");
                $this->cartModel->remove($userId, $item['product_id']);
                Session::set('error', 'Sản phẩm ' . $item['product_id'] . ' không tồn tại hoặc không có người bán! Đã xóa khỏi giỏ hàng.');
                header('Location: /cart');
                exit;
            }

            $sellerId = $result['seller_id'];
            $productName = $result['title'];

            $orderId = $this->orderModel->create($userId, $sellerId, $item['product_id'], $item['quantity'], $totalPrice);
            error_log("Order ID created: " . $orderId);
            $this->orderModel->addDetail($orderId, $details);
            $this->orderModel->createPayment($orderId, $paymentMethod, $totalPrice);
            $orderIds[] = $orderId;

            if ($paymentMethod === 'cod') {
                $this->orderModel->updateStatus($orderId, 'pending');
                $this->createTransaction($orderId, $totalPrice, 'cod', 'pending', null);
                $this->sendOrderNotification($userId, $orderId, 'Đặt hàng thành công', "Đơn hàng #$orderId đã được đặt thành công!", "/order/confirmation/$orderId");
                $this->sendOrderNotification($sellerId, $orderId, 'Đơn hàng mới', "Bạn có đơn hàng mới #$orderId từ người mua!", "/partners/orders/$orderId");
            } else {
                $paymentLink = $this->createPayOSPaymentLink($orderId, $totalPrice, $productName, $details);
                if ($paymentLink) {
                    $this->orderModel->updateStatus($orderId, 'pending_payment');
                    $this->createTransaction($orderId, $totalPrice, 'payos', 'pending', $paymentLink['paymentLinkId'], $paymentLink['orderCode']);
                    $this->sendOrderNotification($userId, $orderId, 'Đang chờ thanh toán', "Đơn hàng #$orderId đang chờ thanh toán!", "/order/confirmation/$orderId");
                    $this->sendOrderNotification($sellerId, $orderId, 'Đơn hàng mới', "Bạn có đơn hàng mới #$orderId (chờ thanh toán)!", "/partners/orders/$orderId");
                    header('Location: ' . $paymentLink['checkoutUrl']);
                    exit;
                } else {
                    Session::set('error', 'Không thể tạo link thanh toán PayOS!');
                    header('Location: /checkout');
                    exit;
                }
            }
        }

        $this->cartModel->clear($userId);
        if ($paymentMethod === 'cod') {
            Session::set('success', 'Đặt hàng thành công! Đơn hàng sẽ được giao sớm.');
            header('Location: /order/confirmation/' . end($orderIds));
            exit;
        }
    }

    private function sendOrderNotification($userId, $orderId, $title, $message, $link)
    {
        // Kiểm tra xem thông báo đã tồn tại chưa
        $existingNotification = $this->notificationModel->findByOrderId($orderId, $userId);
        if (!$existingNotification) {
            $this->notificationModel->create($userId, 'order', $title, $message, $link);
        }
    }

    private function generateSafeOrderCode($orderId)
    {
        $timestamp = time();
        $safeOrderCode = ($timestamp % 1000000) * 10000 + ($orderId % 10000);
        if ($safeOrderCode > 9007199254740991) {
            $safeOrderCode = $safeOrderCode % 9007199254740991;
        }
        if ($safeOrderCode <= 0) {
            $safeOrderCode = abs($safeOrderCode) + 1;
        }
        return $safeOrderCode;
    }

    private function createPayOSPaymentLink($orderId, $amount, $productName, $details)
    {
        try {
            $orderCode = (int)$orderId;
            if ($orderCode <= 0 || $orderCode > 9007199254740991) {
                $orderCode = $this->generateSafeOrderCode($orderId);
            }

            $data = [
                'orderCode' => $orderCode,
                'amount' => (int)$amount,
                'description' => "Thanh toán đơn hàng #{$orderCode}",
                'buyerName' => $details['fullname'],
                'buyerEmail' => Session::get('user')['email'] ?? '',
                'buyerPhone' => $details['phone'],
                'buyerAddress' => $details['house_no'] . ', ' . $details['road_name'] . ', ' . $details['town_city'] . ', ' . $details['state'],
                'items' => [
                    [
                        'name' => $productName,
                        'quantity' => 1,
                        'price' => (int)$amount
                    ]
                ],
                'cancelUrl' => 'http://localhost:8080/checkout/cancel',
                'returnUrl' => 'http://localhost:8080/checkout/success',
                'expiredAt' => time() + 24 * 60 * 60
            ];

            $response = $this->payOS->createPaymentLink($data);
            return [
                'paymentLinkId' => $response['paymentLinkId'],
                'checkoutUrl' => $response['checkoutUrl'],
                'orderCode' => $orderCode
            ];
        } catch (\Exception $e) {
            error_log("PayOS error: " . $e->getMessage());
            return null;
        }
    }

    public function success()
    {
        $data = $_GET;
        $orderCode = $data['orderCode'] ?? null;

        if (!$orderCode) {
            Session::set('error', 'Dữ liệu thanh toán không hợp lệ!');
            header('Location: /checkout');
            exit;
        }

        $stmt = $this->db->prepare("SELECT order_id FROM transactions WHERE order_code = ?");
        $stmt->execute([$orderCode]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        $orderId = $result['order_id'] ?? null;

        if (!$orderId) {
            Session::set('error', 'Không tìm thấy đơn hàng!');
            header('Location: /checkout');
            exit;
        }

        $order = $this->orderModel->getOrderById($orderId);
        if (!$order) {
            Session::set('error', 'Đơn hàng không tồn tại!');
            header('Location: /checkout');
            exit;
        }

        try {
            $paymentInfo = $this->payOS->getPaymentLinkInformation($orderCode);
            if ($paymentInfo['status'] === 'PAID') {
                $this->orderModel->updatePaymentStatus($orderId, 'completed', $paymentInfo['paymentLinkId']);
                $this->orderModel->updateStatus($orderId, 'confirmed');
                $this->createTransaction($orderId, $paymentInfo['amount'], 'payos', 'completed', $paymentInfo['paymentLinkId'], $orderCode);
                $this->sendOrderNotification($order['buyer_id'], $orderId, 'Thanh toán thành công', "Đơn hàng #$orderId đã được thanh toán thành công!", "/order/confirmation/$orderId");
                $this->sendOrderNotification($order['seller_id'], $orderId, 'Đơn hàng đã thanh toán', "Đơn hàng #$orderId đã được thanh toán!", "/partners/orders/$orderId");
                Session::set('success', 'Thanh toán thành công!');
            } else {
                $this->orderModel->updatePaymentStatus($orderId, 'failed', $paymentInfo['paymentLinkId']);
                $this->orderModel->updateStatus($orderId, 'cancelled');
                $this->sendOrderNotification($order['buyer_id'], $orderId, 'Thanh toán thất bại', "Đơn hàng #$orderId đã bị hủy do thanh toán thất bại!", "/order/confirmation/$orderId");
                Session::set('error', 'Thanh toán thất bại hoặc bị hủy!');
            }
        } catch (\Exception $e) {
            error_log("PayOS verification error: " . $e->getMessage());
            Session::set('error', 'Lỗi xác minh thanh toán!');
        }

        header('Location: /order/confirmation/' . $orderId);
        exit;
    }

    public function cancel()
    {
        $orderCode = $_GET['orderCode'] ?? null;

        if (!$orderCode) {
            Session::set('error', 'Dữ liệu thanh toán không hợp lệ!');
            header('Location: /checkout');
            exit;
        }

        $stmt = $this->db->prepare("SELECT order_id FROM transactions WHERE order_code = ?");
        $stmt->execute([$orderCode]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        $orderId = $result['order_id'] ?? null;

        if ($orderId) {
            $this->orderModel->updatePaymentStatus($orderId, 'cancelled', null);
            $this->orderModel->updateStatus($orderId, 'cancelled');
            $this->createTransaction($orderId, 0, 'payos', 'cancelled', null, $orderCode);
            $this->sendOrderNotification($orderId['buyer_id'], $orderId, 'Thanh toán bị hủy', "Đơn hàng #$orderId đã bị hủy!", "/order/confirmation/$orderId");
        }

        Session::set('error', 'Thanh toán đã bị hủy!');
        header('Location: /checkout');
        exit;
    }

    public function confirmation($orderId)
    {
        if (!Session::get('user')) {
            header('Location: /login');
            exit;
        }

        $order = $this->orderModel->getOrderById($orderId);
        if ($order['payment_method'] === 'payos' && $order['status'] === 'pending_payment') {
            $stmt = $this->db->prepare("SELECT order_code FROM transactions WHERE order_id = ?");
            $stmt->execute([$orderId]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            $orderCode = $result['order_code'] ?? null;

            if ($orderCode) {
                try {
                    $paymentInfo = $this->payOS->getPaymentLinkInformation($orderCode);
                    if ($paymentInfo['status'] === 'PAID') {
                        $this->orderModel->updatePaymentStatus($orderId, 'completed', $paymentInfo['paymentLinkId']);
                        $this->orderModel->updateStatus($orderId, 'confirmed');
                        $this->createTransaction($orderId, $paymentInfo['amount'], 'payos', 'completed', $paymentInfo['paymentLinkId'], $orderCode);
                        $this->sendOrderNotification($order['buyer_id'], $orderId, 'Thanh toán thành công', "Đơn hàng #$orderId đã được thanh toán thành công!", "/order/confirmation/$orderId");
                        $this->sendOrderNotification($order['seller_id'], $orderId, 'Đơn hàng đã thanh toán', "Đơn hàng #$orderId đã được thanh toán!", "/partners/orders/$orderId");
                        Session::set('success', 'Thanh toán đã được xác nhận!');
                    } elseif ($paymentInfo['status'] === 'CANCELLED' || $paymentInfo['status'] === 'DECLINED') {
                        $this->orderModel->updatePaymentStatus($orderId, 'cancelled', $paymentInfo['paymentLinkId']);
                        $this->orderModel->updateStatus($orderId, 'cancelled');
                        $this->sendOrderNotification($order['buyer_id'], $orderId, 'Thanh toán bị hủy', "Đơn hàng #$orderId đã bị hủy!", "/order/confirmation/$orderId");
                        Session::set('error', 'Thanh toán đã bị hủy hoặc thất bại!');
                    }
                } catch (\Exception $e) {
                    error_log("PayOS verification error: " . $e->getMessage());
                    Session::set('error', 'Lỗi xác minh thanh toán!');
                }
            }
        }

        require_once __DIR__ . '/../Views/checkout/confirmation.php';
    }

    public function payOrder($orderId)
    {
        if (!Session::get('user')) {
            Session::set('error', 'Vui lòng đăng nhập để thực hiện thanh toán!');
            header('Location: /login');
            exit;
        }

        $order = $this->orderModel->getOrderById($orderId);
        if (!$order) {
            Session::set('error', 'Đơn hàng không tồn tại!');
            header('Location: /profile/my-orders');
            exit;
        }

        if ($order['buyer_id'] !== Session::get('user')['id']) {
            Session::set('error', 'Bạn không có quyền thanh toán đơn hàng này!');
            header('Location: /profile/my-orders');
            exit;
        }

        $stmt = $this->db->prepare("SELECT status, payment_method FROM payment WHERE order_id = ?");
        $stmt->execute([$orderId]);
        $payment = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$payment || ($payment['status'] !== 'failed' && $order['status'] !== 'cancelled')) {
            Session::set('error', 'Đơn hàng không thể thanh toán lại!');
            header('Location: /profile/my-orders');
            exit;
        }

        $stmt = $this->db->prepare("SELECT * FROM order_detail WHERE order_id = ?");
        $stmt->execute([$orderId]);
        $detail = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$detail) {
            Session::set('error', 'Không tìm thấy thông tin chi tiết đơn hàng!');
            header('Location: /profile/my-orders');
            exit;
        }

        $stmt = $this->db->prepare("SELECT title FROM products WHERE id = ?");
        $stmt->execute([$order['product_id']]);
        $product = $stmt->fetch(\PDO::FETCH_ASSOC);
        $productName = $product['title'] ?? 'Sản phẩm';

        if ($payment['payment_method'] === 'payos') {
            $paymentLink = $this->createPayOSPaymentLink($orderId, $order['total_amount'], $productName, $detail);
            if ($paymentLink) {
                $this->orderModel->updatePaymentStatus($orderId, 'pending', $paymentLink['paymentLinkId']);
                $this->orderModel->updateStatus($orderId, 'pending_payment');
                $this->createTransaction($orderId, $order['total_amount'], 'payos', 'pending', $paymentLink['paymentLinkId'], $paymentLink['orderCode']);
                $this->sendOrderNotification($order['buyer_id'], $orderId, 'Đang chờ thanh toán', "Đơn hàng #$orderId đang chờ thanh toán!", "/order/confirmation/$orderId");
                $this->sendOrderNotification($order['seller_id'], $orderId, 'Đơn hàng đang chờ thanh toán', "Đơn hàng #$orderId đang chờ thanh toán!", "/partners/orders/$orderId");
                header('Location: ' . $paymentLink['checkoutUrl']);
                exit;
            } else {
                Session::set('error', 'Không thể tạo link thanh toán PayOS!');
                header('Location: /profile/my-orders');
                exit;
            }
        } else {
            $this->orderModel->updatePaymentStatus($orderId, 'pending', null);
            $this->orderModel->updateStatus($orderId, 'pending');
            $this->createTransaction($orderId, $order['total_amount'], 'cod', 'pending', null);
            $this->sendOrderNotification($order['buyer_id'], $orderId, 'Đặt hàng thành công', "Đơn hàng #$orderId đã được đặt lại thành công!", "/order/confirmation/$orderId");
            $this->sendOrderNotification($order['seller_id'], $orderId, 'Đơn hàng mới', "Đơn hàng #$orderId đã được đặt lại!", "/partners/orders/$orderId");
            Session::set('success', 'Đơn hàng đã được đặt lại thành công!');
            header('Location: /order/confirmation/' . $orderId);
            exit;
        }
    }

    private function createTransaction($orderId, $amount, $paymentMethod, $status, $transactionId = null, $orderCode = null)
    {
        $stmt = $this->db->prepare("
            INSERT INTO transactions (order_id, amount, payment_method, status, transaction_id, order_code, created_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");
        return $stmt->execute([$orderId, $amount, $paymentMethod, $status, $transactionId, $orderCode]);
    }
}
