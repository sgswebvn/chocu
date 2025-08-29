<?php

namespace App\Helpers;

use App\Helpers\Session;

include __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/slide.php';
?>

<!-- Hiển thị thông báo -->
<?php if ($success = Session::get('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($success) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php Session::unset('success'); ?>
<?php endif; ?>
<?php if ($error = Session::get('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php Session::unset('error'); ?>
<?php endif; ?>

<!-- Hero Section -->
<div class="mb-3 mb-xl-5 pt-1 pb-4"></div>

<section class="hero-section text-white text-center">
</section>
<section class="products-grid container">
    <h2 class="section-title text-center mb-3 pb-xl-3 mb-xl-4">Sản phẩm mới nhất</h2>
    <div class="row">
        <?php foreach ($products as $product): ?>
            <div class="col-6 col-md-4 col-lg-3">
                <div class="product-card product-card_style3 mb-3 mb-md-4 mb-xxl-5">
                    <div class="pc__img-wrapper">
                        <a href="/products/<?= $product['id'] ?>">
                            <?php
                            $imagePath = $product['is_partner_paid'] == 1
                                ? '/uploads/partners/' . $product['image']
                                : (!empty($product['image']) ? '/Uploads/' . $product['image'] : '/assets/images/default-product.jpg');
                            ?>
                            <img loading="lazy"
                                src="<?= htmlspecialchars($imagePath) ?>"
                                width="330" height="400"
                                alt="<?= htmlspecialchars($product['title']) ?>" class="pc__img">

                        </a>
                    </div>
                    <div class="pc__info position-relative">
                        <h6 class="pc__title">
                            <a href="/products/<?= $product['id'] ?>"><?= htmlspecialchars($product['title']) ?></a>
                        </h6>
                        <div class="product-card__price d-flex align-items-center">
                            <span class="money price text-secondary">
                                <?= number_format($product['price'], 0, ',', '.') ?> VNĐ
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div><!-- /.row -->

    <div class="text-center mt-2">
        <a class="btn-link btn-link_lg default-underline text-uppercase fw-medium" href="/products">Xem thêm</a>
    </div>

    <div class="mb-3 mb-xl-5 pt-1 pb-4"></div>

    <section class="hot-deals container">
        <h2 class="section-title text-center mb-3 pb-xl-3 mb-xl-4">Sản phẩm được săn đón</h2>
        <div class="row">
            <div class="col-md-6 col-lg-4 col-xl-20per d-flex align-items-center flex-column justify-content-center py-4 align-items-md-start">
                <h2>Sản phẩm được săn đón</h2>
                <h2 class="fw-bold">Mua ngay</h2>
                <a href="/products" class="btn-link default-underline text-uppercase fw-medium mt-3">Xem tất cả</a>
            </div>
            <div class="col-md-6 col-lg-8 col-xl-80per">
                <div class="position-relative">
                    <div class="swiper-container js-swiper-slider" data-settings='{
                  "autoplay": {
                    "delay": 5000
                  },
                  "slidesPerView": 4,
                  "slidesPerGroup": 4,
                  "effect": "none",
                  "loop": false,
                  "breakpoints": {
                    "320": {
                      "slidesPerView": 2,
                      "slidesPerGroup": 2,
                      "spaceBetween": 14
                    },
                    "768": {
                      "slidesPerView": 2,
                      "slidesPerGroup": 3,
                      "spaceBetween": 24
                    },
                    "992": {
                      "slidesPerView": 3,
                      "slidesPerGroup": 1,
                      "spaceBetween": 30,
                      "pagination": false
                    },
                    "1200": {
                      "slidesPerView": 4,
                      "slidesPerGroup": 1,
                      "spaceBetween": 30,
                      "pagination": false
                    }
                  }
                }'>
                        <div class="swiper-wrapper">
                            <?php foreach ($hotDeals as $product): ?>
                                <div class="swiper-slide product-card product-card_style3">
                                    <div class="pc__img-wrapper">
                                        <a href="/products/<?= $product['id'] ?>">
                                            <?php
                                            $imagePath = $product['is_partner_paid'] == 1
                                                ? '/uploads/partners/' . $product['image']
                                                : (!empty($product['image']) ? '/Uploads/' . $product['image'] : '/assets/images/default-product.jpg');
                                            ?>
                                            <img loading="lazy"
                                                src="<?= htmlspecialchars($imagePath) ?>"
                                                width="330" height="400"
                                                alt="<?= htmlspecialchars($product['title']) ?>" class="pc__img">

                                        </a>
                                    </div>
                                    <div class="pc__info position-relative">
                                        <h6 class="pc__title">
                                            <a href="/products/<?= $product['id'] ?>"><?= htmlspecialchars($product['title']) ?></a>
                                        </h6>
                                        <div class="product-card__price d-flex">
                                            <span class="money price text-secondary">
                                                <?= number_format($product['price'], 0, ',', '.') ?> VNĐ
                                                <p><strong>Lượt xem:</strong> <?php echo htmlspecialchars($product['views'] ?? 0); ?></p>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div><!-- /.swiper-wrapper -->
                    </div><!-- /.swiper-container js-swiper-slider -->
                </div><!-- /.position-relative -->
            </div>
        </div>
    </section>
</section>
<!-- Hot Deals -->
<br>
<hr>
<section class="products-grid container">
    <h2 class="section-title text-center mb-3 pb-xl-3 mb-xl-4">Sản phẩm khác</h2>
    <div class="row">
        <?php foreach ($hotDeals as $product): ?>
            <div class="col-6 col-md-4 col-lg-3">
                <div class="product-card product-card_style3 mb-3 mb-md-4 mb-xxl-5">
                    <div class="pc__img-wrapper">
                        <a href="/products/<?= $product['id'] ?>">
                            <?php
                            $imagePath = $product['is_partner_paid'] == 1
                                ? '/uploads/partners/' . $product['image']
                                : (!empty($product['image']) ? '/Uploads/' . $product['image'] : '/assets/images/default-product.jpg');
                            ?>
                            <img loading="lazy"
                                src="<?= htmlspecialchars($imagePath) ?>"
                                width="330" height="400"
                                alt="<?= htmlspecialchars($product['title']) ?>" class="pc__img">

                        </a>
                    </div>
                    <div class="pc__info position-relative">
                        <h6 class="pc__title">
                            <a href="/products/<?= $product['id'] ?>"><?= htmlspecialchars($product['title']) ?></a>
                        </h6>
                        <div class="product-card__price d-flex align-items-center">
                            <span class="money price text-secondary">
                                <?= number_format($product['price'], 0, ',', '.') ?> VNĐ
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div><!-- /.row -->

    <div class="text-center mt-2">
        <a class="btn-link btn-link_lg default-underline text-uppercase fw-medium" href="/products">Xem thêm</a>
    </div>
</section>

<div class="mb-3 mb-xl-5 pt-1 pb-4"></div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
<style>
    .pc__img-wrapper {
        overflow: hidden;
        position: relative;
        border-radius: 8px;
    }

    .pc__img {
        transition: transform 0.4s ease;
        will-change: transform;
    }

    .pc__img-wrapper:hover .pc__img {
        transform: scale(1.07);
    }
</style>
<!-- JavaScript -->
<script src="/assets/js/plugins/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>