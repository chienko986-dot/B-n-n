<?php
/**
 * VIEW: TRANG DAT HANG
 * - So dia chi da luu (chon nhanh nhu Shopee) + them / sua / xoa dia chi
 * - Form thong tin khach hang (ho ten, so dien thoai, email, dia chi, ghi chu)
 * - Khu vuc hien thi don hang (mon, so luong, gia, phi van chuyen, tong tien)
 *
 * Du lieu tu controller:
 *   $cart           = cart_details()
 *   $errors         = mang loi kiem tra du lieu (neu co)
 *   $old            = du lieu nguoi dung da nhap (de khong mat khi co loi)
 *   $dsDiaChi       = so dia chi da luu cua khach (rong neu chua dang nhap)
 *   $diachiDuocChon = dia chi dang duoc chon (row)
 */

$errors = $errors ?? [];
$old    = $old ?? [];
$flash  = get_flash();

$dsDiaChi       = $dsDiaChi ?? [];
$diachiDuocChon = $diachiDuocChon ?? null;
$coSoDiaChi     = count($dsDiaChi) > 0;
$coDangNhap     = !empty($_SESSION['user']['id']);

// Che do dia chi hien tai: 'da_luu' (chon trong so) | 'moi' | 'cu'
if (!empty($old['che_do_dia_chi'])) {
    $cheDoDiaChi = $old['che_do_dia_chi'];
} else {
    $cheDoDiaChi = $coSoDiaChi ? 'da_luu' : 'moi';
}

