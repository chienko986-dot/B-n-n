<?php
/**
 * VIEW: TRANG 404 - KHONG TIM THAY
 */

$flash = get_flash();
?>

<section class="section shop-page">
    <div class="container container-narrow text-center">

        <div class="cart-empty">
            <span class="cart-empty-icon" style="font-size: 2.4rem;">
                <i class="bi bi-emoji-frown"></i>
            </span>
            <h3>404 - Không tìm thấy trang</h3>
            <p>Trang bạn đang tìm không tồn tại hoặc đã bị di chuyển.</p>

            <?php if ($flash): ?>
                <div class="alert alert-info mx-auto mt-3" style="max-width: 480px;" role="alert">
                    <i class="bi bi-info-circle-fill me-1"></i>
                    <?= htmlspecialchars($flash['message'], ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <div class="d-flex gap-2 justify-content-center flex-wrap mt-3">
                <a href="<?= BASE_URL ?>" class="btn btn-brand btn-lg">
                    <i class="bi bi-house-door"></i> Về trang chủ
                </a>
                <a href="<?= BASE_URL ?>#thuc-don" class="btn btn-ghost btn-lg">
                    <i class="bi bi-bowl-hot"></i> Xem thực đơn
                </a>
            </div>
        </div>

    </div>
</section>
