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
    <section class="container">
        <h1>Gói Nâng Cấp Đối Tác</h1>
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Gói Đối Tác - 2,000 VNĐ</h5>
                <p class="card-text">Trở thành đối tác chính thức để quản lý sản phẩm và đơn hàng của bạn!</p>
                <form id="upgrade-form" method="POST" action="/upgrade/process" class="needs-validation" novalidate>
                    <button class="btn btn-primary w-100 text-uppercase" type="submit">Thanh Toán Ngay</button>
                </form>
            </div>
        </div>
    </section>
</main>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('upgrade-form');
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
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Phản hồi từ server không hợp lệ: ' + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    Swal.fire({
                        icon: data.success ? 'success' : 'error',
                        title: data.success ? 'Thành công' : 'Lỗi',
                        text: data.message || 'Đã xảy ra lỗi không xác định. Vui lòng thử lại!',
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
                    console.error('Fetch error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Lỗi',
                        text: 'Đã xảy ra lỗi khi thanh toán: ' + error.message,
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