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
                <a class="nav-link nav-link_underscore" href="/register">Đăng ký</a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link nav-link_underscore active" id="login-tab" data-bs-toggle="tab" href="#tab-item-login"
                    role="tab" aria-controls="tab-item-login" aria-selected="true">Đăng nhập</a>
            </li>
        </ul>
        <div class="tab-content pt-2" id="login_register_tab_content">
            <div class="tab-pane fade show active" id="tab-item-login" role="tabpanel" aria-labelledby="login-tab">
                <div class="login-form">
                    <form id="login-form" method="POST" action="/login" name="login-form" class="needs-validation" novalidate>
                        <div class="form-floating mb-3">
                            <input type="email" class="form-control form-control_gray" name="email" id="email" required>
                            <label for="email">Email *</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="password" class="form-control form-control_gray" name="password" id="password" required>
                            <label for="password">Mật khẩu *</label>
                        </div>
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <a href="/forgot-password" class="btn-text">Quên mật khẩu?</a>
                        </div>
                        <button class="btn btn-primary w-100 text-uppercase" type="submit">Đăng nhập</button>
                        <div class="customer-option mt-4 text-center">
                            <span class="text-secondary">Chưa có tài khoản?</span>
                            <a href="/register" class="btn-text ms-2">Đăng ký</a>
                        </div>
                    </form>
                    <div class="mt-4 text-center">
                        <a href="/google-login" class="btn btn-outline-secondary w-100">
                            <i class="fab fa-google me-2"></i>Đăng nhập bằng Google
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('login-form');
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
                        text: 'Đã xảy ra lỗi khi đăng nhập!',
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