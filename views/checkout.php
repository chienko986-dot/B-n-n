<?php
/**
 * VIEW: TRANG DAT HANG
 * - Form thong tin khach hang (ho ten, so dien thoai, email, dia chi, ghi chu)
 * - Khu vuc hien thi don hang (mon, so luong, gia, phi van chuyen, tong tien)
 *
 * Du lieu tu controller:
 *   $cart    = cart_details()
 *   $errors  = mang loi kiem tra du lieu (neu co)
 *   $old     = du lieu nguoi dung da nhap (de khong mat khi co loi)
 */

$errors = $errors ?? [];
$old    = $old ?? [];
$flash  = get_flash();
?>

<section class="section shop-page">
    <div class="container">

        <div class="shop-head">
            <div>
                <span class="eyebrow">Bước cuối cùng</span>
                <h2 class="section-title">Thông tin đặt hàng</h2>
            </div>
            <a href="<?= BASE_URL ?>?action=cart" class="section-link">
                <i class="bi bi-arrow-left"></i> Quay lại giỏ hàng
            </a>
        </div>

        <?php if ($flash): ?>
            <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : 'success' ?> shop-alert" role="alert">
                <i class="bi <?= $flash['type'] === 'error' ? 'bi-exclamation-circle-fill' : 'bi-check-circle-fill' ?>"></i>
                <?= htmlspecialchars($flash['message'], ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <?php
        // Tong hop toan bo loi thanh 1 o ro rang o dau trang
        // (tranh truong hop khach hang khong thay loi duoi tung o)
        $dsLoi = [];

        if (!empty($errors['top'])) {
            $dsLoi[] = $errors['top'];
        }

        foreach ($errors as $tenField => $thongBao) {
            if ($tenField !== 'top') {
                $dsLoi[] = $thongBao;
            }
        }
        ?>

        <?php if (!empty($dsLoi)): ?>
            <div class="alert alert-danger shop-alert error-summary" id="errorSummary" role="alert" tabindex="-1">
                <i class="bi bi-exclamation-octagon-fill"></i>
                <b>Không thể đặt hàng, vui lòng kiểm tra lại:</b>
                <ul>
                    <?php foreach ($dsLoi as $thongBao): ?>
                        <li><?= htmlspecialchars($thongBao, ENT_QUOTES, 'UTF-8') ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <script>
                (function () {
                    var box = document.getElementById('errorSummary');
                    if (box) {
                        box.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        box.focus({ preventScroll: true });
                    }
                })();
            </script>
        <?php endif; ?>

        <form method="post" action="<?= BASE_URL ?>?action=checkout-post" class="checkout-form" novalidate>

            <div class="checkout-layout">

                <!-- ================= FORM THONG TIN KHACH HANG ================= -->
                <div class="checkout-card">
                    <h3 class="checkout-card-title">
                        <span class="checkout-step">1</span> Thông tin giao hàng
                    </h3>

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="ho_ten">Họ và tên <span class="required">*</span></label>
                            <input type="text"
                                   id="ho_ten"
                                   name="ho_ten"
                                   class="form-control <?= !empty($errors['ho_ten']) ? 'is-invalid' : '' ?>"
                                   placeholder="Nguyễn Văn A"
                                   maxlength="150"
                                   value="<?= old_value($old, 'ho_ten') ?>"
                                   autocomplete="name">
                            <?= field_error($errors, 'ho_ten') ?>
                        </div>

                        <div class="form-group">
                            <label for="so_dien_thoai">Số điện thoại <span class="required">*</span></label>
                            <input type="tel"
                                   id="so_dien_thoai"
                                   name="so_dien_thoai"
                                   class="form-control <?= !empty($errors['so_dien_thoai']) ? 'is-invalid' : '' ?>"
                                   placeholder="0901234567"
                                   maxlength="20"
                                   value="<?= old_value($old, 'so_dien_thoai') ?>"
                                   autocomplete="tel">
                            <?= field_error($errors, 'so_dien_thoai') ?>
                        </div>

                        <div class="form-group form-group-full">
                            <label for="email">Email <span class="required">*</span></label>
                            <input type="email"
                                   id="email"
                                   name="email"
                                   class="form-control <?= !empty($errors['email']) ? 'is-invalid' : '' ?>"
                                   placeholder="ban@gmail.com"
                                   maxlength="255"
                                   value="<?= old_value($old, 'email') ?>"
                                   autocomplete="email">
                            <?= field_error($errors, 'email') ?>
                        </div>

                        <div class="form-group form-group-full">
                            <label for="dia_chi">Địa chỉ giao hàng <span class="required">*</span></label>
                            <textarea id="dia_chi"
                                      name="dia_chi"
                                      rows="3"
                                      class="form-control <?= !empty($errors['dia_chi']) ? 'is-invalid' : '' ?>"
                                      placeholder="Số nhà, đường, phường/xã, quận/huyện, tỉnh/thành phố"
                                      maxlength="500"><?= old_value($old, 'dia_chi') ?></textarea>
                            <?= field_error($errors, 'dia_chi') ?>
                        </div>

                        <div class="form-group form-group-full">
                            <label for="ghi_chu">Ghi chú (không bắt buộc)</label>
                            <textarea id="ghi_chu"
                                      name="ghi_chu"
                                      rows="3"
                                      class="form-control"
                                      placeholder="Ví dụ: gọi thêm nước sốt, không hành, giao trước cửa..."
                                      maxlength="500"><?= old_value($old, 'ghi_chu') ?></textarea>
                            <?= field_error($errors, 'ghi_chu') ?>
                        </div>
                    </div>

                    <div class="payment-box">
                        <span class="payment-title"><i class="bi bi-cash-coin"></i> Hình thức thanh toán</span>
                        <label class="payment-option">
                            <input type="radio" name="thanh_toan" value="tien_mat" checked>
                            <span><i class="bi bi-truck"></i> Thanh toán khi nhận hàng (COD)</span>
                        </label>
                        <label class="payment-option">
                            <input type="radio" name="thanh_toan" value="chuyen_khoan">
                            <span><i class="bi bi-bank"></i> Chuyển khoản ngân hàng</span>
                        </label>
                    </div>
                </div>

                <!-- ================= TONG DON HANG ================= -->
                <aside class="checkout-card checkout-summary">
                    <h3 class="checkout-card-title">
                        <span class="checkout-step">2</span> Đơn hàng của bạn
                    </h3>

                    <ul class="order-lines">
                        <?php foreach ($cart['items'] as $item): ?>
                            <li class="order-line">
                                <span class="order-line-qty"><?= (int) $item['so_luong'] ?>×</span>
                                <span class="order-line-name">
                                    <?= htmlspecialchars($item['ten_mon'], ENT_QUOTES, 'UTF-8') ?>
                                    <small><?= format_price($item['don_gia']) ?></small>
                                </span>
                                <span class="order-line-total"><?= format_price($item['thanh_tien']) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>

                    <ul class="summary-list">
                        <li>
                            <span>Tạm tính</span>
                            <strong><?= format_price($cart['tong_tien_hang']) ?></strong>
                        </li>
                        <li>
                            <span>Phí vận chuyển</span>
                            <strong>
                                <?= $cart['phi_giao_hang'] > 0 ? format_price($cart['phi_giao_hang']) : 'Miễn phí' ?>
                            </strong>
                        </li>
                    </ul>

                    <div class="summary-total">
                        <span>Tổng thanh toán</span>
                        <strong><?= format_price($cart['tong_thanh_toan']) ?></strong>
                    </div>

                    <button type="submit" class="btn btn-brand w-100 summary-btn">
                        Xác nhận đặt hàng <i class="bi bi-bag-check"></i>
                    </button>

                    <p class="summary-note">
                        <i class="bi bi-shield-check"></i>
                        Thông tin của bạn chỉ dùng để giao hàng.
                    </p>
                </aside>

            </div>
        </form>

    </div>
</section>
