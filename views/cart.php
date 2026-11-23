<?php
/**
 * VIEW: TRANG GIO HANG
 * - Hien thi danh sach mon da them vao gio
 * - Nut tang / giam so luong, nut xoa, thanh tien tung mon
 * - Tong tien gio hang, nut tiep tuc mua hang va tien hanh dat hang
 *
 * Du lieu tu controller: $cart = cart_details()
 *   $cart['items'], $cart['tong_tien_hang'],
 *   $cart['phi_giao_hang'], $cart['tong_thanh_toan']
 */

$flash = get_flash();
?>

<section class="section shop-page">
    <div class="container">

        <div class="shop-head">
            <div>
                <span class="eyebrow">Giỏ hàng</span>
                <h2 class="section-title">Giỏ hàng của bạn</h2>
            </div>
            <a href="<?= BASE_URL ?>" class="section-link">
                <i class="bi bi-arrow-left"></i> Tiếp tục mua sắm
            </a>
        </div>

        <?php if ($flash): ?>
            <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : 'success' ?> shop-alert" role="alert">
                <i class="bi <?= $flash['type'] === 'error' ? 'bi-exclamation-circle-fill' : 'bi-check-circle-fill' ?>"></i>
                <?= htmlspecialchars($flash['message'], ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <?php if (empty($cart['items'])): ?>
            <!-- ================= GIO HANG RONG ================= -->
            <div class="cart-empty">
                <span class="cart-empty-icon"><i class="bi bi-bag-x"></i></span>
                <h3>Giỏ hàng đang trống</h3>
                <p>Chưa có món nào trong giỏ. Vào thực đơn và chọn ngay món bạn thích nhé!</p>
                <a href="<?= BASE_URL ?>#thuc-don" class="btn btn-brand btn-lg">
                    <i class="bi bi-bowl-hot"></i> Tiếp tục mua hàng
                </a>
            </div>

        <?php else: ?>
            <!-- ================= GIO HANG CO MON ================= -->
            <div class="cart-layout"
                 id="cartPage"
                 data-ship-fee="<?= (int) PHI_GIAO_HANG ?>"
                 data-free-from="<?= (int) MIEN_PHI_GIAO_TU ?>">

                <div class="cart-list">

                    <?php foreach ($cart['items'] as $item): ?>
                        <div class="cart-item"
                             data-id="<?= (int) $item['id'] ?>"
                             data-key="<?= htmlspecialchars($item['key'] ?? (string) $item['id'], ENT_QUOTES, 'UTF-8') ?>"
                             data-price="<?= (int) round($item['don_gia']) ?>">

                            <!-- Anh mon an -->
                            <div class="cart-item-thumb">
                                <i class="bi bi-bowl-hot cart-item-fallback"></i>
                                <?php if (!empty($item['hinh_anh'])): ?>
                                    <img src="<?= BASE_URL . htmlspecialchars($item['hinh_anh'], ENT_QUOTES, 'UTF-8') ?>"
                                         alt="<?= htmlspecialchars($item['ten_mon'], ENT_QUOTES, 'UTF-8') ?>"
                                         loading="lazy"
                                         onerror="this.style.display='none'">
                                <?php endif; ?>
                            </div>

                            <!-- Thong tin mon -->
                            <div class="cart-item-info">
                                <span class="cart-item-cat"><?= htmlspecialchars($item['ten_danh_muc'], ENT_QUOTES, 'UTF-8') ?></span>
                                <h3 class="cart-item-name"><?= htmlspecialchars($item['ten_mon'], ENT_QUOTES, 'UTF-8') ?></h3>

                                <?php if (!empty($item['size_ten']) || !empty($item['topping_list']) || !empty($item['ghi_chu'])): ?>
                                    <div class="cart-item-opts">
                                        <?php if (!empty($item['size_ten'])): ?>
                                            <span class="cart-opt-tag"><i class="bi bi-rulers"></i> <?= htmlspecialchars($item['size_ten'], ENT_QUOTES, 'UTF-8') ?></span>
                                        <?php endif; ?>
                                        <?php if (!empty($item['topping_list'])): ?>
                                            <span class="cart-opt-tag"><i class="bi bi-plus-circle"></i> <?= htmlspecialchars($item['topping_list'], ENT_QUOTES, 'UTF-8') ?></span>
                                        <?php endif; ?>
                                        <?php if (!empty($item['ghi_chu'])): ?>
                                            <span class="cart-opt-note"><i class="bi bi-sticky"></i> <?= htmlspecialchars($item['ghi_chu'], ENT_QUOTES, 'UTF-8') ?></span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>

                                <span class="cart-item-price"><?= format_price($item['don_gia']) ?></span>

                                <?php if ($item['het_hang']): ?>
                                    <span class="cart-item-warn">
                                        <i class="bi bi-exclamation-triangle-fill"></i>
                                        Chỉ còn <?= (int) $item['ton_kho'] ?> phần
                                    </span>
                                <?php endif; ?>
                            </div>

                            <!-- So luong -->
                            <div class="cart-item-qty">
                                <form method="post" action="<?= BASE_URL ?>?action=cart-update" class="qty-form js-qty-form">
                                    <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                                    <input type="hidden" name="key" value="<?= htmlspecialchars($item['key'] ?? '', ENT_QUOTES, 'UTF-8') ?>">

                                    <button type="submit" name="huong" value="giam" class="qty-btn js-qty-minus"
                                            aria-label="Giảm số lượng">
                                        <i class="bi bi-dash-lg"></i>
                                    </button>

                                    <input type="number"
                                           class="qty-input js-qty-input"
                                           name="so_luong"
                                           min="1"
                                           max="<?= max(1, (int) $item['ton_kho']) ?>"
                                           value="<?= (int) $item['so_luong'] ?>"
                                           aria-label="Số lượng">

                                    <button type="submit" name="huong" value="tang" class="qty-btn js-qty-plus"
                                            aria-label="Tăng số lượng">
                                        <i class="bi bi-plus-lg"></i>
                                    </button>

                                    <button type="submit" class="qty-update js-qty-update">Cập nhật</button>
                                </form>
                            </div>

                            <!-- Thanh tien tung mon -->
                            <div class="cart-item-total">
                                <span class="cart-item-total-label">Thành tiền</span>
                                <strong class="js-line-total"><?= format_price($item['thanh_tien']) ?></strong>
                            </div>

                            <!-- Xoa mon -->
                            <form method="post" action="<?= BASE_URL ?>?action=cart-remove" class="cart-item-remove js-remove-form">
                                <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                                <input type="hidden" name="key" value="<?= htmlspecialchars($item['key'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                <button type="submit" class="btn-remove" title="Xóa khỏi giỏ hàng" aria-label="Xóa sản phẩm">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </form>
                        </div>
                    <?php endforeach; ?>

                    <!-- Cac nut phu -->
                    <div class="cart-foot">
                        <a href="<?= BASE_URL ?>#thuc-don" class="btn btn-ghost">
                            <i class="bi bi-arrow-left"></i> Tiếp tục mua hàng
                        </a>

                        <form method="post" action="<?= BASE_URL ?>?action=cart-clear">
                            <button type="submit" class="btn btn-link cart-clear-btn">
                                <i class="bi bi-x-circle"></i> Xóa toàn bộ giỏ hàng
                            </button>
                        </form>
                    </div>
                </div>

                <!-- ================= TONG DON HANG ================= -->
                <aside class="cart-summary">
                    <h3 class="cart-summary-title">
                        <i class="bi bi-receipt-cutoff"></i> Tổng đơn hàng
                    </h3>

                    <ul class="summary-list">
                        <li>
                            <span>Số món</span>
                            <strong><?= (int) $cart['so_mon'] ?> món</strong>
                        </li>
                        <li>
                            <span>Tổng số lượng</span>
                            <strong class="js-summary-qty"><?= (int) $cart['so_luong'] ?> phần</strong>
                        </li>
                        <li>
                            <span>Tạm tính</span>
                            <strong class="js-summary-subtotal"><?= format_price($cart['tong_tien_hang']) ?></strong>
                        </li>
                        <li>
                            <span>Phí vận chuyển</span>
                            <strong class="js-summary-ship">
                                <?= $cart['phi_giao_hang'] > 0 ? format_price($cart['phi_giao_hang']) : 'Miễn phí' ?>
                            </strong>
                        </li>
                    </ul>

                    <div class="summary-total">
                        <span>Tổng cộng</span>
                        <strong class="js-summary-total"><?= format_price($cart['tong_thanh_toan']) ?></strong>
                    </div>

                    <a href="<?= BASE_URL ?>?action=checkout" class="btn btn-brand w-100 summary-btn">
                        Tiến hành đặt hàng <i class="bi bi-arrow-right"></i>
                    </a>

                    <a href="<?= BASE_URL ?>#thuc-don" class="btn btn-ghost w-100 summary-btn">
                        Tiếp tục mua hàng
                    </a>

                    <p class="summary-note">
                        <i class="bi bi-truck"></i>
                        Miễn phí giao hàng cho đơn từ <?= format_price(MIEN_PHI_GIAO_TU) ?>.
                    </p>
                </aside>
            </div>
        <?php endif; ?>

    </div>
</section>
