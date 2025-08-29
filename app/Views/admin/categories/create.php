<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm danh mục</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Inter', sans-serif;
            padding-top: 70px;
        }

        .nav-link {
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .nav-link:hover {
            background-color: #495057;
            color: #fff !important;
        }

        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 25px rgba(0, 0, 0, 0.1);
        }

        .form-control {
            border-radius: 8px;
        }

        .btn {
            transition: transform 0.2s ease, background-color 0.2s ease;
        }

        .btn:hover {
            transform: scale(1.05);
        }

        .form-label {
            font-weight: 500;
        }
    </style>
</head>

<body>
    <?php include __DIR__ . '/../layouts/header.php'; ?>
    <main class="container mt-4">
        <h2 class="fw-bold text-dark mb-4">Thêm danh mục mới</h2>
        <?php if ($error = \App\Helpers\Session::get('error')): ?>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Lỗi',
                        text: '<?php echo htmlspecialchars($error); ?>',
                        confirmButtonText: 'OK'
                    });
                });
            </script>
            <?php \App\Helpers\Session::unset('error'); ?>
        <?php endif; ?>
        <div class="card">
            <div class="card-body p-4">
                <form action="/admin/categories/create" method="POST">
                    <div class="mb-3">
                        <label for="name" class="form-label">Tên danh mục</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Thêm danh mục</button>
                        <a href="/admin/categories" class="btn btn-secondary">Hủy</a>
                    </div>
                </form>
            </div>
        </div>
    </main>
    <?php include __DIR__ . '/../layouts/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>

</html>