<?php
namespace App\Models;

use App\Config\Database;
use PDO;

class Withdrawal
{
    private $db;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
    }

    public function getPendingRequests()
    {
        $stmt = $this->db->prepare("
            SELECT wr.*, u.username, u.email 
            FROM withdrawal_requests wr
            JOIN users u ON wr.user_id = u.id
            WHERE wr.status = 'pending'
            ORDER BY wr.requested_at DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProcessedRequests()
    {
        $stmt = $this->db->prepare("
            SELECT wr.*, u.username, u.email 
            FROM withdrawal_requests wr
            JOIN users u ON wr.user_id = u.id
            WHERE wr.status IN ('approved', 'completed', 'rejected')
            ORDER BY wr.processed_at DESC, wr.requested_at DESC
            LIMIT 50
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function approve($id)
    {
        $stmt = $this->db->prepare("UPDATE withdrawal_requests SET status = 'approved', processed_at = NOW() WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function reject($id, $note = '')
    {
        $stmt = $this->db->prepare("UPDATE withdrawal_requests SET status = 'rejected', processed_at = NOW(), admin_note = ? WHERE id = ?");
        return $stmt->execute([$note, $id]);
    }

    public function complete($id)
    {
        $stmt = $this->db->prepare("UPDATE withdrawal_requests SET status = 'completed', processed_at = NOW() WHERE id = ? AND status = 'approved'");
        return $stmt->execute([$id]);
    }

    public function getById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM withdrawal_requests WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}