<?php

namespace App\Helpers;

use App\Helpers\Session;

Session::start();

$error = Session::get('error');
$success = Session::get('success');
if ($error) {
    Session::unset('error');
}
if ($success) {
    Session::unset('success');
}

include __DIR__ . '/../layouts/header.php';
?>
<main class="pt-90">
    <div class="mb-4 pb-4"></div>
    <section class="login-register container">
        <h2 class="mb-5 text-center">Đăng ký tài khoản</h2>

        <!-- Bước 1: Nhập thông tin -->
        <div id="step-register" class="register-step">
            <form id="form-register-info">
                <input type="hidden" name="action" value="send_otp">
                <div class="form-floating mb-3">
                    <input type="text" class="form-control" name="username" required>
                    <label>Tên người dùng *</label>
                </div>
                <div class="form-floating mb-3">
                    <input type="email" class="form-control" name="email" required>
                    <label>Email *</label>
                </div>
                <div class="form-floating mb-4">
                    <input type="password" class="form-control" name="password" required minlength="6">
                    <label>Mật khẩu *</label>
                </div>
                <button type="submit" class="btn btn-primary w-100">Gửi mã xác minh</button>
            </form>
        </div>

        <!-- Bước 2: Nhập OTP -->
        <div id="step-otp" class="register-step d-none">
            <div class="text-center mb-4">
                <h5>Nhập mã OTP đã gửi đến email</h5>
                <small class="text-muted">Mã có hiệu lực trong 5 phút</small>
            </div>
            <form id="form-verify-otp">
                <input type="hidden" name="action" value="verify_otp">
                <div class="text-center mb-3">
                    <input type="text" class="form-control form-control-lg text-center" 
                           name="otp" maxlength="6" placeholder="------" required 
                           style="letter-spacing: 10px; font-size: 24px;">
                </div>
                <button type="submit" class="btn btn-success w-100">Xác nhận đăng ký</button>
                <div class="mt-3 text-center">
                    <a href="#" id="resend-otp" class="text-primary">Gửi lại mã</a>
                </div>
            </form>
        </div>
        <div class="text-center mt-4">
        <h3 ><a href="/partner-register" >Đăng ký tài khoản đối tác </a></h3> 
<br>
            <span>Đã có tài khoản? </span>
            <a href="/login" class="btn-text">Đăng nhập</a>
        </div>
    </section>
</main>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.getElementById('form-register-info').onsubmit = async function(e) {
    e.preventDefault();
    const formData = new FormData(this);

    const res = await fetch('/register', {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    });
    const data = await res.json();

    Swal.fire({
        icon: data.success ? 'success' : 'error',
        title: data.success ? 'Thành công' : 'Lỗi',
        text: data.message,
        timer: data.success ? 1500 : null
    });

    if (data.success && data.step === 'verify') {
        document.getElementById('step-register').classList.add('d-none');
        document.getElementById('step-otp').classList.remove('d-none');
    }
};

document.getElementById('form-verify-otp').onsubmit = async function(e) {
    e.preventDefault();
    const formData = new FormData(this);

    const res = await fetch('/register', {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    });
    const data = await res.json();

    Swal.fire({
        icon: data.success ? 'success' : 'error',
        title: data.success ? 'Thành công!' : 'Thất bại',
        text: data.message,
        timer: data.success ? 2000 : null
    }).then(() => {
        if (data.success && data.redirect) {
            window.location.href = data.redirect;
        }
    });
};

// Gửi lại OTP
document.getElementById('resend-otp').onclick = async function(e) {
    e.preventDefault();
    // Có thể lấy lại thông tin từ session hoặc yêu cầu nhập lại email
    Swal.fire('Chức năng gửi lại mã đang phát triển');
};
</script>
<?php
include __DIR__ . '/../layouts/footer.php';
?>