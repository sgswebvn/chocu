<?php

namespace App\Controllers;

use App\Models\User;
use App\Helpers\Session;
use App\Models\Order;
use App\Models\Product;
use App\WebSocket\NotificationServer;
use Google_Client;
use App\Helpers\EmailService;

class AuthController
{
    private $userModel;
    private $productModel;
    private $orderModel;
    private $emailService;

    public function __construct()
    {
        $this->userModel = new User();
        $this->productModel = new Product();
        $this->orderModel = new Order();
    }

    public function register()
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';

        // Bước 1: Gửi OTP
        if ($action === 'send_otp') {
            $this->handleSendOtp();
        }

        // Bước 2: Xác minh OTP + Đăng ký
        if ($action === 'verify_otp') {
            $this->handleVerifyOtp();
        }
    }

    // Hiển thị form đăng ký
    require_once __DIR__ . '/../Views/auth/register.php';
}
    private function handleSendOtp()
{
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($email) || empty($password)) {
        return $this->jsonResponse(false, 'Vui lòng điền đầy đủ thông tin!');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return $this->jsonResponse(false, 'Email không hợp lệ!');
    }

    if ($this->userModel->findByEmail($email)) {
        return $this->jsonResponse(false, 'Email này đã được đăng ký!');
    }

    if ($this->userModel->findByUsername($username)) {
        return $this->jsonResponse(false, 'Tên người dùng đã tồn tại!');
    }

    // DÙNG HÀM CÓ SẴN → KHÔNG CẦN $db, KHÔNG LỖI!
    if (!$this->userModel->registerUser($username, $email, $password, 0, 'user')) {
        return $this->jsonResponse(false, 'Đăng ký thất bại! Vui lòng thử lại.');
    }

    // Lấy user vừa tạo để lấy ID (nếu cần)
    $user = $this->userModel->findByEmail($email);

    // Tạo OTP + lưu vào user
    $otp = sprintf("%06d", mt_rand(0, 999999));
    if (!$this->userModel->setVerificationCode($email, $otp)) {
        return $this->jsonResponse(false, 'Lỗi hệ thống khi lưu mã xác minh!');
    }

    // Gửi email
    $emailService = new EmailService();
    if ($emailService->sendVerificationCode($email, $username, $otp)) {
        Session::set('pending_verification_email', $email);
        $this->jsonResponse(true, 'Đã gửi mã OTP đến email của bạn!', ['step' => 'verify']);
    } else {
        $this->jsonResponse(false, 'Không thể gửi email. Vui lòng thử lại!');
    }
}
    private function handleVerifyOtp()
    {
        $otp = trim($_POST['otp'] ?? '');
        $email = Session::get('pending_verification_email');

        if (empty($otp) || empty($email)) {
            return $this->jsonResponse(false, 'Dữ liệu không hợp lệ!');
        }

        $user = $this->userModel->verifyCode($email, $otp);

        if ($user) {
            Session::unset('pending_verification_email');
            $this->jsonResponse(true, 'Xác minh thành công! Bạn có thể đăng nhập ngay.', [
                'redirect' => '/login'
            ]);
        } else {
            $this->jsonResponse(false, 'Mã OTP không đúng hoặc đã hết hạn!');
        }
    }