// Du lieu dia chinh dung chung cho ca view va JS (nạp 1 lan)
$dsDiaChinh = dia_chinh_du_lieu();
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

        <form method="post" action="<?= BASE_URL ?>?action=checkout-post" class="checkout-form" id="checkoutForm" novalidate
              data-dia-chinh="<?= BASE_URL ?>assets/data/vn-address.json">
            <input type="hidden" name="_csrf_token" value="<?= csrf_token() ?>">

            <div class="checkout-layout">

                <!-- ================= FORM THONG TIN KHACH HANG ================= -->
                <div class="checkout-card">
                    <h3 class="checkout-card-title">
                        <span class="checkout-step">1</span> Thông tin giao hàng
                    </h3>

                    <!-- ================= SO DIA CHI DA LUU ================= -->
                    <?php if ($coSoDiaChi): ?>
                        <div class="form-group form-group-full">
                            <label>Sổ địa chỉ <span class="required">*</span></label>

                            <div class="addr-book" id="addrBook"
                                 data-addresses='<?= htmlspecialchars(json_encode($dsDiaChi, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8') ?>'>
                                <?php foreach ($dsDiaChi as $dc):
                                    $dcId  = (int) $dc['id'];
                                    $chon  = $diachiDuocChon
                                        && (int) $diachiDuocChon['id'] === $dcId
                                        && $cheDoDiaChi === 'da_luu';
                                ?>
                                    <div class="addr-card<?= $chon ? ' is-active' : '' ?>" data-addr-id="<?= $dcId ?>">
                                        <label class="addr-card-main">
                                            <input type="radio" name="che_do_dia_chi"
                                                   value="da_luu|<?= $dcId ?>" <?= $chon ? 'checked' : '' ?>>
                                            <span class="addr-card-body">
                                                <span class="addr-card-head">
                                                    <b><?= htmlspecialchars($dc['ten_nguoi_nhan'], ENT_QUOTES, 'UTF-8') ?></b>
                                                    <span class="addr-card-phone"><?= htmlspecialchars($dc['so_dien_thoai'], ENT_QUOTES, 'UTF-8') ?></span>
                                                    <?php if ((int) $dc['mac_dinh'] === 1): ?>
                                                        <span class="addr-card-badge">Mặc định</span>
                                                    <?php endif; ?>
                                                </span>
                                                <span class="addr-card-address"><?= htmlspecialchars($dc['dia_chi_chi_tiet'], ENT_QUOTES, 'UTF-8') ?></span>
                                                <?php if (!empty($dc['ghi_chu'])): ?>
                                                    <span class="addr-card-note">
                                                        <i class="bi bi-sticky"></i>
                                                        <?= htmlspecialchars($dc['ghi_chu'], ENT_QUOTES, 'UTF-8') ?>
                                                    </span>
                                                <?php endif; ?>
                                            </span>
                                        </label>

                                        <span class="addr-card-actions">
                                            <button type="button" class="addr-card-btn addr-btn-edit" data-addr-id="<?= $dcId ?>">
                                                Sửa
                                            </button>
                                            <button type="submit" class="addr-card-btn addr-btn-del"
                                                    form="addrDelForm<?= $dcId ?>"
                                                    onclick="return confirm('Bạn có chắc muốn xóa địa chỉ này?');">
                                                Xóa
                                            </button>
                                        </span>
                                    </div>
                                <?php endforeach; ?>

                                <button type="button" class="addr-book-add" id="addrAddBtn">
                                    <i class="bi bi-plus-lg"></i> Thêm địa chỉ mới
                                </button>
                            </div>

                            <p class="addr-saved-hint">
                                <i class="bi bi-info-circle"></i>
                                Chọn một địa chỉ ở trên để giao nhanh, hoặc nhập địa chỉ khác bên dưới.
                            </p>
                        </div>
                    <?php endif; ?>

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

                        <!-- ================= NHAP DIA CHI KHAC (MOI / CU) ================= -->
                        <div class="form-group form-group-full" id="manualAddr">
                            <div class="addr-or"><span>hoặc nhập địa chỉ khác</span></div>

                            <label>Địa chỉ giao hàng <span class="required">*</span></label>

                            <div class="addr-mode" role="radiogroup" aria-label="Cách nhập địa chỉ giao hàng">
                                <label class="addr-mode-option">
                                    <input type="radio" name="che_do_dia_chi" value="moi"
                                           <?= $cheDoDiaChi === 'moi' ? 'checked' : '' ?>>
                                    <span class="addr-mode-body">
                                        <i class="bi bi-geo-alt-fill"></i>
                                        <b>Địa chỉ mới</b>
                                        <small>Chọn Tỉnh / Huyện / Xã</small>
                                    </span>
                                </label>
                                <label class="addr-mode-option">
                                    <input type="radio" name="che_do_dia_chi" value="cu"
                                           <?= $cheDoDiaChi === 'cu' ? 'checked' : '' ?>>
                                    <span class="addr-mode-body">
                                        <i class="bi bi-pencil-square"></i>
                                        <b>Địa chỉ cũ</b>
                                        <small>Gõ tay toàn bộ địa chỉ</small>
                                    </span>
                                </label>
                            </div>

                            <!-- ================= CHE DO MOI: TINH / HUYEN / XA ================= -->
                            <div class="addr-panel" id="addrPanelMoi"
                                 <?= $cheDoDiaChi === 'moi' ? '' : 'hidden' ?>>
                                <div class="addr-grid">
                                    <div class="form-group">
                                        <label for="tinh_id">Tỉnh / Thành phố <span class="required">*</span></label>
                                        <select id="tinh_id"
                                                name="tinh_id"
                                                class="form-control addr-select <?= !empty($errors['tinh_id']) ? 'is-invalid' : '' ?>"
                                                data-cap="tinh">
                                            <option value="">-- Chọn tỉnh / thành --</option>
                                            <?php foreach ($dsDiaChinh as $tinh): ?>
                                                <option value="<?= htmlspecialchars((string) $tinh['level1_id'], ENT_QUOTES, 'UTF-8') ?>"
                                                    <?= (string) ($old['tinh_id'] ?? '') === (string) $tinh['level1_id'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars((string) $tinh['name'], ENT_QUOTES, 'UTF-8') ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <?= field_error($errors, 'tinh_id') ?>
                                    </div>

                                    <div class="form-group">
                                        <label for="huyen_id">Quận / Huyện <span class="required">*</span></label>
                                        <select id="huyen_id"
                                                name="huyen_id"
                                                class="form-control addr-select <?= !empty($errors['huyen_id']) ? 'is-invalid' : '' ?>"
                                                data-cap="huyen"
                                                <?= ($old['tinh_id'] ?? '') === '' ? 'disabled' : '' ?>>
                                            <option value="">-- Chọn quận / huyện --</option>
                                            <?php
                                            $dsHuyen = [];
                                            if (($old['tinh_id'] ?? '') !== '') {
                                                foreach ($dsDiaChinh as $tinh) {
                                                    if ((string) $tinh['level1_id'] === (string) $old['tinh_id']) {
                                                        $dsHuyen = $tinh['level2s'] ?? [];
                                                        break;
                                                    }
                                                }
                                            }
                                            foreach ($dsHuyen as $huyen): ?>
                                                <option value="<?= htmlspecialchars((string) $huyen['level2_id'], ENT_QUOTES, 'UTF-8') ?>"
                                                    <?= (string) ($old['huyen_id'] ?? '') === (string) $huyen['level2_id'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars((string) $huyen['name'], ENT_QUOTES, 'UTF-8') ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <?= field_error($errors, 'huyen_id') ?>
                                    </div>

                                    <div class="form-group">
                                        <label for="xa_id">Phường / Xã <span class="required">*</span></label>
                                        <select id="xa_id"
                                                name="xa_id"
                                                class="form-control addr-select <?= !empty($errors['xa_id']) ? 'is-invalid' : '' ?>"
                                                data-cap="xa"
                                                <?= ($old['huyen_id'] ?? '') === '' ? 'disabled' : '' ?>>
                                            <option value="">-- Chọn phường / xã --</option>
                                            <?php
                                            $dsXa = [];
                                            if (($old['tinh_id'] ?? '') !== '' && ($old['huyen_id'] ?? '') !== '') {
                                                foreach ($dsDiaChinh as $tinh) {
                                                    if ((string) $tinh['level1_id'] !== (string) $old['tinh_id']) {
                                                        continue;
                                                    }
                                                    foreach (($tinh['level2s'] ?? []) as $huyen) {
                                                        if ((string) $huyen['level2_id'] === (string) $old['huyen_id']) {
                                                            $dsXa = $huyen['level3s'] ?? [];
                                                            break 2;
                                                        }
                                                    }
                                                }
                                            }
                                            foreach ($dsXa as $xa): ?>
                                                <option value="<?= htmlspecialchars((string) $xa['level3_id'], ENT_QUOTES, 'UTF-8') ?>"
                                                    <?= (string) ($old['xa_id'] ?? '') === (string) $xa['level3_id'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars((string) $xa['name'], ENT_QUOTES, 'UTF-8') ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <?= field_error($errors, 'xa_id') ?>
                                    </div>

                                    <div class="form-group addr-grid-full">
                                        <label for="so_nha">Số nhà, đường <span class="required">*</span></label>
                                        <input type="text"
                                               id="so_nha"
                                               name="so_nha"
                                               class="form-control <?= !empty($errors['so_nha']) ? 'is-invalid' : '' ?>"
                                               placeholder="Ví dụ: 123 Nguyễn Văn Linh"
                                               maxlength="255"
                                               value="<?= old_value($old, 'so_nha') ?>"
                                               autocomplete="street-address">
                                        <?= field_error($errors, 'so_nha') ?>
                                    </div>

                                    <div class="form-group addr-grid-full">
                                        <span class="addr-preview-label">Địa chỉ đầy đủ</span>
                                        <p class="addr-preview" id="addrPreview" aria-live="polite">
                                            <i class="bi bi-geo-alt"></i>
                                            <span>Chọn tỉnh / thành, quận / huyện, phường / xã để xem địa chỉ.</span>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- ================= CHE DO CU: GO TAY ================= -->
                            <div class="addr-panel" id="addrPanelCu"
                                 <?= $cheDoDiaChi === 'cu' ? '' : 'hidden' ?>>
                                <label for="dia_chi">Địa chỉ giao hàng <span class="required">*</span></label>
                                <textarea id="dia_chi"
                                          name="dia_chi"
                                          rows="3"
                                          class="form-control <?= !empty($errors['dia_chi']) ? 'is-invalid' : '' ?>"
                                          placeholder="Số nhà, đường, phường/xã, quận/huyện, tỉnh/thành phố"
                                          maxlength="500"><?= old_value($old, 'dia_chi') ?></textarea>
                                <?= field_error($errors, 'dia_chi') ?>
                            </div>

                            <!-- Luu dia chi vao so khi khach nhap dia chi moi -->
                            <?php if ($coDangNhap): ?>
                                <label class="addr-save-check" id="addrSaveBox">
                                    <input type="checkbox" name="luu_diachi" value="1"
                                           <?= !empty($old['luu_diachi']) ? 'checked' : '' ?>>
                                    <span>
                                        <b>Lưu địa chỉ này vào sổ địa chỉ</b>
                                        <small>Lần sau đặt hàng chỉ cần chọn là xong</small>
                                    </span>
                                </label>
                            <?php endif; ?>
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
                                    <?php if (!empty($item['size_ten']) || !empty($item['topping_list']) || !empty($item['ghi_chu'])): ?>
                                        <small class="order-line-opts">
                                            <?php if (!empty($item['size_ten'])): ?>
                                                <i class="bi bi-rulers"></i> <?= htmlspecialchars($item['size_ten'], ENT_QUOTES, 'UTF-8') ?>
                                            <?php endif; ?>
                                            <?php if (!empty($item['topping_list'])): ?>
                                                <i class="bi bi-plus-circle"></i> <?= htmlspecialchars($item['topping_list'], ENT_QUOTES, 'UTF-8') ?>
                                            <?php endif; ?>
                                            <?php if (!empty($item['ghi_chu'])): ?>
                                                <i class="bi bi-sticky"></i> <?= htmlspecialchars($item['ghi_chu'], ENT_QUOTES, 'UTF-8') ?>
                                            <?php endif; ?>
                                        </small>
                                    <?php endif; ?>
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

        <!-- ================= FORM XOA DIA CHI (ngoai form dat hang) ================= -->
        <?php foreach ($dsDiaChi as $dc): ?>
            <form method="post" action="<?= BASE_URL ?>?action=address-delete" id="addrDelForm<?= (int) $dc['id'] ?>">
                <input type="hidden" name="id" value="<?= (int) $dc['id'] ?>">
                <input type="hidden" name="_csrf_token" value="<?= csrf_token() ?>">
            </form>
        <?php endforeach; ?>

        <!-- ================= MODAL THEM / SUA DIA CHI ================= -->
        <div class="addr-modal" id="addrModal" hidden>
            <div class="addr-modal-backdrop" data-addr-close></div>
            <div class="addr-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="addrModalTitle">
                <div class="addr-modal-head">
                    <h4 id="addrModalTitle">Thêm địa chỉ mới</h4>
                    <button type="button" class="addr-modal-close" data-addr-close aria-label="Đóng">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>

                <form method="post" action="<?= BASE_URL ?>?action=address-save" id="addrForm" class="addr-form">
                    <input type="hidden" name="_csrf_token" value="<?= csrf_token() ?>">
                    <input type="hidden" name="id" id="addrFormId" value="">

                    <div class="addr-form-grid">
                        <div class="form-group">
                            <label for="m_ten">Tên người nhận <span class="required">*</span></label>
                            <input type="text" id="m_ten" name="ten_nguoi_nhan"
                                   class="form-control" maxlength="150" required
                                   placeholder="Nguyễn Văn A" autocomplete="name">
                        </div>

                        <div class="form-group">
                            <label for="m_sdt">Số điện thoại <span class="required">*</span></label>
                            <input type="tel" id="m_sdt" name="so_dien_thoai"
                                   class="form-control" maxlength="20" required
                                   placeholder="0901234567" autocomplete="tel">
                        </div>

                        <div class="form-group form-group-full">
                            <div class="addr-mode" role="radiogroup" aria-label="Cách nhập địa chỉ">
                                <label class="addr-mode-option">
                                    <input type="radio" name="dia_chi_che_do" value="chon" checked>
                                    <span class="addr-mode-body">
                                        <i class="bi bi-map"></i>
                                        <b>Chọn Tỉnh / Huyện / Xã</b>
                                    </span>
                                </label>
                                <label class="addr-mode-option">
                                    <input type="radio" name="dia_chi_che_do" value="text">
                                    <span class="addr-mode-body">
                                        <i class="bi bi-pencil-square"></i>
                                        <b>Gõ tay địa chỉ</b>
                                    </span>
                                </label>
                            </div>
                        </div>

                        <!-- Che do chon Tinh / Huyen / Xa -->
                        <div class="form-group form-group-full" id="addrPanelChon">
                            <div class="addr-grid">
                                <div class="form-group">
                                    <label for="m_tinh_id">Tỉnh / Thành phố <span class="required">*</span></label>
                                    <select id="m_tinh_id" name="tinh_id" class="form-control addr-select" data-cap="tinh">
                                        <option value="">-- Chọn tỉnh / thành --</option>
                                        <?php foreach ($dsDiaChinh as $tinh): ?>
                                            <option value="<?= htmlspecialchars((string) $tinh['level1_id'], ENT_QUOTES, 'UTF-8') ?>">
                                                <?= htmlspecialchars((string) $tinh['name'], ENT_QUOTES, 'UTF-8') ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="m_huyen_id">Quận / Huyện <span class="required">*</span></label>
                                    <select id="m_huyen_id" name="huyen_id" class="form-control addr-select" data-cap="huyen" disabled>
                                        <option value="">-- Chọn quận / huyện --</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="m_xa_id">Phường / Xã <span class="required">*</span></label>
                                    <select id="m_xa_id" name="xa_id" class="form-control addr-select" data-cap="xa" disabled>
                                        <option value="">-- Chọn phường / xã --</option>
                                    </select>
                                </div>

                                <div class="form-group addr-grid-full">
                                    <label for="m_so_nha">Số nhà, đường <span class="required">*</span></label>
                                    <input type="text" id="m_so_nha" name="so_nha"
                                           class="form-control" maxlength="255"
                                           placeholder="Ví dụ: 123 Nguyễn Văn Linh">
                                </div>

                                <div class="form-group addr-grid-full">
                                    <span class="addr-preview-label">Địa chỉ đầy đủ</span>
                                    <p class="addr-preview" id="m_addrPreview" aria-live="polite">
                                        <i class="bi bi-geo-alt"></i>
                                        <span>Chọn tỉnh / thành, quận / huyện, phường / xã để xem địa chỉ.</span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Che do go tay -->
                        <div class="form-group form-group-full" id="addrPanelText" hidden>
                            <label for="m_dia_chi_text">Địa chỉ giao hàng <span class="required">*</span></label>
                            <textarea id="m_dia_chi_text" name="dia_chi_text" rows="3"
                                      class="form-control" maxlength="500"
                                      placeholder="Số nhà, đường, phường/xã, quận/huyện, tỉnh/thành phố"></textarea>
                        </div>

                        <div class="form-group form-group-full">
                            <label for="m_ghi_chu">Ghi chú cho người giao (không bắt buộc)</label>
                            <input type="text" id="m_ghi_chu" name="ghi_chu"
                                   class="form-control" maxlength="500"
                                   placeholder="Ví dụ: gọi trước khi đến, để ở lễ tân...">
                        </div>

                        <div class="form-group form-group-full">
                            <label class="addr-save-check">
                                <input type="checkbox" name="mac_dinh" value="1">
                                <span>
                                    <b>Đặt làm địa chỉ mặc định</b>
                                    <small>Tự chọn sẵn khi bạn vào trang đặt hàng</small>
                                </span>
                            </label>
                        </div>
                    </div>

                    <div class="addr-modal-foot">
                        <button type="button" class="btn btn-ghost" data-addr-close>Hủy</button>
                        <button type="submit" class="btn btn-brand">Lưu địa chỉ</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</section>
