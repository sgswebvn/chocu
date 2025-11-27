<?php

namespace App\Controllers\Partners;

use App\Helpers\Session;
use App\Models\Partners\Review;
use App\Models\Notification;
use App\WebSocket\NotificationServer;

class PReviewController
{
    private $reviewModel;
    private $notificationModel;

    public function __construct()
    {
        $this->reviewModel = new Review();
        $this->notificationModel = new Notification();
        if (!Session::get('user')) {
            error_log("PReviewController: User not logged in");
            Session::set('error', 'Vui lòng đăng nhập để quản lý đánh giá!');
            header('Location: /login');
            exit;
        }
        $user = Session::get('user');
        if ($user['role'] !== 'partners' || !isset($user['is_partner_paid']) || !$user['is_partner_paid']) {
            error_log("PReviewController: Access denied for user ID: {$user['id']}, role: {$user['role']}, is_partner_paid: " . ($user['is_partner_paid'] ?? 'not set'));
            Session::set('error', 'Bạn cần nâng cấp tài khoản đối tác để quản lý đánh giá!');
            header('Location: /upgrade');
            exit;
        }
    }

    public function index()
    {
        $user = Session::get('user');
        $reviews = $this->reviewModel->findByUser($user['id']);
        error_log("PReviewController: Loaded " . count($reviews) . " reviews for user ID: {$user['id']}");
        require_once __DIR__ . '/../../Views/a-partner/review/index.php';
    }
    

    public function reply($id)
    {
        error_log("PReviewController: Processing reply request for review ID: $id");
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Session::set('error', 'Yêu cầu không hợp lệ!');
            header('Location: /partners/review');
            exit;
        }

        $user = Session::get('user');
        $reply = trim($_POST['reply'] ?? '');

        if (!$id || !$reply) {
            Session::set('error', 'Vui lòng điền đầy đủ thông tin!');
            header('Location: /partners/review');
            exit;
        }

        // Kiểm tra review có thuộc shop không
        $stmt = $this->reviewModel->db->prepare("SELECT id, reply FROM seller_ratings WHERE id = ? AND seller_id = ?");
        $stmt->execute([$id, $user['id']]);
        $review = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$review) {
            Session::set('error', 'Đánh giá không tồn tại hoặc không thuộc shop này!');
            header('Location: /partners/review');
            exit;
        }
        if ($review['reply']) {
            Session::set('error', 'Đánh giá này đã được trả lời!');
            header('Location: /partners/review');
            exit;
        }

        if ($this->reviewModel->reply($id, $user['id'], $reply)) {
            $this->notificationModel->create(
                $user['id'],
                'review',
                'Trả lời đánh giá',
                'Bạn đã trả lời một đánh giá cho shop!',
                '/partners/review'
            );
            NotificationServer::sendNotification(
                $user['id'],
                'review',
                [
                    'title' => 'Trả lời đánh giá',
                    'message' => 'Bạn đã trả lời một đánh giá cho shop!',
                    'link' => '/partners/review'
                ]
            );
            error_log("PReviewController: Reply successful for review ID: $id, user ID: {$user['id']}");
            Session::set('success', 'Trả lời đánh giá thành công!');
        } else {
            error_log("PReviewController: Reply failed for review ID: $id, user ID: {$user['id']}");
            Session::set('error', 'Trả lời đánh giá thất bại!');
        }
        header('Location: /partners/review');
        exit;
    }
}
