<?php
/**
 * VIEW: LICH SU DON HANG CUA KHACH HANG
 * - Danh sach don da dat (ma don, ngay, tong tien, trang thai)
 * - Nut xem chi tiet tung don
 *
 * Du lieu: $dsDon = OrderModel::getByUser($userId)
 */

$flash = get_flash();

$trangThai = [
    'cho_xac_nhan' => ['Đã tiếp nhận', 'bi-clock-history', 'warning'],
    'dang_giao'    => ['Đang giao hàng', 'bi-truck', 'info'],
    'hoan_thanh'   => ['Hoàn thành', 'bi-check-circle-fill', 'success'],
    'huy'          => ['Đã hủy', 'bi-x-circle-fill', 'danger'],
];
?>

<section class="section shop-page">
    <div class="container container-narrow">

        <div class="shop-head">
            <div>
                <span class="eyebrow">Tài khoản</span>
                <h2 class="section-title">Đơn hàng của tôi</h2>
            </div>
            <a href="<?= BASE_URL ?>?action=account" class="section-link">
                <i class="bi bi-person-circle"></i> Tài khoản
            </a>
        </div>

        <?php if ($flash): ?>
            <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : 'success' ?> shop-alert" role="alert">
                <i class="bi <?= $flash['type'] === 'error' ? 'bi-exclamation-circle-fill' : 'bi-check-circle-fill' ?>"></i>
                <?= htmlspecialchars($flash['message'], ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <?php if (empty($dsDon)): ?>
            <div class="cart-empty">
                <span class="cart-empty-icon"><i class="bi bi-receipt"></i></span>
                <h3>Chưa có đơn hàng nào</h3>
                <p>Bạn chưa đặt đơn nào. Vào thực đơn và chọn ngon món mình thích nhé!</p>
                <a href="<?= BASE_URL ?>#thuc-don" class="btn btn-brand btn-lg">
                    <i class="bi bi-bowl-hot"></i> Đặt món ngay
                </a>
            </div>

        <?php else: ?>
            <div class="order-history">
                <?php foreach ($dsDon as $don):
                    $status = $trangThai[$don['trang_thai']] ?? $trangThai['cho_xac_nhan'];
                    $tong   = (float) $don['tong_tien'] + (float) $don['phi_giao_hang'];
                ?>
                    <article class="order-card">
                        <div class="order-card-head">
                            <div>
                                <strong class="order-code">
                                    <i class="bi bi-receipt-cutoff"></i>
                                    <?= htmlspecialchars($don['ma_don'], ENT_QUOTES, 'UTF-8') ?>
                                </strong>
                                <span class="order-date">
                                    <i class="bi bi-calendar3"></i>
                                    <?= date('d/m/Y H:i', strtotime($don['ngay_dat'])) ?>
                                </span>
                            </div>
                            <span class="status-badge status-<?= $status[2] ?>">
                                <i class="bi <?= $status[1] ?>"></i> <?= $status[0] ?>
                            </span>
                        </div>

                        <div class="order-card-body">
                            <div class="order-meta">
                                <span><i class="bi bi-bag"></i> <?= (int) $don['so_mon'] ?> món / <?= (int) $don['so_luong_tong'] ?> phần</span>
                                <?php if (!empty($don['phuong_thuc'])): ?>
                                    <span>
                                        <i class="bi <?= $don['phuong_thuc'] === 'chuyen_khoan' ? 'bi-bank' : 'bi-cash-coin' ?>"></i>
                                        <?= $don['phuong_thuc'] === 'chuyen_khoan' ? 'Chuyển khoản' : 'COD' ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div class="order-total">
                                <span>Tổng thanh toán</span>
                                <strong><?= format_price($tong) ?></strong>
                            </div>
                        </div>

                        <div class="order-card-foot">
                            <a href="<?= BASE_URL ?>?action=order-detail&id=<?= (int) $don['id'] ?>" class="btn btn-brand btn-sm">
                                <i class="bi bi-eye"></i> Xem chi tiết
                            </a>
                            <?php if ($don['trang_thai'] === 'hoan_thanh'): ?>
                                <span class="order-done-note"><i class="bi bi-check-circle"></i> Cảm ơn bạn đã ủng hộ!</span>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</section>
