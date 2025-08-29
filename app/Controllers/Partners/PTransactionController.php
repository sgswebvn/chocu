<?php

namespace App\Controllers\Partners;

use App\Helpers\Session;
use App\Config\Database;

class PTransactionController
{
    private $db;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
        if (!Session::get('user')) {
            Session::set('error', 'Vui lòng đăng nhập để xem lịch sử giao dịch!');
            header('Location: /login');
            exit;
        }
        $user = Session::get('user');
        if ($user['role'] !== 'partners' || !isset($user['is_partner_paid']) || !$user['is_partner_paid']) {
            Session::set('error', 'Bạn cần nâng cấp tài khoản đối tác để xem lịch sử giao dịch!');
            header('Location: /upgrade');
            exit;
        }
    }

    public function index()
    {
        $user = Session::get('user');
        $stmt = $this->db->prepare("SELECT * FROM transactions WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$user['id']]);
        $transactions = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        require_once __DIR__ . '/../../Views/a-partner/transaction/index.php';
    }
}
