<?php
$don     = $donHang['donhang'] ?? null;
$chiTiet = $donHang['chi_tiet'] ?? [];
$flash   = get_flash();

$trangThai = [
    'cho_xac_nhan' => ['Đã tiếp nhận', 'bi-clock-history', 'warning'],
    'dang_giao'    => ['Đang giao hàng', 'bi-truck', 'info'],
    'hoan_thanh'   => ['Hoàn thành', 'bi-check-circle-fill', 'success'],
    'huy'          => ['Đã hủy', 'bi-x-circle-fill', 'danger'],
];

$thanhToan = [
    'tien_mat'     => ['Thanh toán khi nhận hàng (COD)', 'bi-truck'],
    'chuyen_khoan' => ['Chuyển khoản ngân hàng', 'bi-bank'],
];

$status     = $don ? ($trangThai[$don['trang_thai']] ?? $trangThai['cho_xac_nhan']) : null;
$phuongThuc = $don ? ($thanhToan[$don['phuong_thuc'] ?? ''] ?? $thanhToan['tien_mat']) : null;
$tong       = $don ? (float) $don['tong_thanh_toan'] : 0;
?>
<section class="section shop-page">
    <div class="container container-narrow">

        <div class="shop-head">
            <div>
                <span class="eyebrow">Tra cứu đơn hàng</span>
                <h2 class="section-title">Chi tiết đơn hàng</h2>
            </div>
            <a href="<?= BASE_URL ?>" class="section-link">
                <i class="bi bi-arrow-left"></i> Về trang chủ
            </a>
        </div>

        <?php if ($flash): ?>
            <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : 'success' ?> shop-alert" role="alert">
                <i class="bi <?= $flash['type'] === 'error' ? 'bi-exclamation-circle-fill' : 'bi-check-circle-fill' ?>"></i>
                <?= htmlspecialchars($flash['message'], ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <form class="checkout-card order-lookup" method="get" action="<?= BASE_URL ?>">
            <h3 class="checkout-card-title">
                <i class="bi bi-search"></i> Nhập mã đơn hàng
            </h3>
            <div class="order-lookup-form">
                <input type="hidden" name="action" value="order-detail">
                <input type="text" class="form-control" name="ma_don" required maxlength="30"
                       placeholder="Ví dụ: DH261117A3F9C2"
                       value="<?= htmlspecialchars($maDon, ENT_QUOTES, 'UTF-8') ?>"
                       aria-label="Mã đơn hàng">
                <button type="submit" class="btn btn-brand">
                    <i class="bi bi-search"></i> Tra cứu
                </button>
            </div>
        </form>

        <?php if (!$don): ?>
            <div class="cart-empty">
                <span class="cart-empty-icon"><i class="bi bi-receipt"></i></span>
                <h3><?= $maDon !== '' ? 'Không tìm thấy đơn hàng' : 'Bạn chưa có đơn hàng nào để xem' ?></h3>
                <p>
                    <?php if ($maDon !== ''): ?>
                        Không có đơn hàng nào với mã
                        <strong><?= htmlspecialchars($maDon, ENT_QUOTES, 'UTF-8') ?></strong>.
                        Vui lòng kiểm tra lại mã đơn hàng và thử lại.
                    <?php else: ?>
                        Nhập mã đơn hàng đã nhận được sau khi đặt hàng để xem chi tiết, trạng thái và các món đã đặt.
                    <?php endif; ?>
                </p>
                <a href="<?= BASE_URL ?>#thuc-don" class="btn btn-brand btn-lg">
                    <i class="bi bi-bowl-hot"></i> Đặt món ngay
                </a>
            </div>
        <?php else: ?>
            <div class="success-hero">
                <span class="success-icon"><i class="bi bi-receipt"></i></span>
                <h1>Chi tiết đơn hàng</h1>
                <p>Đơn hàng được đặt lúc <?= date('d/m/Y H:i', strtotime($don['ngay_dat'])) ?>.</p>

                <div class="success-order-code">
                    <span>Mã đơn hàng</span>
                    <strong><?= htmlspecialchars($don['ma_don'], ENT_QUOTES, 'UTF-8') ?></strong>
                </div>

                <span class="status-badge status-<?= $status[2] ?>">
                    <i class="bi <?= $status[1] ?>"></i> <?= $status[0] ?>
                </span>
            </div>

            <div class="success-layout">
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
                        <li>
                            <i class="bi <?= $phuongThuc[1] ?>"></i>
                            <div>
                                <small>Hình thức thanh toán</small>
                                <strong>
                                    <?= htmlspecialchars($phuongThuc[0], ENT_QUOTES, 'UTF-8') ?>
                                    <?php if (($don['trang_thai_thanh_toan'] ?? '') === 'da_tt'): ?>
                                        <span class="status-badge status-success">Đã thanh toán</span>
                                    <?php endif; ?>
                                </strong>
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

                <div class="checkout-card">
                    <h3 class="checkout-card-title">
                        <i class="bi bi-bag-check-fill"></i> Các món đã đặt
                    </h3>

                    <ul class="order-lines order-lines-detail">
                        <?php foreach ($chiTiet as $mon): ?>
                            <li class="order-line">
                                <span class="order-line-thumb">
                                    <?php if (!empty($mon['hinh_anh'])): ?>
                                        <img src="<?= BASE_URL . htmlspecialchars($mon['hinh_anh'], ENT_QUOTES, 'UTF-8') ?>"
                                             alt="<?= htmlspecialchars($mon['ten_mon'], ENT_QUOTES, 'UTF-8') ?>">
                                    <?php else: ?>
                                        <i class="bi bi-bowl"></i>
                                    <?php endif; ?>
                                </span>
                                <span class="order-line-qty"><?= (int) $mon['so_luong'] ?>×</span>
                                <span class="order-line-name">
                                    <?= htmlspecialchars($mon['ten_mon'], ENT_QUOTES, 'UTF-8') ?>
                                    <small><?= format_price($mon['don_gia']) ?> / phần</small>
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
                <a href="<?= BASE_URL ?>#thuc-don" class="btn btn-brand btn-lg">
                    <i class="bi bi-bowl-hot"></i> Tiếp tục mua hàng
                </a>
                <a href="<?= BASE_URL ?>" class="btn btn-ghost btn-lg">
                    <i class="bi bi-house-door"></i> Về trang chủ
                </a>
            </div>
        <?php endif; ?>

    </div>
</section>
