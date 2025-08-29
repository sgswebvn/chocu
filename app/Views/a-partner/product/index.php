<?php
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';
?>

<header class="pc-header">
    <div class="header-wrapper">
        <div class="me-auto pc-mob-drp">
            <ul class="list-unstyled">
                <li class="pc-h-item pc-sidebar-collapse">
                    <a href="#" class="pc-head-link ms-0" id="sidebar-hide">
                        <i class="ti ti-menu-2"></i>
                    </a>
                </li>
                <li class="pc-h-item pc-sidebar-popup">
                    <a href="#" class="pc-head-link ms-0" id="mobile-collapse">
                        <i class="ti ti-menu-2"></i>
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
                        <div class="page-header-title">
                            <h5 class="m-b-10">Quản lý sản phẩm</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-3">
                            <h5>Danh sách sản phẩm</h5>
                            <a href="/partners/product/create" class="btn btn-primary">Thêm sản phẩm</a>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover table-borderless mb-0">
                                <thead>
                                    <tr>
                                        <th>TÊN SẢN PHẨM</th>
                                        <th>DANH MỤC</th>
                                        <th>GIÁ</th>
                                        <th>MÔ TẢ</th>
                                        <th>ẢNH</th>
                                        <th>HÀNH ĐỘNG</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($products as $product): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($product['title']); ?></td>
                                            <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                                            <td><?php echo number_format($product['price'], 0, ',', '.'); ?> VNĐ</td>
                                            <td><?php echo htmlspecialchars($product['description']); ?></td>
                                            <td>
                                                <img src="<?php echo htmlspecialchars($product['image']
                                                                ? '/uploads/partners/' . $product['image']
                                                                : '/assets/images/default-product.jpg'); ?>"
                                                    alt="product" width="50">
                                            </td>

                                            <td>
                                                <a href="/partners/product/edit/<?php echo $product['id']; ?>" class="btn btn-sm btn-primary">Sửa</a>
                                                <a href="/partners/product/delete/<?php echo $product['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Bạn có chắc muốn xóa sản phẩm này?')">Xóa</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../layouts/footer.php';
?>