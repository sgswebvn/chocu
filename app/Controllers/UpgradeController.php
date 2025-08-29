<?php

namespace App\Controllers;

use App\Helpers\Session;
use App\Models\User;
use App\WebSocket\NotificationServer;
use PayOS\PayOS;
use App\Config\Database;

class UpgradeController
{
    private $userModel;
    private $payOS;
    private $db;

    public function __construct()
    {
        $this->userModel = new User();
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
            Session::set('error', 'Vui lòng đăng nhập để mua gói nâng cấp!');
            header('Location: /login');
            exit;
        }

        $user = Session::get('user');
        if ($user['role'] !== 'partners') {
            Session::set('error', 'Chỉ đối tác mới có thể mua gói nâng cấp!');
            header('Location: /profile');
            exit;
        }

        // Check is_partner_paid with fallback to 0
        $isPartnerPaid = isset($user['is_partner_paid']) ? $user['is_partner_paid'] : 0;
        if ($isPartnerPaid) {
            Session::set('error', 'Bạn đã mua gói nâng cấp!');
            header('Location: /partners');
            exit;
        }

        require_once __DIR__ . '/../Views/upgrade/index.php';
    }

    public function process()
    {
        // Add CORS headers
        header('Access-Control-Allow-Origin: *'); // Replace '*' with specific origin in production
        header('Access-Control-Allow-Methods: POST');
        header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

        if (!Session::get('user')) {
            $response = ['success' => false, 'message' => 'Vui lòng đăng nhập để mua gói nâng cấp!'];
        } elseif ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $response = ['success' => false, 'message' => 'Yêu cầu không hợp lệ!'];
        } else {
            $user = Session::get('user');
            if ($user['role'] !== 'partners') {
                $response = ['success' => false, 'message' => 'Chỉ đối tác mới có thể mua gói nâng cấp!'];
            } else {
                // Check is_partner_paid with fallback to 0
                $isPartnerPaid = isset($user['is_partner_paid']) ? $user['is_partner_paid'] : 0;
                if ($isPartnerPaid) {
                    $response = ['success' => false, 'message' => 'Bạn đã mua gói nâng cấp!'];
                } else {
                    $paymentLink = $this->createPayOSPaymentLink($user['id'], 2000, 'Gói nâng cấp đối tác');
                    if ($paymentLink) {
                        $this->createTransaction($user['id'], 2000, 'payos', 'pending', $paymentLink['paymentLinkId'], $paymentLink['orderCode']);
                        $response = [
                            'success' => true,
                            'message' => 'Tạo link thanh toán thành công! chờ chuyển hướng...',
                            'redirect' => $paymentLink['checkoutUrl']
                        ];
                    } else {
                        $response = ['success' => false, 'message' => 'Không thể tạo link thanh toán PayOS!'];
                    }
                }
            }
        }

        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }

    private function createPayOSPaymentLink($userId, $amount, $description)
    {
        try {
            $orderCode = $this->generateSafeOrderCode($userId);
            $data = [
                'orderCode' => $orderCode,
                'amount' => (int)$amount,
                'description' => $description,
                'buyerName' => Session::get('user')['username'] ?? '',
                'buyerEmail' => Session::get('user')['email'] ?? '',
                'buyerPhone' => '',
                'buyerAddress' => '',
                'items' => [
                    [
                        'name' => $description,
                        'quantity' => 1,
                        'price' => (int)$amount
                    ]
                ],
                'cancelUrl' => 'http://localhost:8080/upgrade/cancel',
                'returnUrl' => 'http://localhost:8080/upgrade/success',
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

    private function generateSafeOrderCode($userId)
    {
        $timestamp = time();
        $safeOrderCode = ($timestamp % 1000000) * 10000 + ($userId % 10000);
        if ($safeOrderCode > 9007199254740991) {
            $safeOrderCode = $safeOrderCode % 9007199254740991;
        }
        if ($safeOrderCode <= 0) {
            $safeOrderCode = abs($safeOrderCode) + 1;
        }
        return $safeOrderCode;
    }

    public function success()
    {
        $data = $_GET;
        $orderCode = $data['orderCode'] ?? null;

        if (!$orderCode) {
            Session::set('error', 'Dữ liệu thanh toán không hợp lệ!');
            header('Location: /upgrade');
            exit;
        }

        $stmt = $this->db->prepare("SELECT user_id FROM transactions WHERE order_code = ?");
        $stmt->execute([$orderCode]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        $userId = $result['user_id'] ?? null;

        if (!$userId) {
            Session::set('error', 'Không tìm thấy giao dịch!');
            header('Location: /upgrade');
            exit;
        }

        try {
            $paymentInfo = $this->payOS->getPaymentLinkInformation($orderCode);
            if ($paymentInfo['status'] === 'PAID') {
                $this->userModel->updatePartnerStatus($userId, 1);
                $this->createTransaction($userId, $paymentInfo['amount'], 'payos', 'completed', $paymentInfo['paymentLinkId'], $orderCode);
                $user = $this->userModel->findById($userId);
                if (!$user) {
                    Session::set('error', 'Không thể tải thông tin người dùng!');
                    header('Location: /upgrade');
                    exit;
                }
                Session::set('user', $user);
                NotificationServer::sendNotification(
                    $userId,
                    'auth',
                    [
                        'title' => 'Nâng cấp thành công',
                        'message' => 'Bạn đã trở thành đối tác chính thức!',
                        'link' => '/partners'
                    ]
                );
                Session::set('success', 'Nâng cấp tài khoản đối tác thành công!');
                header('Location: /partners');
                exit;
            } else {
                $this->createTransaction($userId, $paymentInfo['amount'], 'payos', 'failed', $paymentInfo['paymentLinkId'], $orderCode);
                Session::set('error', 'Thanh toán thất bại!');
                header('Location: /upgrade');
                exit;
            }
        } catch (\Exception $e) {
            error_log("PayOS verification error: " . $e->getMessage());
            Session::set('error', 'Lỗi xác minh thanh toán!');
            header('Location: /upgrade');
            exit;
        }
    }

    public function cancel()
    {
        $orderCode = $_GET['orderCode'] ?? null;

        if ($orderCode) {
            $stmt = $this->db->prepare("SELECT user_id FROM transactions WHERE order_code = ?");
            $stmt->execute([$orderCode]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            $userId = $result['user_id'] ?? null;

            if ($userId) {
                $this->createTransaction($userId, 0, 'payos', 'cancelled', null, $orderCode);
            }
        }

        Session::set('error', 'Thanh toán đã bị hủy!');
        header('Location: /upgrade');
        exit;
    }

    private function createTransaction($userId, $amount, $paymentMethod, $status, $transactionId = null, $orderCode = null)
    {
        $stmt = $this->db->prepare("
            INSERT INTO transactions (user_id, amount, payment_method, status, transaction_id, order_code, order_id, created_at)
            VALUES (?, ?, ?, ?, ?, ?, NULL, NOW())
        ");
        return $stmt->execute([$userId, $amount, $paymentMethod, $status, $transactionId, $orderCode]);
    }
}
