<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<?php require_once __DIR__ . '/../layouts/navbar.php'; ?>

<?php
use App\Helpers\Session;
use App\Config\Database;

// === LẤY USER HIỆN TẠI ===
$user = Session::get('user');
if (!$user || $user['role'] !== 'partners' || !$user['is_partner_paid']) {
    Session::set('error', 'Bạn cần nâng cấp tài khoản đối tác!');
    header('Location: /upgrade');
    exit;
}

// === LẤY NGÂN HÀNG TRỰC TIẾP TỪ DB – PHP THUẦN 100% ===
try {
    $pdo = (new Database())->getConnection();
    $stmt = $pdo->prepare("SELECT * FROM bank_accounts WHERE user_id = ? AND is_default = 1 LIMIT 1");
    $stmt->execute([$user['id']]);
    $bankAccount = $stmt->fetch(PDO::FETCH_ASSOC); // có hoặc false
} catch (Exception $e) {
    $bankAccount = null;
    error_log("Lỗi lấy ngân hàng: " . $e->getMessage());
}

// === TÍNH AVATAR ===
$avatar = !empty($user['images'])
    ? '/uploads/partners/' . htmlspecialchars($user['images']) . '?t=' . time()
    : '/assets/images/user/avatar-2.jpg';
?>

<header class="pc-header">
    <div class="header-wrapper">
        <div class="me-auto pc-mob-drp">
            <ul class="list-unstyled">
                <li class="pc-h-item pc-sidebar-collapse"><a href="#" class="pc-head-link ms-0" id="sidebar-hide"><i class="ti ti-menu-2"></i></a></li>
                <li class="pc-h-item pc-sidebar-popup"><a href="#" class="pc-head-link ms-0" id="mobile-collapse"><i class="ti ti-menu-2"></i></a></li>
            </ul>
        </div>
        <div class="ms-auto">
            <ul class="list-unstyled">
                <li class="dropdown pc-h-item header-user-profile">
                    <a class="pc-head-link dropdown-toggle arrow-none me-0" data-bs-toggle="dropdown" href="#" role="button">
                        <img src="<?= $avatar ?>" alt="user" class="user-avtar wid-35 rounded-circle">
                        <span><?= htmlspecialchars($user['username']) ?></span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</header>

<div class="pc-container">
    <div class="pc-content">
        <div class="page-header">
            <div class="page-block">
                <div class="row align-items-center">
                    <div class="col-md-12">
                        <div class="page-header-title"><h5 class="m-b-10">Hồ sơ cá nhân</h5></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Cột trái: Thông tin cá nhân -->
            <div class="col-md-12 col-xl-6">
                <div class="card">
                    <div class="card-body">
                        <h6 class="mb-3 fw-bold text-primary">Thông tin cá nhân</h6>
                        <form action="/partners/profile" method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label class="form-label">Tên người dùng</label>
                                <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
                            </div>
                            <div class="mb-4">
                                <label class="form-label d-block">Ảnh đại diện</label>
                                <div class="position-relative d-inline-block">
                                    <img id="avatar-preview" src="<?= $avatar ?>" class="rounded-circle border" style="width:120px;height:120px;object-fit:cover;cursor:pointer;">
                                    <label for="image" class="position-absolute bottom-0 end-0 bg-primary text-white rounded-circle p-2 shadow" style="cursor:pointer;">
                                        <i class="ti ti-camera fs-5"></i>
                                    </label>
                                </div>
                                <input type="file" id="image" name="image" accept="image/*" hidden>
                                <small class="form-text text-muted d-block mt-2">JPG, PNG, GIF. Tối đa 2MB.</small>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">Cập nhật hồ sơ</button>
                                <a href="/store/<?= $user['id'] ?>" class="btn btn-success">Xem gian hàng</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Cột phải: Thông tin ngân hàng -->
            <div class="col-md-12 col-xl-6">
                <div class="card border-primary shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Thông tin nhận tiền rút</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($bankAccount): ?>
                            <div class="alert alert-success d-flex align-items-center mb-3">
                                <i class="ti ti-check fs-5 me-2"></i>
                                <div>Đã liên kết ngân hàng thành công!</div>
                            </div>
                            <div class="text-center bg-light p-4 rounded border mb-3">
                                <img src="<?= htmlspecialchars($bankAccount['logo'] ?? 'https://vietqr.io/assets/banks/default.png') ?>" 
                                     width="80" class="mb-3 rounded shadow">
                                <div class="fw-bold fs-5"><?= htmlspecialchars($bankAccount['bank_name']) ?></div>
                                <div class="text-muted">STK: <strong><?= htmlspecialchars($bankAccount['account_number']) ?></strong></div>
                                <div class="text-muted">Chủ TK: <strong><?= htmlspecialchars($bankAccount['account_holder']) ?></strong></div>
                                <?php if (!empty($bankAccount['branch'])): ?>
                                    <div class="text-muted small mt-1">Chi nhánh: <?= htmlspecialchars($bankAccount['branch']) ?></div>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5 text-muted">
                                <i class="ti ti-building-bank" style="font-size:70px;"></i>
                                <p class="mt-3 text-danger fw-bold fs-4">Chưa liên kết ngân hàng</p>
                                <small class="d-block">Bạn cần liên kết ngân hàng để rút tiền từ ví</small>
                            </div>
                        <?php endif; ?>

                        <div class="text-end">
                            <button type="button" class="btn btn-primary px-5" data-bs-toggle="modal" data-bs-target="#bankModal">
                                <?= $bankAccount ? 'Sửa thông tin' : 'Liên kết ngay' ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal liên kết ngân hàng -->
