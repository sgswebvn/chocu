<?php

namespace App\Controllers;

use App\Models\Notification;
use App\Helpers\Session;

class NotificationController
{
    private $notificationModel;

    public function __construct()
    {
        $this->notificationModel = new Notification();
        if (!Session::get('user')) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập!']);
            exit;
        }
    }

    public function markRead()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? null;
            $userId = Session::get('user')['id'];
            if ($id && $this->notificationModel->markAsRead($id, $userId)) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Đánh dấu thất bại!']);
            }
        }
    }

    public function markAllRead()
    {
        header('Content-Type: application/json');
        $userId = Session::get('user')['id'];
        if ($this->notificationModel->markAllAsRead($userId)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Đánh dấu tất cả thất bại!']);
        }
    }

    public function getUnreadCount()
    {
        header('Content-Type: application/json');
        $userId = Session::get('user')['id'];
        $count = $this->notificationModel->getUnreadCount($userId);
        echo json_encode(['success' => true, 'count' => $count]);
    }
}
