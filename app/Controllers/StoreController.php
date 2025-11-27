<?php

namespace App\Controllers;

use App\Helpers\Session;
use App\Models\User;
use App\Models\Partners\Review;
// Giả định bạn có ProductModel
use App\Models\Product; 

class StoreController
{
    private $userModel;
    private $reviewModel;
    // private $productModel; // Nếu cần kiểm tra giao dịch chi tiết

    public function __construct()
    {
        $this->userModel = new User();
        $this->reviewModel = new Review();
        // $this->productModel = new Product(); 
    }

    public function show($shopId)
    {
        error_log("Received shopId: $shopId");
        $shop = $this->userModel->findById($shopId);
        if (!$shop) {
            error_log("Shop not found for ID: $shopId");
            Session::set('error', 'Không tìm thấy người dùng hoặc gian hàng!');
            header('Location: /');
            exit;
        }

        error_log("Showing store for ID: $shopId, is_partner_paid: " . $shop['is_partner_paid']);
        require_once __DIR__ . '/../Views/store/show.php';
    }

    /**
     * Gửi đánh giá cho Shop (is_partner_paid = 1)
     */
    public function review($shopId)
    {
        if (!Session::get('user')) {
            Session::set('error', 'Vui lòng đăng nhập để gửi đánh giá!');
            header('Location: /login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Session::set('error', 'Yêu cầu không hợp lệ!');
            header('Location: /store/' . $shopId);
            exit;
        }

        $shop = $this->userModel->findById($shopId);
        
        // 1. Kiểm tra Shop có hợp lệ và phải là Shop (is_partner_paid = 1)
        if (!$shop || (int)$shop['is_partner_paid'] !== 1) {
            Session::set('error', 'Không thể đánh giá vì đây không phải là shop chính thức!');
            header('Location: /store/' . $shopId);
            exit;
        }

        $rating = (int)($_POST['rating'] ?? 0);
        $comment = trim($_POST['comment'] ?? '');
        $buyerId = Session::get('user')['id'];
        $currentUserId = Session::get('user')['id'];

        // 2. Kiểm tra người mua không thể tự đánh giá
        if ($shop['id'] == $buyerId) {
            Session::set('error', 'Bạn không thể tự đánh giá gian hàng của mình!');
            header('Location: /store/' . $shopId);
            exit;
        }

        // 3. Kiểm tra dữ liệu hợp lệ
        if ($rating < 1 || $rating > 5 || empty($comment)) {
            Session::set('error', 'Vui lòng chọn điểm đánh giá từ 1-5 và nhập nhận xét!');
            header('Location: /store/' . $shopId);
            exit;
        }

        // Tùy chọn: Thêm logic kiểm tra xem người mua đã mua hàng của shop này chưa (thường dùng Order Model)
        // Đây là bước quan trọng để tránh đánh giá spam.
        // if (!$this->reviewModel->canReviewSeller($shopId, $buyerId)) {
        //     Session::set('error', 'Bạn chỉ có thể đánh giá sau khi đã mua hàng từ shop này.');
        //     header('Location: /store/' . $shopId);
        //     exit;
        // }
        
        // 4. Thực hiện tạo đánh giá. 
        // Lưu ý: Tôi đã cập nhật Model để không cần order_id trong hàm createSellerReview
        if ($this->reviewModel->createSellerReview($shopId, $buyerId, $rating, $comment)) {
            Session::set('success', 'Gửi đánh giá shop thành công!');
        } else {
            Session::set('error', 'Chỉ có thể gửi một đánh giá cho mỗi shop! ');
        }

        header('Location: /store/' . $shopId);
        exit;
    }

    /**
     * Gửi đánh giá cho Người dùng thường (is_partner_paid = 0)
     */
    public function userReview($shopId)
    {
        if (!Session::get('user')) {
            Session::set('error', 'Vui lòng đăng nhập để gửi đánh giá!');
            header('Location: /login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Session::set('error', 'Yêu cầu không hợp lệ!');
            header('Location: /store/' . $shopId);
            exit;
        }

        $shop = $this->userModel->findById($shopId);
        
        // 1. Kiểm tra Người được đánh giá phải là Người dùng thường (is_partner_paid = 0)
        if (!$shop || (int)$shop['is_partner_paid'] === 1) {
            Session::set('error', 'Không thể đánh giá vì đây là shop chính thức!');
            header('Location: /store/' . $shopId);
            exit;
        }

        $rating = (int)($_POST['rating'] ?? 0);
        $comment = trim($_POST['comment'] ?? '');
        $raterId = Session::get('user')['id'];
        $raterInfo = $this->userModel->findById($raterId);

        // 2. Kiểm tra người đánh giá không thể tự đánh giá
        if ($shop['id'] == $raterId) {
            Session::set('error', 'Bạn không thể tự đánh giá chính mình!');
            header('Location: /store/' . $shopId);
            exit;
        }

        // 3. Kiểm tra Người đánh giá không được là Shop (is_partner_paid = 1)
        if ($raterInfo && $raterInfo['is_partner_paid'] == 1) {
            Session::set('error', 'Shop không được phép đánh giá người dùng!');
            header('Location: /store/' . $shopId);
            exit;
        }

        // 4. Kiểm tra dữ liệu hợp lệ
        if ($rating < 1 || $rating > 5 || empty($comment)) {
            Session::set('error', 'Vui lòng chọn điểm đánh giá từ 1-5 và nhập nhận xét!');
            header('Location: /store/' . $shopId);
            exit;
        }
        
        // Tùy chọn: Thêm logic kiểm tra xem người đánh giá có giao dịch với người được đánh giá không.
        
        // 5. Thực hiện tạo đánh giá.
        if ($this->reviewModel->createUserReview($raterId, $shopId, $rating, $comment)) {
            Session::set('success', 'Gửi đánh giá người dùng thành công!');
        } else {
            Session::set('error', 'Không thể gửi đánh giá người dùng! (Lỗi hệ thống hoặc đã đánh giá)');
        }

        header('Location: /store/' . $shopId);
        exit;
    }

    /**
     * Phản hồi đánh giá Shop
     */
    public function replyReview($shopId, $reviewId)
    {
        $currentUser = Session::get('user');
        
        // 1. Kiểm tra Quyền: Chỉ Shop chính chủ (shopId == currentUserId) và là Shop trả phí (is_partner_paid = 1) mới được phản hồi
        if (!$currentUser || $currentUser['id'] != $shopId || (int)$currentUser['is_partner_paid'] !== 1) {
            Session::set('error', 'Bạn không có quyền phản hồi đánh giá này!');
            header('Location: /store/' . $shopId);
            exit; // Sửa lỗi cú pháp: Cần exit sau khi chuyển hướng trong khối if.
        }
        
        // 2. Kiểm tra phương thức POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Session::set('error', 'Yêu cầu không hợp lệ!');
            header('Location: /store/' . $shopId);
            exit;
        }

        $reply = trim($_POST['reply'] ?? '');
        
        // 3. Kiểm tra nội dung phản hồi
        if (empty($reply)) {
            Session::set('error', 'Vui lòng nhập nội dung phản hồi!');
            header('Location: /store/' . $shopId);
            exit;
        }

        // 4. Thực hiện phản hồi
        if ($this->reviewModel->reply($reviewId, $shopId, $reply)) {
            Session::set('success', 'Gửi phản hồi thành công!');
        } else {
            Session::set('error', 'Không thể gửi phản hồi! (Đánh giá không tồn tại/đã có phản hồi)');
        }

        header('Location: /store/' . $shopId);
        exit;
    }
}