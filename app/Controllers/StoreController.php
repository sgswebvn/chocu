<?php

namespace App\Controllers;

use App\Helpers\Session;
use App\Models\User;
use App\Models\Partners\Review;

class StoreController
{
    private $userModel;
    private $reviewModel;

    public function __construct()
    {
        $this->userModel = new User();
        $this->reviewModel = new Review();
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
        if (!$shop || $shop['is_partner_paid'] != 1) {
            Session::set('error', 'Không thể đánh giá vì đây không phải shop!');
            header('Location: /store/' . $shopId);
            exit;
        }

        $rating = $_POST['rating'] ?? 0;
        $comment = $_POST['comment'] ?? '';
        $buyerId = Session::get('user')['id'];

        if ($shop['id'] == $buyerId) {
            Session::set('error', 'Bạn không thể tự đánh giá gian hàng của mình!');
            header('Location: /store/' . $shopId);
            exit;
        }

        if ($rating < 1 || $rating > 5 || empty($comment)) {
            Session::set('error', 'Vui lòng chọn điểm đánh giá từ 1-5 và nhập nhận xét!');
            header('Location: /store/' . $shopId);
            exit;
        }

        if ($this->reviewModel->createSellerReview($shopId, $buyerId, $rating, $comment)) {
            Session::set('success', 'Gửi đánh giá shop thành công!');
        } else {
            Session::set('error', 'Không thể gửi đánh giá shop!');
        }

        header('Location: /store/' . $shopId);
        exit;
    }

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
        if (!$shop || $shop['is_partner_paid'] == 1) {
            Session::set('error', 'Không thể đánh giá vì đây là shop!');
            header('Location: /store/' . $shopId);
            exit;
        }

        $rating = $_POST['rating'] ?? 0;
        $comment = $_POST['comment'] ?? '';
        $raterId = Session::get('user')['id'];

        if ($shop['id'] == $raterId) {
            Session::set('error', 'Bạn không thể tự đánh giá chính mình!');
            header('Location: /store/' . $shopId);
            exit;
        }

        if ($this->userModel->findById($raterId)['is_partner_paid'] == 1) {
            Session::set('error', 'Shop không được phép đánh giá người dùng!');
            header('Location: /store/' . $shopId);
            exit;
        }

        if ($rating < 1 || $rating > 5 || empty($comment)) {
            Session::set('error', 'Vui lòng chọn điểm đánh giá từ 1-5 và nhập nhận xét!');
            header('Location: /store/' . $shopId);
            exit;
        }

        if ($this->reviewModel->createUserReview($raterId, $shopId, $rating, $comment)) {
            Session::set('success', 'Gửi đánh giá người dùng thành công!');
        } else {
            Session::set('error', 'Không thể gửi đánh giá người dùng!');
        }

        header('Location: /store/' . $shopId);
        exit;
    }

    public function replyReview($shopId, $reviewId)
    {
        error_log("StoreController: Attempting to reply to review ID: $reviewId for shop ID: $shopId");
        $currentUser = Session::get('user');
        if (!$currentUser) {
            error_log("StoreController: Reply failed: User not logged in");
            Session::set('error', 'Vui lòng đăng nhập để phản hồi đánh giá!');
            header('Location: /login');
            exit;
        }

        if ($currentUser['id'] != $shopId) {
            error_log("StoreController: Reply failed: User ID {$currentUser['id']} is not shop ID $shopId");
            Session::set('error', 'Bạn không có quyền phản hồi đánh giá này!');
            header('Location: /store/' . $shopId);
            exit;
        }

        if ($currentUser['is_partner_paid'] != 1) {
            error_log("StoreController: Reply failed: User ID $shopId is not a partner shop");
            Session::set('error', 'Bạn không có quyền phản hồi vì đây không phải shop!');
            header('Location: /store/' . $shopId);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            error_log("StoreController: Reply failed: Invalid request method");
            Session::set('error', 'Yêu cầu không hợp lệ!');
            header('Location: /store/' . $shopId);
            exit;
        }

        $reply = trim($_POST['reply'] ?? '');
        if (empty($reply)) {
            error_log("StoreController: Reply failed: Empty reply content");
            Session::set('error', 'Vui lòng nhập nội dung phản hồi!');
            header('Location: /store/' . $shopId);
            exit;
        }

        if ($this->reviewModel->reply($reviewId, $shopId, $reply)) {
            error_log("StoreController: Reply successful for review ID: $reviewId, shop ID: $shopId");
            Session::set('success', 'Gửi phản hồi thành công!');
        } else {
            error_log("StoreController: Reply failed for review ID: $reviewId, shop ID: $shopId");
            Session::set('error', 'Không thể gửi phản hồi!');
        }

        header('Location: /store/' . $shopId);
        exit;
    }
}