private function jsonResponse($success, $message, $extra = [])
{
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array_merge([
        'success' => $success,
        'message' => $message
    ], $extra));
    exit;
}
    public function partnerRegister()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $is_active = 0;
            $role = 'partners';

            if (empty($username) || empty($email) || empty($password)) {
                $response = ['success' => false, 'message' => 'Vui lòng điền đầy đủ các trường!'];
            } elseif ($this->userModel->registerUser($username, $email, $password, $is_active, $role)) {
                $user = $this->userModel->findByEmail($email);
                NotificationServer::sendNotification(
                    $user['id'],
                    'auth',
                    [
                        'title' => 'Chào mừng đối tác',
                        'message' => "Chào mừng bạn đến với Chợ C2C, $username! Vui lòng mua gói nâng cấp để trở thành đối tác chính thức.",
                        'link' => '/upgrade'
                    ]
                );
                $response = ['success' => true, 'message' => 'Đăng ký đối tác thành công! Vui lòng mua gói nâng cấp.', 'redirect' => '/upgrade'];
            } else {
                $response = ['success' => false, 'message' => 'Đăng ký thất bại! Email hoặc tên người dùng đã tồn tại.'];
            }

            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode($response);
                exit;
            }

            Session::set($response['success'] ? 'success' : 'error', $response['message']);
            if ($response['success']) {
                header('Location: /upgrade');
                exit;
            }
        }

        require_once __DIR__ . '/../Views/auth/partner_register.php';
    }

  public function login()
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $response = ['success' => false, 'message' => 'Vui lòng nhập đầy đủ thông tin!'];
        } else {
            $result = $this->userModel->login($email, $password);

            if ($result === 'not_verified') {
                $response = [
                    'success' => false,
                    'message' => 'Tài khoản chưa được xác minh! Vui lòng kiểm tra email để nhận mã OTP.'
                ];
            } elseif ($result === 'locked') {
                $response = [
                    'success' => false,
                    'message' => 'Tài khoản đã bị khóa. Vui lòng liên hệ quản trị viên.'
                ];
            } elseif ($result) {
                // ĐĂNG NHẬP THÀNH CÔNG - chỉ khi đã xác minh
                Session::set('user', $result);
                $redirect = ($result['role'] === 'partners' && !$result['is_partner_paid']) ? '/upgrade' : '/';
                $response = [
                    'success' => true,
                    'message' => 'Đăng nhập thành công!',
                    'redirect' => $redirect
                ];
            } else {
                $response = ['success' => false, 'message' => 'Email hoặc mật khẩu không đúng!'];
            }
        }

        // AJAX response
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            header('Content-Type: application/json');
            echo json_encode($response);
            exit;
        }

        Session::set($response['success'] ? 'success' : 'error', $response['message']);
        if ($response['success'] ?? false) {
            header('Location: ' . $response['redirect']);
            exit;
        }
    }

    require_once __DIR__ . '/../Views/auth/login.php';
}

    public function googleLogin()
    {
        $client = new Google_Client();
        $client->setClientId($_ENV['GOOGLE_CLIENT_ID']);
        $client->setClientSecret($_ENV['GOOGLE_CLIENT_SECRET']);
        $client->setRedirectUri('http://localhost:8080/google-callback');
        $client->addScope('email');
        $client->addScope('profile');

        if (isset($_GET['code'])) {
            $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
            if (isset($token['error'])) {
                Session::set('error', 'Lỗi xác thực Google: ' . $token['error']);
                header('Location: /login');
                exit;
            }

            $client->setAccessToken($token);
            $google_oauth = new \Google_Service_Oauth2($client);
            $userInfo = $google_oauth->userinfo->get();

            $email = $userInfo->email;
            $username = $userInfo->name;
            $googleId = $userInfo->id;

            $user = $this->userModel->loginWithGoogle($email, $googleId, $username);

            if ($user === 'locked') {
                Session::set('error', 'Tài khoản của bạn đã bị khóa, vui lòng liên hệ quản trị viên!');
                header('Location: /login');
                exit;
            } elseif ($user === 'partner_not_allowed') {
                Session::set('error', 'Đối tác không được phép đăng nhập bằng Google!');
                header('Location: /login');
                exit;
            } elseif ($user) {
                Session::set('user', $user);
                $redirect = $user['role'] === 'partners' && !$user['is_partner_paid'] ? '/upgrade' : '/';
                Session::set('success', 'Đăng nhập bằng Google thành công!');
                header('Location: ' . $redirect);
                exit;
            } else {
                Session::set('error', 'Đăng nhập bằng Google thất bại!');
                header('Location: /login');
                exit;
            }
        } else {
            header('Location: ' . $client->createAuthUrl());
            exit;
        }
    }

    public function logout()
    {
        Session::destroy();
        Session::set('success', 'Đăng xuất thành công!');
        header('Location: /login');
        exit;
    }

    public function changePassword()
    {
        if (!Session::get('user')) {
            Session::set('error', 'Vui lòng đăng nhập để đổi mật khẩu!');
            header('Location: /login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            $user = Session::get('user');

            if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
                $response = ['success' => false, 'message' => 'Vui lòng điền đầy đủ các trường!'];
            } elseif ($newPassword !== $confirmPassword) {
                $response = ['success' => false, 'message' => 'Mật khẩu mới và xác nhận không khớp!'];
            } else {
                $dbUser = $this->userModel->login($user['email'], $currentPassword);
                if ($dbUser) {
                    if ($this->userModel->updatePassword($user['id'], $newPassword)) {
                        $response = ['success' => true, 'message' => 'Đổi mật khẩu thành công!', 'redirect' => '/profile'];
                    } else {
                        $response = ['success' => false, 'message' => 'Đổi mật khẩu thất bại!'];
                    }
                } else {
                    $response = ['success' => false, 'message' => 'Mật khẩu hiện tại không đúng!'];
                }
            }

            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode($response);
                exit;
            }

            Session::set($response['success'] ? 'success' : 'error', $response['message']);
            if ($response['success']) {
                header('Location: /profile');
                exit;
            }
        }

        require_once __DIR__ . '/../Views/auth/change_password.php';
    }

    public function forgotPassword()
    {
        $email = $_POST['email'] ?? '';
        if (empty($email)) {
            $response = ['success' => false, 'message' => 'Vui lòng nhập email!'];
        } else {
            $user = $this->userModel->findByEmail($email);
            if ($user) {
                $token = bin2hex(random_bytes(32));
                if ($this->userModel->saveResetToken($user['id'], $token)) {
                    $mail = new \PHPMailer\PHPMailer\PHPMailer();
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'vanhieu12b6@gmail.com';
                    $mail->Password = 'rucjmrzvstrhkuuq';
                    $mail->SMTPSecure = 'tls';
                    $mail->Port = 587;

                    $mail->setFrom('no-reply@c2c.com', 'Chợ C2C');
                    $mail->addAddress($email);
                    $mail->Subject = 'Đặt lại mật khẩu';
                    $mail->Body = "Click để đặt lại mật khẩu: " . $_ENV['APP_URL'] . "/reset-password?token=$token";
                    if ($mail->send()) {
                        $response = ['success' => true, 'message' => 'Link đặt lại mật khẩu đã được gửi!'];
                    } else {
                        $response = ['success' => false, 'message' => 'Gửi email thất bại!'];
                    }
                } else {
                    $response = ['success' => false, 'message' => 'Lưu token thất bại!'];
                }
            } else {
                $response = ['success' => false, 'message' => 'Email không tồn tại!'];
            }

            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode($response);
                exit;
            }

            Session::set($response['success'] ? 'success' : 'error', $response['message']);
        }

        require_once __DIR__ . '/../Views/auth/forgot_password.php';
    }

    public function resetPassword()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['token'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            if (empty($token) || empty($newPassword) || empty($confirmPassword)) {
                $response = ['success' => false, 'message' => 'Vui lòng điền đầy đủ các trường!'];
            } elseif ($newPassword !== $confirmPassword) {
                $response = ['success' => false, 'message' => 'Mật khẩu mới và xác nhận không khớp!'];
            } else {
                $user = $this->userModel->findByResetToken($token);
                if ($user) {
                    if ($this->userModel->updatePassword($user['id'], $newPassword)) {
                        $this->userModel->clearResetToken($user['id']);
                        $response = ['success' => true, 'message' => 'Đặt lại mật khẩu thành công!', 'redirect' => '/login'];
                    } else {
                        $response = ['success' => false, 'message' => 'Đặt lại mật khẩu thất bại!'];
                    }
                } else {
                    $response = ['success' => false, 'message' => 'Token không hợp lệ hoặc đã hết hạn!'];
                }
            }

            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode($response);
                exit;
            }

            Session::set($response['success'] ? 'success' : 'error', $response['message']);
            if ($response['success']) {
                header('Location: /login');
                exit;
            }
        }

        $token = $_GET['token'] ?? '';
        if (empty($token) || !$this->userModel->findByResetToken($token)) {
            Session::set('error', 'Token không hợp lệ hoặc đã hết hạn!');
            header('Location: /forgot-password');
            exit;
        }

        require_once __DIR__ . '/../Views/auth/reset_password.php';
    }

    public function profile()
    {
        if (!Session::get('user')) {
            Session::set('error', 'Vui lòng đăng nhập để xem hồ sơ!');
            header('Location: /login');
            exit;
        }

        $userId = Session::get('user')['id'];
        $products = $this->productModel->getProductsByUserId($userId);
        $orders = $this->orderModel->getOrdersBySellerId($userId);

        require_once __DIR__ . '/../Views/profile/index.php';
    }
}
