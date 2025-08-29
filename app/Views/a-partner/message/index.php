<?php

use App\Helpers\Session;

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
                            <h5 class="m-b-10">Quản lý tin nhắn</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <?php if ($error = Session::get('error')): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?= htmlspecialchars($error) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                            <?php Session::unset('error'); ?>
                        <?php endif; ?>
                        <?php if ($success = Session::get('success')): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <?= htmlspecialchars($success) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                            <?php Session::unset('success'); ?>
                        <?php endif; ?>
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Người dùng</th>
                                    <th>Sản phẩm</th>
                                    <th>Thời gian tin nhắn gần nhất</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($conversations as $conv): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($conv['other_user_name']) ?></td>
                                        <td>

                                            <div class="d-flex align-items-center">
                                                <?php
                                                $imagePath = ($conv['is_partner_paid'] == 1 ? '/Uploads/partners/' . $conv['product_image'] : '/Uploads/' . $conv['product_image']);
                                                $image = $imagePath ?: '/assets/images/default-product.jpg';
                                                ?>
                                                <img src="<?= htmlspecialchars($image) ?>" alt="<?= htmlspecialchars($conv['product_name']) ?>" width="50" class="me-2">

                                            </div>
                                        </td>
                                        <td><?= date('d/m/Y H:i', strtotime($conv['last_message_time'])) ?></td>
                                        <td>
                                            <a href="/partners/message/<?= $conv['product_id'] ?>/<?= $conv['other_user_id'] ?>" class="btn btn-sm btn-primary">Xem</a>
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

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>