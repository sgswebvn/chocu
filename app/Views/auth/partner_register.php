<?php
namespace App\Helpers;
use App\Helpers\Session;

Session::start();

$error   = Session::get('error');
$success = Session::get('success');
if ($error)   Session::unset('error');
if ($success) Session::unset('success');

include __DIR__ . '/../layouts/header.php';
?>

<main class="pt-90">
    <div class="mb-4 pb-4"></div>
    <section class="login-register container">
        <h2 class="mb-5 text-center">Đăng Ký Làm Đối Tác</h2>

        <!-- Bước 1: Nhập thông tin -->
        <div id="step-register" class="register-step">
            <div class="alert alert-info text-center mb-4">
                Sau khi xác minh email, bạn sẽ được chuyển đến trang mua gói nâng cấp (20.000 VNĐ) để trở thành đối tác chính thức.
            </div>

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

                <button type="submit" class="btn btn-primary w-100">Gửi mã xác minh OTP</button>
            </form>
        </div>

        <!-- Bước 2: Nhập OTP -->
        <div id="step-otp" class="register-step d-none">
            <div class="text-center mb-4">
                <h5>Nhập mã OTP đã gửi đến email của bạn</h5>
                <small class="text-muted">Mã có hiệu lực trong 5 phút</small>
            </div>

            <form id="form-verify-otp">
                <input type="hidden" name="action" value="verify_otp">
                
                <div class="text-center mb-3">
                    <input type="text" 
                           class="form-control form-control-lg text-center" 
                           name="otp" 
                           maxlength="6" 
                           placeholder="------" 
                           required 
                           style="letter-spacing: 10px; font-size: 24px;">
                </div>

                <button type="submit" class="btn btn-success w-100">Hoàn tất đăng ký đối tác</button>

                <div class="mt-3 text-center">
                    <a href="#" id="resend-otp" class="text-primary">Gửi lại mã OTP</a>
                </div>
            </form>
        </div>

        <div class="text-center mt-4">
            <span>Đã có tài khoản? </span>
            <a href="/login" class="btn-text">Đăng nhập</a>
            <span class="mx-2">|</span>
            <a href="/register" class="btn-text">Đăng ký người dùng thường</a>
        </div>
    </section>
</main>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// Gửi thông tin đăng ký → nhận OTP
document.getElementById('form-register-info').onsubmit = async function(e) {
    e.preventDefault();
    const formData = new FormData(this);

    try {
        const res = await fetch('/partner-register', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await res.json();

        Swal.fire({
            icon: data.success ? 'success' : 'error',
            title: data.success ? 'Thành công!' : 'Lỗi',
            text: data.message,
            timer: data.success ? 1500 : null,
            showConfirmButton: !data.success
        });

        if (data.success && data.step === 'verify') {
            document.getElementById('step-register').classList.add('d-none');
            document.getElementById('step-otp').classList.remove('d-none');
        }
    } catch (err) {
        Swal.fire('Lỗi', 'Không thể kết nối đến máy chủ!', 'error');
    }
};

// Xác minh OTP
document.getElementById('form-verify-otp').onsubmit = async function(e) {
    e.preventDefault();
    const formData = new FormData(this);

    try {
        const res = await fetch('/partner-register', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await res.json();

        Swal.fire({
            icon: data.success ? 'success' : 'error',
            title: data.success ? 'Chúc mừng!' : 'Thất bại',
            text: data.message,
            timer: data.success ? 2000 : null,
            showConfirmButton: !data.success
        }).then(() => {
            if (data.success && data.redirect) {
                window.location.href = data.redirect; // Sẽ là /upgrade
            }
        });
    } catch (err) {
        Swal.fire('Lỗi', 'Không thể kết nối đến máy chủ!', 'error');
    }
};

// Gửi lại OTP (tùy chọn nâng cao: lấy lại email từ session hoặc nhập lại)
document.getElementById('resend-otp').onclick = async function(e) {
    e.preventDefault();

    Swal.fire({
        title: 'Gửi lại mã OTP?',
        text: "Mã mới sẽ được gửi đến email của bạn",
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Gửi lại',
        cancelButtonText: 'Hủy'
    }).then(async (result) => {
        if (result.isConfirmed) {
            // Gọi lại bước send_otp (có thể cần lưu tạm email ở đâu đó hoặc yêu cầu nhập lại)
            // Cách đơn giản nhất: quay lại bước 1
            Swal.fire('Info', 'Vui lòng nhập lại thông tin để gửi mã mới', 'info');
            document.getElementById('step-otp').classList.add('d-none');
            document.getElementById('step-register').classList.remove('d-none');
            document.getElementById('form-register-info').reset();
        }
    });
};
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>