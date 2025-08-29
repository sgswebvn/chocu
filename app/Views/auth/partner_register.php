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
        <h1 class="mb-5">Đăng Ký Làm Đối Tác</h1>
        <div class="tab-content pt-2">
            <div class="tab-pane fade show active" id="tab-item-partner-register" role="tabpanel">
                <div class="register-form">
                    <form id="partner-register-form" method="POST" action="/partner-register" name="partner-register-form" class="needs-validation" novalidate>
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control form-control_gray" name="username" id="username" required>
                            <label for="username">Tên người dùng *</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="email" class="form-control form-control_gray" name="email" id="email" required>
                            <label for="email">Email *</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="password" class="form-control form-control_gray" name="password" id="password" required>
                            <label for="password">Mật khẩu *</label>
                        </div>
                        <div class="mb-3">
                            <p class="text-secondary">Sau khi đăng ký, bạn cần mua gói nâng cấp 20,000 VNĐ để trở thành đối tác chính thức.</p>
                        </div>
                        <button class="btn btn-primary w-100 text-uppercase" type="submit">Đăng Ký Đối Tác</button>
                        <div class="customer-option mt-4 text-center">
                            <span class="text-secondary">Đã có tài khoản?</span>
                            <a href="/login" class="btn-text ms-2">Đăng nhập</a>
                        </div>
                        <div class="customer-option mt-2 text-center">
                            <span class="text-secondary">Đăng ký làm người dùng?</span>
                            <a href="/register" class="btn-text ms-2">Đăng ký</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</main>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('partner-register-form');
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            if (!form.checkValidity()) {
                form.classList.add('was-validated');
                return;
            }

            const formData = new FormData(form);
            fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    Swal.fire({
                        icon: data.success ? 'success' : 'error',
                        title: data.success ? 'Thành công' : 'Lỗi',
                        text: data.message,
                        confirmButtonText: 'OK',
                        confirmButtonColor: data.success ? '#3085d6' : '#d33',
                        timer: data.success ? 2000 : null,
                        timerProgressBar: true
                    }).then(() => {
                        if (data.success && data.redirect) {
                            window.location.href = data.redirect;
                        }
                    });
                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Lỗi',
                        text: 'Đã xảy ra lỗi khi đăng ký!',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#d33'
                    });
                });
        });

        <?php if ($error): ?>
            Swal.fire({
                icon: 'error',
                title: 'Lỗi',
                text: '<?= htmlspecialchars($error) ?>',
                confirmButtonText: 'OK',
                confirmButtonColor: '#d33'
            });
        <?php endif; ?>
        <?php if ($success): ?>
            Swal.fire({
                icon: 'success',
                title: 'Thành công',
                text: '<?= htmlspecialchars($success) ?>',
                confirmButtonText: 'OK',
                confirmButtonColor: '#3085d6',
                timer: 2000,
                timerProgressBar: true
            });
        <?php endif; ?>
    });
</script>

<?php
include __DIR__ . '/../layouts/footer.php';
?>