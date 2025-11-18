<?php
namespace App\Controllers;

use App\Helpers\Session;
use App\Models\Withdrawal;

class AccountantController
{
    private $withdrawalModel;

    public function __construct()
    {
        $user = Session::get('user');
        if (!$user || $user['role'] !== 'accountant') {
            Session::set('error', 'Bạn không có quyền truy cập khu vực này!');
            header('Location: /login');
            exit;
        }
        $this->withdrawalModel = new Withdrawal();
    }

    public function dashboard()
    {
        $pending = $this->withdrawalModel->getPendingRequests();
        require_once __DIR__ . '/../Views/accountant/dashboard.php';
    }

    public function history()
    {
        $processed = $this->withdrawalModel->getProcessedRequests();
        require_once __DIR__ . '/../Views/accountant/history.php';
    }

    public function approve()
    {
        $id = $_POST['id'] ?? 0;
        if ($this->withdrawalModel->approve($id)) {
            echo json_encode(['success' => true, 'message' => 'Đã duyệt yêu cầu rút tiền']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi khi duyệt']);
        }
    }

    public function reject()
    {
        $id = $_POST['id'] ?? 0;
        $note = $_POST['note'] ?? 'Không đủ điều kiện';
        if ($this->withdrawalModel->reject($id, $note)) {
            echo json_encode(['success' => true, 'message' => 'Đã từ chối yêu cầu']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi khi từ chối']);
        }
    }

    public function complete()
    {
        $id = $_POST['id'] ?? 0;
        if ($this->withdrawalModel->complete($id)) {
            echo json_encode(['success' => true, 'message' => 'Đã xác nhận chuyển tiền thành công']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Chỉ có thể xác nhận khi đã duyệt']);
        }
    }
}