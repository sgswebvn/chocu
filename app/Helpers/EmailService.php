<?php

namespace App\Helpers;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailService
{
    private $mailer;
    
    public function __construct()
    {
        $this->mailer = new PHPMailer(true);
        
        // Cấu hình SMTP từ .env
        $this->mailer->isSMTP();
        $this->mailer->Host = 'smtp.gmail.com';
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = $_ENV['SMTP_USER'];
        $this->mailer->Password = $_ENV['SMTP_PASS'];
        $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $this->mailer->Port = 465;
        $this->mailer->CharSet = 'UTF-8';
        $this->mailer->setFrom($_ENV['SMTP_NAME'], 'Hệ thống quản lý');
    }
    
    public function sendActivationEmail($to, $username, $isActive)
{
    try {
        $this->mailer->addAddress($to);
        $this->mailer->isHTML(true);

        if ($isActive == 1) {
            $this->mailer->Subject = 'Tài khoản của bạn đã được kích hoạt thành công';
            $statusText = 'kích hoạt';
            $color = '#28a745';
        } else {
            $this->mailer->Subject = 'Tài khoản của bạn đã bị tạm khóa';
            $statusText = 'tạm khóa';
            $color = '#dc3545';
        }

        $this->mailer->Body = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto; padding: 30px; border: 1px solid #ddd; border-radius: 10px;'>
                <h2 style='color: {$color};'>Thông báo trạng thái tài khoản</h2>
                <p>Xin chào <strong>{$username}</strong>,</p>
                <p>Tài khoản của bạn đã được <strong style='color: {$color};'>{$statusText}</strong> bởi quản trị viên.</p>
                <p>Thời gian: " . date('d/m/Y H:i:s') . "</p>
                <br>
                <p>Nếu bạn cần hỗ trợ, vui lòng liên hệ qua email hỗ trợ.</p>
                <hr>
                <small>Chợ C2C - Hệ thống quản lý</small>
            </div>
        ";

        $this->mailer->send();
        return true;
    } catch (Exception $e) {
        error_log("Email lỗi: " . $e->getMessage());
        return false;
    }
}
    public function sendVerificationCode($to, $username, $code)
{
    try {
        $this->mailer->addAddress($to);
        $this->mailer->isHTML(true);
        $this->mailer->Subject = 'Mã xác minh tài khoản Chợ C2C';

        $this->mailer->Body = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto; padding: 20px; border: 1px solid #eee;'>
                <h2 style='color: #333;'>Xác minh tài khoản</h2>
                <p>Xin chào <strong>{$username}</strong>,</p>
                <p>Mã xác minh của bạn là:</p>
                <h1 style='background: #f0f0f0; padding: 15px; text-align: center; letter-spacing: 5px; font-size: 28px;'>
                    <strong>{$code}</strong>
                </h1>
                <p>Mã này có hiệu lực trong <strong>5 phút</strong>.</p>
                <p>Nếu bạn không yêu cầu, vui lòng bỏ qua email này.</p>
                <hr>
                <small>Chợ C2C - Nền tảng mua bán C2C</small>
            </div>
        ";

        $this->mailer->send();
        return true;
    } catch (Exception $e) {
        error_log("Email send failed: " . $e->getMessage());
        return false;
    }
}
}
?>