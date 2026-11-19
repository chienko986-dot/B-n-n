<?php
/**
 * VIEW: TRANG DAT HANG THANH CONG
 * - Thong bao thanh cong + ma don hang
 * - Thong tin khach hang
 * - Cac mon da dat, so luong, gia
 * - Tong thanh toan
 * - Nut quay ve trang chu va tiep tuc mua hang
 *
 * Du lieu tu controller: $donHang = ['donhang' => ..., 'chi_tiet' => [...]]
 */

$don     = $donHang['donhang'];
$chiTiet = $donHang['chi_tiet'];
$tong    = (float) $don['tong_thanh_toan'];

// Trang thai don hang bang tieng Viet
$trangThai = [
    'cho_xac_nhan' => ['Đã tiếp nhận', 'bi-clock-history', 'warning'],
    'dang_giao'    => ['Đang giao hàng', 'bi-truck', 'info'],
    'hoan_thanh'   => ['Hoàn thành', 'bi-check-circle-fill', 'success'],
    'huy'          => ['Đã hủy', 'bi-x-circle-fill', 'danger'],
];
$status = $trangThai[$don['trang_thai']] ?? $trangThai['cho_xac_nhan'];
?>

<section class="section shop-page">
    <div class="container container-narrow">

        <!-- ================= BANNER THANH CONG ================= -->
        <div class="success-hero">
            <span class="success-icon"><i class="bi bi-check2-all"></i></span>
            <h1>Đặt hàng thành công!</h1>
            <p>Cảm ơn bạn đã tin tưởng AnLaMe. Đơn hàng của bạn đã được tiếp nhận và đang được chuẩn bị.</p>

            <div class="success-order-code">
                <span>Mã đơn hàng</span>
                <strong><?= htmlspecialchars($don['ma_don'], ENT_QUOTES, 'UTF-8') ?></strong>
            </div>

            <span class="status-badge status-<?= $status[2] ?>">
                <i class="bi <?= $status[1] ?>"></i> <?= $status[0] ?>
            </span>
        </div>

        <div class="success-layout">

            <!-- ================= THONG TIN KHACH HANG ================= -->
            <div class="checkout-card">
                <h3 class="checkout-card-title">
                    <i class="bi bi-person-lines-fill"></i> Thông tin giao hàng
                </h3>

                <ul class="info-list">
                    <li>
                        <i class="bi bi-person-fill"></i>
                        <div>
                            <small>Họ và tên</small>
                            <strong><?= htmlspecialchars($don['ho_ten'], ENT_QUOTES, 'UTF-8') ?></strong>
                        </div>
                    </li>
                    <li>
                        <i class="bi bi-telephone-fill"></i>
                        <div>
                            <small>Số điện thoại</small>
                            <strong><?= htmlspecialchars($don['so_dien_thoai'], ENT_QUOTES, 'UTF-8') ?></strong>
                        </div>
                    </li>
                    <li>
                        <i class="bi bi-envelope-fill"></i>
                        <div>
                            <small>Email</small>
                            <strong><?= htmlspecialchars($don['email'], ENT_QUOTES, 'UTF-8') ?></strong>
                        </div>
                    </li>
                    <li>
                        <i class="bi bi-geo-alt-fill"></i>
                        <div>
                            <small>Địa chỉ giao hàng</small>
                            <strong><?= htmlspecialchars($don['dia_chi_giao'], ENT_QUOTES, 'UTF-8') ?></strong>
                        </div>
                    </li>
                    <li>
                        <i class="bi bi-calendar-event"></i>
                        <div>
                            <small>Thời gian đặt</small>
                            <strong><?= date('d/m/Y H:i', strtotime($don['ngay_dat'])) ?></strong>
                        </div>
                    </li>
                    <?php if (!empty($don['ghi_chu'])): ?>
                        <li>
                            <i class="bi bi-sticky"></i>
                            <div>
                                <small>Ghi chú</small>
                                <strong><?= htmlspecialchars($don['ghi_chu'], ENT_QUOTES, 'UTF-8') ?></strong>
                            </div>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>

            <!-- ================= CAC MON DA DAT ================= -->
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

                <p class="summary-note">
                    <i class="bi bi-cash-coin"></i>
                    Thanh toán khi nhận hàng.
                </p>
            </div>
        </div>

        <!-- ================= CAC NUT HANH DONG ================= -->
        <div class="success-actions">
            <a href="<?= BASE_URL ?>?action=order-detail&ma_don=<?= urlencode($don['ma_don']) ?>" class="btn btn-ghost btn-lg">
                <i class="bi bi-receipt"></i> Xem chi tiết đơn hàng
            </a>
            <a href="<?= BASE_URL ?>" class="btn btn-brand btn-lg">
                <i class="bi bi-house-door"></i> Về trang chủ
            </a>
            <a href="<?= BASE_URL ?>#thuc-don" class="btn btn-ghost btn-lg">
                <i class="bi bi-bowl-hot"></i> Tiếp tục mua hàng
            </a>
        </div>

    </div>
</section>
