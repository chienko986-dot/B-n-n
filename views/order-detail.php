<?php
/**
 * VIEW: CHI TIET DON HANG CUA KHACH HANG
 * - Thong tin giao hang, cac mon da dat, tong tien
 * - Chi xem (khong huy don trong phan nay)
 *
 * Du lieu: $don (row DONHANG + join), $chiTiet = OrderModel::getItems()
 */

$flash = get_flash();

$trangThai = [
    'cho_xac_nhan' => ['Đã tiếp nhận', 'bi-clock-history', 'warning'],
    'dang_giao'    => ['Đang giao hàng', 'bi-truck', 'info'],
    'hoan_thanh'   => ['Hoàn thành', 'bi-check-circle-fill', 'success'],
    'huy'          => ['Đã hủy', 'bi-x-circle-fill', 'danger'],
];
$status = $trangThai[$don['trang_thai']] ?? $trangThai['cho_xac_nhan'];
$tong   = (float) $don['tong_thanh_toan'];

$tenKhach = $don['ho_ten'] ?? ($don['ten_khach_hang'] ?? '');
$sdt      = $don['so_dien_thoai'] ?? ($don['sdt_khach_hang'] ?? '');
$email    = $don['email'] ?? ($don['email_khach_hang'] ?? '');
$diaChi   = $don['dia_chi_giao'] ?? ($don['dia_chi_giao_hang'] ?? '');
$maDon    = $don['ma_don'] ?? ('DH' . $don['id']);
?>

