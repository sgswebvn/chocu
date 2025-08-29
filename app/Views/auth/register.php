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
        <ul class="nav nav-tabs mb-5" id="login_register_tab" role="tablist">
            <li class="nav-item" role="presentation">
                <a class="nav-link nav-link_underscore active" id="register-tab" data-bs-toggle="tab" href="#tab-item-register"
                    role="tab" aria-controls="tab-item-register" aria-selected="true">Đăng ký</a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link nav-link_underscore" href="/login">Đăng nhập</a>
            </li>
        </ul>
        <div class="tab-content pt-2" id="login_register_tab_content">
            <div class="tab-pane fade show active" id="tab-item-register" role="tabpanel" aria-labelledby="register-tab">
                <div class="register-form">
                    <form id="register-form" method="POST" action="/register" name="register-form" class="needs-validation" novalidate>
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
                        <button class="btn btn-primary w-100 text-uppercase" type="submit">Đăng ký</button>
                        <div class="customer-option mt-4 text-center">
                            <span class="text-secondary">Đã có tài khoản?</span>
                            <a href="/login" class="btn-text ms-2">Đăng nhập</a>
                        </div>
                        <div class="customer-option mt-2 text-center">
                            <span class="text-secondary">Muốn trở thành đối tác?</span>
                            <a href="/partner-register" class="btn-text ms-2">Đăng ký làm đối tác</a>
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
        const form = document.getElementById('register-form');
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