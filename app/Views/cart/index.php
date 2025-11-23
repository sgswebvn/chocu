<?php
namespace App\Helpers;
use App\Helpers\Session;

$error   = Session::get('error');
$success = Session::get('success');
if ($error)   Session::unset('error');
if ($success) Session::unset('success');

include __DIR__ . '/../layouts/header.php';
?>

<main class="pt-90">
    <div class="mb-4 pb-4"></div>
    <section class="cart container">
        <h1 class="mb-5">Giỏ hàng</h1>
        <div class="row">
            <div class="col-12">

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>

                <?php if (empty($cartItems)): ?>
                    <p class="text-muted">Giỏ hàng của bạn đang trống.</p>
                    <a href="/" class="btn btn-outline-primary mt-3">Tiếp tục mua sắm</a>
                <?php else: ?>
                    <form method="GET" action="/checkout" id="checkoutForm">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover align-middle" id="cart-table">
                                <thead class="table-light">
                                    <tr>
                                        <th width="60">
                                            <input type="checkbox" id="select-all" class="form-check-input">
                                        </th>
                                        <th>Sản phẩm</th>
                                        <th>Giá</th>
                                        <th>Số lượng</th>
                                        <th>Tổng</th>
                                        <th width="80">Hành động</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cartItems as $item):
                                        $itemTotal = $item['price'] * $item['quantity'];
                                        $itemTotalWithVat = $itemTotal * 1.1;
                                    ?>
                                        <tr class="cart-row" data-product-id="<?= $item['product_id'] ?>">
                                            <td class="text-center">
                                                <input type="checkbox" 
                                                       name="selected_items[]" 
                                                       value="<?= $item['product_id'] ?>" 
                                                       class="form-check-input item-checkbox"
                                                       data-price="<?= $itemTotalWithVat ?>">
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="/Uploads/<?= htmlspecialchars($item['image']) ?>" 
                                                         alt="<?= htmlspecialchars($item['title']) ?>" 
                                                         style="width: 50px; height: 50px; object-fit: cover; border-radius: 6px; margin-right: 12px;">
                                                    <div>
                                                        <strong><?= htmlspecialchars($item['title']) ?></strong>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?= number_format($item['price']) ?> ₫</td>
                                            <td class="text-center fw-bold"><?= $item['quantity'] ?></td>
                                            <td class="fw-bold text-danger"><?= number_format($itemTotal) ?> ₫</td>
                                            <td class="text-center">
                                                <a href="javascript:void(0)" 
                                                   class="text-danger js-remove-cart" 
                                                   data-id="<?= $item['product_id'] ?>" 
                                                   data-price="<?= $itemTotalWithVat ?>"
                                                   title="Xóa">
                                                    <i class="bi bi-trash3-fill"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Tổng tiền động -->
                        <div class="bg-light rounded p-4 mt-4 shadow-sm">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="mb-3">Tổng kết đơn hàng</h5>
                                    <p class="mb-1"><strong>Tổng phụ:</strong> <span id="subtotal">0</span> ₫</p>
                                    <p class="mb-1"><strong>VAT (10%):</strong> <span id="vat">0</span> ₫</p>
                                    <p class="fs-4 fw-bold text-primary mb-0">
                                        <strong>Tổng cộng:</strong> <span id="total">0</span> ₫
                                    </p>
                                    <small class="text-muted">Đã chọn: <span id="selected-count">0</span> sản phẩm</small>
                                </div>
                                <div>
                                    <button type="submit" class="btn btn-primary btn-lg px-5" id="checkout-btn" disabled>
                                        Thanh toán ngay
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </section>
</main>

<style>
    #checkout-btn:disabled { opacity: 0.6; cursor: not-allowed; }
    .form-check-input { transform: scale(1.3); cursor: pointer; }
    .cart-row { transition: all 0.3s; }
    .cart-row.removing { opacity: 0; transform: translateX(-20px); }
</style>

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('.item-checkbox');
    const selectAll = document.getElementById('select-all');
    const subtotalEl = document.getElementById('subtotal');
    const vatEl = document.getElementById('vat');
    const totalEl = document.getElementById('total');
    const countEl = document.getElementById('selected-count');
    const checkoutBtn = document.getElementById('checkout-btn');

    // Hàm tính lại tổng tiền
    function updateTotal() {
        let subtotal = 0;
        checkboxes.forEach(cb => {
            if (cb.checked && cb.closest('.cart-row')) {
                subtotal += parseFloat(cb.dataset.price);
            }
        });

        const vat = subtotal * 0.1;
        const total = subtotal;
        const checkedCount = document.querySelectorAll('.item-checkbox:checked').length;

        subtotalEl.textContent = Math.round(subtotal / 1.1).toLocaleString();
        vatEl.textContent = Math.round(vat).toLocaleString();
        totalEl.textContent = Math.round(total).toLocaleString();
        countEl.textContent = checkedCount;

        checkoutBtn.disabled = checkedCount === 0;
        checkoutBtn.innerHTML = checkedCount > 0 
            ? `Thanh toán ngay (${checkedCount} sản phẩm) <i class="bi bi-arrow-right ms-2"></i>`
            : 'Chưa chọn sản phẩm';
    }

    // Chọn tất cả
    selectAll.addEventListener('change', function() {
        document.querySelectorAll('.item-checkbox').forEach(cb => {
            if (cb.closest('.cart-row')) cb.checked = this.checked;
        });
        updateTotal();
    });

    // Tick từng món
    document.querySelectorAll('.item-checkbox').forEach(cb => {
        cb.addEventListener('change', updateTotal);
    });

    // XÓA SẢN PHẨM KHÔNG CẦN RELOAD
    document.querySelectorAll('.js-remove-cart').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const productId = this.dataset.id;
            const price = parseFloat(this.dataset.price);
            const row = this.closest('.cart-row');
            const checkbox = row.querySelector('.item-checkbox');

            Swal.fire({
                icon: 'warning',
                title: 'Xóa sản phẩm?',
                text: 'Bạn có chắc muốn xóa khỏi giỏ hàng?',
                showCancelButton: true,
                confirmButtonText: 'Xóa',
                cancelButtonText: 'Hủy',
                confirmButtonColor: '#d33'
            }).then(result => {
                if (result.isConfirmed) {
                    fetch('/cart/remove/' + productId, {
                        method: 'POST',
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            // Xóa dòng mượt mà
                            row.classList.add('removing');
                            setTimeout(() => {
                                row.remove();
                                // Bỏ tick nếu đang chọn
                                if (checkbox.checked) {
                                    checkbox.checked = false;
                                }
                                updateTotal();

                                // Nếu giỏ trống → reload để hiện thông báo trống
                                if (document.querySelectorAll('.cart-row').length === 0) {
                                    location.reload();
                                }
                            }, 300);

                            Swal.fire({
                                icon: 'success',
                                title: 'Đã xóa!',
                                text: data.message,
                                timer: 1200,
                                timerProgressBar: true,
                                showConfirmButton: false
                            });
                        } else {
                            Swal.fire('Lỗi', data.message, 'error');
                        }
                    })
                    .catch(() => {
                        Swal.fire('Lỗi', 'Không thể kết nối server!', 'error');
                    });
                }
            });
        });
    });

    // Khởi động
    updateTotal();
});
</script>
<br>
<?php include __DIR__ . '/../layouts/footer.php'; ?>