<section class="section shop-page">
    <div class="container container-narrow">

        <div class="shop-head">
            <div>
                <span class="eyebrow">Đơn hàng</span>
                <h2 class="section-title">Chi tiết đơn <?= htmlspecialchars($maDon, ENT_QUOTES, 'UTF-8') ?></h2>
            </div>
            <a href="<?= BASE_URL ?>?action=orders" class="section-link">
                <i class="bi bi-arrow-left"></i> Lịch sử đơn hàng
            </a>
        </div>

        <?php if ($flash): ?>
            <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : 'success' ?> shop-alert" role="alert">
                <i class="bi <?= $flash['type'] === 'error' ? 'bi-exclamation-circle-fill' : 'bi-check-circle-fill' ?>"></i>
                <?= htmlspecialchars($flash['message'], ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <!-- Thong tin don -->
        <div class="success-hero">
            <span class="success-icon"><i class="bi <?= $status[1] ?>"></i></span>
            <div class="success-order-code">
                <span>Mã đơn hàng</span>
                <strong><?= htmlspecialchars($maDon, ENT_QUOTES, 'UTF-8') ?></strong>
            </div>
            <span class="status-badge status-<?= $status[2] ?>">
                <i class="bi <?= $status[1] ?>"></i> <?= $status[0] ?>
            </span>
            <p class="mt-2 mb-0">Đặt lúc <?= date('d/m/Y H:i', strtotime($don['ngay_dat'])) ?></p>
        </div>

        <div class="success-layout">

            <!-- Thong tin giao hang -->
            <div class="checkout-card">
                <h3 class="checkout-card-title">
                    <i class="bi bi-person-lines-fill"></i> Thông tin giao hàng
                </h3>
                <ul class="info-list">
                    <li>
                        <i class="bi bi-person-fill"></i>
                        <div><small>Họ và tên</small><strong><?= htmlspecialchars($tenKhach, ENT_QUOTES, 'UTF-8') ?></strong></div>
                    </li>
                    <li>
                        <i class="bi bi-telephone-fill"></i>
                        <div><small>Số điện thoại</small><strong><?= htmlspecialchars($sdt, ENT_QUOTES, 'UTF-8') ?></strong></div>
                    </li>
                    <?php if (!empty($email)): ?>
                        <li>
                            <i class="bi bi-envelope-fill"></i>
                            <div><small>Email</small><strong><?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?></strong></div>
                        </li>
                    <?php endif; ?>
                    <li>
                        <i class="bi bi-geo-alt-fill"></i>
                        <div><small>Địa chỉ giao hàng</small><strong><?= htmlspecialchars($diaChi, ENT_QUOTES, 'UTF-8') ?></strong></div>
                    </li>
                    <?php if (!empty($don['ghi_chu'])): ?>
                        <li>
                            <i class="bi bi-sticky"></i>
                            <div><small>Ghi chú</small><strong><?= htmlspecialchars($don['ghi_chu'], ENT_QUOTES, 'UTF-8') ?></strong></div>
                        </li>
                    <?php endif; ?>
                    <?php if (!empty($don['phuong_thuc'])): ?>
                        <li>
                            <i class="bi <?= $don['phuong_thuc'] === 'chuyen_khoan' ? 'bi-bank' : 'bi-cash-coin' ?>"></i>
                            <div>
                                <small>Thanh toán</small>
                                <strong><?= $don['phuong_thuc'] === 'chuyen_khoan' ? 'Chuyển khoản ngân hàng' : 'Tiền mặt khi nhận hàng (COD)' ?></strong>
                            </div>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>

            <!-- Cac mon da dat -->
            <div class="checkout-card">
                <h3 class="checkout-card-title">
                    <i class="bi bi-bag-check-fill"></i> Các món đã đặt
                </h3>

                <ul class="order-lines">
                    <?php foreach ($chiTiet as $mon): ?>
                        <li class="order-line">
                            <span class="order-line-qty"><?= (int) $mon['so_luong'] ?>×</span>
                            <span class="order-line-name">
                                <?= htmlspecialchars($mon['ten_mon'], ENT_QUOTES, 'UTF-8') ?>
                                <?php if (!empty($mon['size_ten']) || !empty($mon['topping_list']) || !empty($mon['ghi_chu'])): ?>
                                    <small class="order-line-opts">
                                        <?php if (!empty($mon['size_ten'])): ?>
                                            <i class="bi bi-rulers"></i> <?= htmlspecialchars($mon['size_ten'], ENT_QUOTES, 'UTF-8') ?>
                                        <?php endif; ?>
                                        <?php if (!empty($mon['topping_list'])): ?>
                                            <i class="bi bi-plus-circle"></i> <?= htmlspecialchars($mon['topping_list'], ENT_QUOTES, 'UTF-8') ?>
                                        <?php endif; ?>
                                        <?php if (!empty($mon['ghi_chu'])): ?>
                                            <i class="bi bi-sticky"></i> <?= htmlspecialchars($mon['ghi_chu'], ENT_QUOTES, 'UTF-8') ?>
                                        <?php endif; ?>
                                    </small>
                                <?php endif; ?>
                                <small><?= format_price($mon['don_gia']) ?></small>
                            </span>
                            <span class="order-line-total"><?= format_price($mon['thanh_tien']) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <ul class="summary-list">
                    <li>
                        <span>Tổng tiền hàng</span>
                        <strong><?= format_price($don['tong_tien']) ?></strong>
                    </li>
                    <li>
                        <span>Phí vận chuyển</span>
                        <strong>
                            <?= (float) $don['phi_giao_hang'] > 0 ? format_price($don['phi_giao_hang']) : 'Miễn phí' ?>
                        </strong>
                    </li>
                </ul>

                <div class="summary-total">
                    <span>Tổng thanh toán</span>
                    <strong><?= format_price($tong) ?></strong>
                </div>
            </div>
        </div>

        <div class="success-actions">
            <a href="<?= BASE_URL ?>?action=orders" class="btn btn-brand btn-lg">
                <i class="bi bi-receipt"></i> Lịch sử đơn hàng
            </a>
            <a href="<?= BASE_URL ?>#thuc-don" class="btn btn-ghost btn-lg">
                <i class="bi bi-bowl-hot"></i> Đặt món thêm
            </a>
        </div>

    </div>
</section>
