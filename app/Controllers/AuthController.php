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
        // $this->emailService không cần khởi tạo ở đây nếu chỉ dùng trong các hàm xử lý
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
    
    /**
     * Xử lý gửi OTP cho đăng ký User thường
     */
    private function handleSendOtp()
    {
        $username = trim($_POST['username'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = 'user';

        if (empty($username) || empty($email) || empty($password)) {
            return $this->jsonResponse(false, 'Vui lòng điền đầy đủ thông tin!');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->jsonResponse(false, 'Email không hợp lệ!');
        }
        
        // 1. Tìm User theo Email
        $user = $this->userModel->findByEmail($email);
        $isNewUser = false; // Cờ theo dõi để xử lý lỗi sau này

        if ($user) {
            // A. Email đã được đăng ký và kích hoạt
            if ((int)$user['is_active'] === 1) {
                return $this->jsonResponse(false, 'Email này đã được đăng ký và kích hoạt!');
            }
            
            // B. User tồn tại nhưng CHƯA kích hoạt (Cho phép gửi lại/cập nhật thông tin)
            // Cập nhật thông tin (username/password mới nếu người dùng thay đổi)
            if (!$this->userModel->updatePendingUser($user['id'], $username, $password, $role)) {
                return $this->jsonResponse(false, 'Lỗi cập nhật thông tin đăng ký dở dang!');
            }

        } else {
            // 2. Email chưa tồn tại -> Tạo User mới
            if ($this->userModel->findByUsername($username)) {
                return $this->jsonResponse(false, 'Tên người dùng đã tồn tại!');
            }
            
            if (!$this->userModel->registerUser($username, $email, $password, 0, $role)) {
                return $this->jsonResponse(false, 'Đăng ký thất bại! Vui lòng thử lại.');
            }
            
            // Lấy lại User sau khi tạo để dùng ID/username
            $user = $this->userModel->findByEmail($email);
            if (!$user) {
                return $this->jsonResponse(false, 'Lỗi hệ thống: Không tìm thấy User vừa tạo.');
            }
            $isNewUser = true;
        }

        // 3. Tạo và lưu OTP mới
        $otp = sprintf("%06d", mt_rand(100000, 999999));
        if (!$this->userModel->setVerificationCode($email, $otp)) {
            // Logic xóa user chỉ áp dụng nếu User là MỚI và lỗi DB
            if ($isNewUser) {
                $this->userModel->db->prepare("DELETE FROM users WHERE email = ?")->execute([$email]);
            }
            return $this->jsonResponse(false, 'Lỗi hệ thống khi lưu mã OTP!');
        }

        // 4. Gửi email
        $emailService = new \App\Helpers\EmailService();
        if ($emailService->sendVerificationCode($email, $user['username'], $otp)) {
            Session::set('pending_verification_email', $email);
            return $this->jsonResponse(true, 'Đã gửi mã OTP đến email của bạn!', ['step' => 'verify']);
        } else {
     
            return $this->jsonResponse(false, 'Không thể gửi email OTP. Vui lòng thử lại!');
        }
    }

    /**
     * Xử lý xác minh OTP cho User thường
     */
    private function handleVerifyOtp()
    {
        $otp   = trim($_POST['otp'] ?? '');
        $email = Session::get('pending_verification_email');

        if (empty($otp) || empty($email)) {
             // Thêm unset session nếu hết phiên
            Session::unset('pending_verification_email');
            return $this->jsonResponse(false, 'Dữ liệu không hợp lệ hoặc đã hết phiên!');
        }

        // DÙNG MODEL ĐỂ VERIFY -> Kích hoạt User + Clear OTP trong DB
        $user = $this->userModel->verifyCode($email, $otp);

        if ($user) {
            Session::unset('pending_verification_email');
            Session::set('user', $user); // Đăng nhập
            if (class_exists(NotificationServer::class) && $user['role'] === 'user') {
    NotificationServer::sendNotification(
        $user['id'],
        'auth',
        [
            'title' => 'Chào mừng!',
            'message' => "Chào mừng {$user['username']} đến với hệ thống!",
            'link' => '/'
        ]
    );
}
            $redirect = ($user['role'] === 'partners') ? '/upgrade' : '/';

            return $this->jsonResponse(true, 'Xác minh thành công! Chào mừng bạn!', [
                'redirect' => $redirect
            ]);
        } else {
            // Khi OTP sai, KHÔNG unset session để user có thể thử lại
            return $this->jsonResponse(false, 'Mã OTP không đúng hoặc đã hết hạn!');
        }
    }
    
    /**
     * Hàm response JSON
     */
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
            $action = $_POST['action'] ?? '';

            // Bước 1: Gửi OTP
            if ($action === 'send_otp') {
                $this->handlePartnerSendOtp();
            }

            // Bước 2: Xác minh OTP + Hoàn tất đăng ký đối tác
            if ($action === 'verify_otp') {
                $this->handlePartnerVerifyOtp();
            }
        }

        // Hiển thị form đăng ký đối tác (bước nhập thông tin)
        require_once __DIR__ . '/../Views/auth/partner_register.php';
    }

    /**
     * Xử lý gửi OTP cho đăng ký đối tác
     */
    private function handlePartnerSendOtp()
    {
        $username = trim($_POST['username'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = 'partners';

        if (empty($username) || empty($email) || empty($password)) {
            return $this->jsonResponse(false, 'Vui lòng điền đầy đủ thông tin!');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->jsonResponse(false, 'Email không hợp lệ!');
        }
        
        $user = $this->userModel->findByEmail($email);
        $isNewUser = false;

        if ($user) {
            // A. Email đã được đăng ký và kích hoạt
            if ((int)$user['is_active'] === 1) {
                 return $this->jsonResponse(false, 'Email này đã được đăng ký và kích hoạt!');
            }
            
            // B. User tồn tại nhưng CHƯA kích hoạt (Cho phép gửi lại/cập nhật thông tin)
            // Cập nhật thông tin (username/password/role mới)
            if (!$this->userModel->updatePendingUser($user['id'], $username, $password, $role)) {
                return $this->jsonResponse(false, 'Lỗi cập nhật thông tin đăng ký dở dang!');
            }

        } else {
            // 2. Email chưa tồn tại -> Tạo User mới
            if ($this->userModel->findByUsername($username)) {
                return $this->jsonResponse(false, 'Tên người dùng đã tồn tại!');
            }
            
            if (!$this->userModel->registerUser($username, $email, $password, 0, $role)) {
                return $this->jsonResponse(false, 'Đăng ký thất bại! Vui lòng thử lại.');
            }
            
            $user = $this->userModel->findByEmail($email);
            if (!$user) {
                return $this->jsonResponse(false, 'Lỗi hệ thống: Không tìm thấy User vừa tạo.');
            }
            $isNewUser = true;
        }

        // 3. Tạo và lưu OTP mới
        $otp = sprintf("%06d", mt_rand(100000, 999999));
        if (!$this->userModel->setVerificationCode($email, $otp)) {
            // Logic xóa user chỉ áp dụng nếu User là MỚI và lỗi DB
            if ($isNewUser) {
                $this->userModel->db->prepare("DELETE FROM users WHERE email = ?")->execute([$email]);
            }
            return $this->jsonResponse(false, 'Lỗi hệ thống khi lưu mã OTP!');
        }

        // 4. Gửi email
        $emailService = new \App\Helpers\EmailService();
        if ($emailService->sendVerificationCode($email, $user['username'], $otp)) {
            Session::set('pending_verification_email', $email);
            Session::set('pending_partner_registration', true);
            return $this->jsonResponse(true, 'Đã gửi mã OTP!', ['step' => 'verify']);
        } else {
            // Nếu gửi email lỗi, KHÔNG xóa User
            return $this->jsonResponse(false, 'Gửi email thất bại!');
        }
    }
    
    /**
     * Xử lý xác minh OTP cho đối tác
     */
    private function handlePartnerVerifyOtp()
    {
        $otp   = trim($_POST['otp'] ?? '');
        $email = Session::get('pending_verification_email');

        if (empty($otp) || empty($email)) {
             // Thêm unset session nếu hết phiên
            Session::unset('pending_verification_email');
            Session::unset('pending_partner_registration');
            return $this->jsonResponse(false, 'Dữ liệu không hợp lệ hoặc đã hết phiên!');
        }

        $user = $this->userModel->verifyCode($email, $otp);

        if ($user) {
            // Đăng nhập luôn cho user (BẮT BUỘC ĐI ĐẦU)
            Session::set('user', $user);
            
            // Hủy session
            Session::unset('pending_verification_email');
            Session::unset('pending_partner_registration');

            // Gửi thông báo WebSocket (Đảm bảo class tồn tại)
            if (class_exists(NotificationServer::class)) {
                NotificationServer::sendNotification(
                    $user['id'],
                    'auth',
                    [
                        'title' => 'Chào mừng đối tác mới!',
                        'message' => "Chào mừng {$user['username']}! Vui lòng mua gói nâng cấp để trở thành đối tác chính thức.",
                        'link' => '/upgrade'
                    ]
                );
            }

            // activateUser không cần thiết vì đã có trong verifyCode
            $this->jsonResponse(true, 'Đăng ký đối tác thành công! Chào mừng bạn!', [
                'redirect' => '/upgrade'
            ]);
        } else {
            $this->jsonResponse(false, 'Mã OTP không đúng hoặc đã hết hạn!');
        }
    }

    // ... (Các hàm login, googleLogin, logout, changePassword, forgotPassword, resetPassword, profile giữ nguyên)
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
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            if (empty($email)) {
                $response = ['success' => false, 'message' => 'Vui lòng nhập email!'];
            } else {
                $user = $this->userModel->findByEmail($email);
                if ($user) {
                    $token = bin2hex(random_bytes(32));
                    if ($this->userModel->saveResetToken($user['id'], $token)) {
                        // Khởi tạo PHPMailer (nên dùng EmailService)
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