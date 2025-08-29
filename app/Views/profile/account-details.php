<?php

use App\Helpers\Session;

Session::start();

// Kiểm tra đăng nhập
if (!Session::get('user')) {
    Session::set('error', 'Vui lòng đăng nhập để xem chi tiết tài khoản!');
    header('Location: /login');
    exit;
}

// Xử lý thông báo
$error = Session::get('error');
$success = Session::get('success');
if ($error) {
    Session::unset('error');
}
if ($success) {
    Session::unset('success');
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết tài khoản - <?php echo htmlspecialchars(Session::get('user')['username'] ?? 'Không xác định'); ?></title>
    <style>
        .account-nav {
            list-style: none;
            padding: 0;
        }

        .account-nav__link {
            display: block;
            padding: 10px 0;
            color: #333;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }

        .account-nav__link:hover,
        .account-nav__link.active {
            color: #007bff;
        }

        .card {
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            font-weight: 500;
            color: #333;
            margin-bottom: 0.5rem;
            display: block;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }

        .form-group input:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.3);
        }

        .avatar-preview {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 50%;
            border: 2px solid #ddd;
        }

        .btn-primary {
            background-color: #007bff;
            color: white;
            padding: 8px 16px;
            border-radius: 5px;
            text-decoration: none;
            transition: background-color 0.2s;
        }

        .btn-primary:hover {
            background-color: #0056b3;
        }

        .btn-secondary {
            background-color: #6c757d;
            color: white;
            padding: 8px 16px;
            border-radius: 5px;
            text-decoration: none;
            transition: background-color 0.2s;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
        }
    </style>
</head>

<body>
    <?php require_once __DIR__ . '/../layouts/header.php'; ?>
    <?php require_once __DIR__ . '/../products/linkcss.php'; ?>

    <main class="pt-90">
        <div class="mb-4 pb-4"></div>
        <section class="my-account container">
            <div class="row">
                <!-- Sidebar Menu -->
                <div class="col-lg-3">
                    <ul class="account-nav">
                        <li><a href="/profile" class="account-nav__link">Tổng quan</a></li>
                        <li><a href="/profile/orders" class="account-nav__link">Đơn hàng</a></li>
                        <li><a href="/profile/products" class="account-nav__link">Sản phẩm</a></li>
                        <li><a href="/profile/account-details" class="account-nav__link active">Chi tiết tài khoản</a></li>
                        <li><a href="/logout" class="account-nav__link">Đăng xuất</a></li>
                    </ul>
                </div>

                <!-- Nội dung chính -->
                <div class="col-lg-9 " style="position: relative; padding-top:40px">
                    <div class="card">
                        <div class="card-body">
                            <h2 class="mb-4">Cập nhật thông tin cá nhân</h2>
                            <form action="/profile/account-details" method="POST" enctype="multipart/form-data">
                                <div class="form-group">
                                    <label for="username">Tên người dùng</label>
                                    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars(Session::get('user')['username']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars(Session::get('user')['email']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Ảnh đại diện</label>
                                    <div class="relative d-inline-block">
                                        <img id="avatar-preview" src="<?php echo htmlspecialchars(Session::get('user')['images'] ? '/Uploads/' . Session::get('user')['images'] : '/assets/images/user/avatar-default.jpg') . '?t=' . time(); ?>" alt="Avatar" class="avatar-preview">
                                        <label for="image" class="absolute bottom-0 right-0 bg-dark text-white rounded-circle p-2 cursor-pointer">
                                            <i class="bi bi-camera"></i>
                                        </label>
                                        <input type="file" id="image" name="image" accept="image/*" hidden>
                                    </div>
                                    <small class="d-block mt-2 text-muted">Chỉ chấp nhận file JPG, JPEG, PNG, GIF.</small>
                                </div>
                                <div class="d-flex gap-3">
                                    <button type="submit" class="btn btn-primary">Cập nhật</button>
                                    <a href="/profile/change-password" class="btn btn-secondary">Đổi mật khẩu</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Tích hợp SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="/assets/js/plugins/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/plugins/bootstrap-slider.min.js"></script>
    <script src="/assets/js/plugins/swiper.min.js"></script>
    <script>
        // Tránh tải trùng lặp countdown.js và theme.js
        if (!window.countdownLoaded) {
            document.write('<script src="/assets/js/plugins/countdown.js"><\/script>');
            window.countdownLoaded = true;
        }
        if (!window.themeLoaded) {
            document.write('<script src="/assets/js/theme.js"><\/script>');
            window.themeLoaded = true;
        }

        // Xem trước ảnh đại diện
        $(document).ready(function() {
            $('#image').on('change', function(event) {
                const file = event.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        $('#avatar-preview').attr('src', e.target.result);
                    };
                    reader.readAsDataURL(file);
                }
            });

            // Hiển thị thông báo từ Session
            <?php if ($error): ?>
                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi',
                    text: '<?php echo htmlspecialchars($error); ?>',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#d33'
                });
            <?php endif; ?>
            <?php if ($success): ?>
                Swal.fire({
                    icon: 'success',
                    title: 'Thành công',
                    text: '<?php echo htmlspecialchars($success); ?>',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#3085d6',
                    timer: 2000,
                    timerProgressBar: true
                });
            <?php endif; ?>
        });
    </script>

    <?php require_once __DIR__ . '/../layouts/footer.php'; ?>
</body>

</html>