<div class="modal fade" id="bankModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="/partners/profile/bank" method="POST" id="bankForm">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Liên kết tài khoản ngân hàng</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-4">
                        <label class="form-label fw-bold">Chọn ngân hàng <span class="text-danger">*</span></label>
                        <select name="bank_code" id="bank_select" class="form-select form-select-lg" required>
                            <option value="">-- Đang tải danh sách ngân hàng... --</option>
                        </select>
                        <div id="bank_preview" class="text-center mt-3" style="display:none;">
                            <img id="bank_logo" src="" width="80" class="rounded shadow">
                            <div class="mt-2"><strong id="bank_name_preview" class="text-primary"></strong></div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Số tài khoản <span class="text-danger">*</span></label>
                        <input type="text" name="account_number" id="account_number" class="form-control form-control-lg" 
                               value="<?= $bankAccount['account_number'] ?? '' ?>" placeholder="Nhập số tài khoản" required>
                        <div id="lookup_result" class="mt-2"></div>
                    </div>

                    <input type="hidden" name="bank_name" id="hidden_bank_name">
                    <input type="hidden" name="logo" id="hidden_logo">
                    <input type="hidden" name="bank_short_name" id="bank_short_name_input">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Chủ tài khoản <span class="text-danger">*</span></label>
                            <input type="text" name="account_holder" id="account_holder" class="form-control" 
                                   value="<?= $bankAccount['account_holder'] ?? '' ?>" readonly required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Chi nhánh (không bắt buộc)</label>
                            <input type="text" name="branch" class="form-control" 
                                   value="<?= $bankAccount['branch'] ?? '' ?>" placeholder="VD: Chi nhánh Hà Nội">
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary px-5">Lưu thông tin ngân hàng</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// API MIỄN PHÍ 100%
const BANKS_API = 'https://api.vietqr.io/v2/banks';
const LOOKUP_API = 'https://api.vietqr.io/v2/lookup';
let banks = [];

// Load danh sách ngân hàng khi mở modal
document.getElementById('bankModal').addEventListener('shown.bs.modal', function () {
    if (banks.length === 0) {
        fetch(BANKS_API)
            .then(r => r.json())
            .then(res => {
                if (res.code === '00') {
                    banks = res.data;
                    const select = document.getElementById('bank_select');
                    select.innerHTML = '<option value="">-- Chọn ngân hàng --</option>';
                    banks.forEach(b => {
                        const opt = new Option(`${b.shortName} - ${b.name}`, b.bin);
                        opt.dataset.logo = b.logo;
                        select.add(opt);
                    });

                    // Nếu đang sửa → tự động chọn ngân hàng cũ
                    <?php if ($bankAccount): ?>
                        select.value = '<?= $bankAccount['bank_code'] ?? '' ?>';
                        if (select.value) select.dispatchEvent(new Event('change'));
                        document.getElementById('account_number').dispatchEvent(new Event('input'));
                    <?php endif; ?>
                }
            });
    }
});

// Khi chọn ngân hàng
document.getElementById('bank_select').addEventListener('change', function() {
    const selected = banks.find(b => b.bin === this.value);
    const preview = document.getElementById('bank_preview');
    if (selected) {
        document.getElementById('bank_logo').src = selected.logo;
        document.getElementById('bank_name_preview').textContent = selected.shortName;
        document.getElementById('hidden_bank_name').value = selected.name;
        document.getElementById('hidden_logo').value = selected.logo;
        document.getElementById('bank_short_name_input').value = selected.shortName;
        preview.style.display = 'block';
    } else {
        preview.style.display = 'none';
    }
});

// Tra cứu tên chủ tài khoản
document.getElementById('account_number').addEventListener('input', function() {
    const acc = this.value.replace(/\D/g, '');
    const bin = document.getElementById('bank_select').value;
    const result = document.getElementById('lookup_result');
    result.innerHTML = '';

    if (acc.length < 8 || !bin) return;

    result.innerHTML = '<small class="text-primary">Đang tra cứu...</small>';

    fetch(LOOKUP_API, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ bin: bin, accountNumber: acc })
    })
    .then(r => r.json())
    .then(res => {
        if (res.code === '00' && res.data.accountName) {
            document.getElementById('account_holder').value = res.data.accountName;
            result.innerHTML = `<div class="alert alert-success p-2 small">Chủ tài khoản: <strong>${res.data.accountName}</strong></div>`;
        } else {
            result.innerHTML = '<small class="text-warning">Không tìm được tên (vẫn có thể lưu)</small>';
        }
    })
    .catch(() => {
        result.innerHTML = '<small class="text-danger">Lỗi kết nối, thử lại sau</small>';
    });
});

// Preview avatar
document.getElementById('image')?.addEventListener('change', e => {
    if (e.target.files[0]) {
        document.getElementById('avatar-preview').src = URL.createObjectURL(e.target.files[0]);
    }
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>