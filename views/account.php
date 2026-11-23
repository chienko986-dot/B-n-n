<?php
/**
 * VIEW: TRANG TAI KHOAN KHACH HANG
 * - Sua thong tin ca nhan (ho ten, sdt, dia chi)
 * - Doi mat khau
 * - Xem so dia chi da luu
 *
 * Du lieu tu AccountController:
 *   $user    = row NGUOIDUNG (khong co mat_khau)
 *   $dsDiaChi = so dia chi da luu
 *   $error   = thong bao loi (neu co, khi render lai sau POST loi)
 *   $tab     = 'profile' | 'password' (tab duoc mo khi co loi)
 */

$flash    = get_flash();
$dsDiaChi = $dsDiaChi ?? [];
$tab      = $tab ?? 'profile';
$old      = $old ?? [];
$hoTen    = $old['ho_ten']   ?? $user['ho_ten'];
$sdt      = $old['so_dien_thoai'] ?? ($user['so_dien_thoai'] ?? '');
$diaChi   = $old['dia_chi']  ?? ($user['dia_chi'] ?? '');
?>

<section class="section shop-page">
    <div class="container container-narrow">

        <div class="shop-head">
            <div>
                <span class="eyebrow">Tài khoản</span>
                <h2 class="section-title">Xin chào, <?= htmlspecialchars($user['ho_ten'], ENT_QUOTES, 'UTF-8') ?></h2>
            </div>
            <a href="<?= BASE_URL ?>?action=orders" class="section-link">
                <i class="bi bi-receipt"></i> Đơn hàng của tôi
            </a>
        </div>

        <?php if ($flash): ?>
            <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : 'success' ?> shop-alert" role="alert">
                <i class="bi <?= $flash['type'] === 'error' ? 'bi-exclamation-circle-fill' : 'bi-check-circle-fill' ?>"></i>
                <?= htmlspecialchars($flash['message'], ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($error) && ($tab ?? '') === 'profile'): ?>
            <div class="alert alert-danger shop-alert" role="alert">
                <i class="bi bi-exclamation-circle-fill"></i>
                <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <!-- Tabs -->
        <div class="filter-chips mb-4" role="tablist">
            <a href="#thong-tin" class="chip <?= $tab === 'profile' ? 'is-active' : '' ?>" data-tab="profile">
                <i class="bi bi-person-lines-fill me-1"></i> Thông tin
            </a>
            <a href="#mat-khau" class="chip <?= $tab === 'password' ? 'is-active' : '' ?>" data-tab="password">
                <i class="bi bi-shield-lock me-1"></i> Đổi mật khẩu
            </a>
            <a href="#so-dia-chi" class="chip" data-tab="address">
                <i class="bi bi-geo-alt me-1"></i> Sổ địa chỉ (<?= count($dsDiaChi) ?>)
            </a>
        </div>

        <!-- ================= TAB THONG TIN ================= -->
        <div class="checkout-card account-panel" id="panel-profile" <?= $tab === 'profile' ? '' : 'hidden' ?>>
            <h3 class="checkout-card-title">
                <i class="bi bi-person-lines-fill"></i> Thông tin cá nhân
            </h3>

            <form method="post" action="<?= BASE_URL ?>?action=account-update">
                <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">

                <div class="form-group form-group-full mb-3">
                    <label class="form-label">Email</label>
                    <input class="form-control" type="email" value="<?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') ?>" disabled>
                    <small class="text-muted">Email đăng nhập, không thể thay đổi.</small>
                </div>

                <div class="form-group form-group-full mb-3">
                    <label class="form-label" for="ac_ho_ten">Họ và tên <span class="required">*</span></label>
                    <input class="form-control" id="ac_ho_ten" name="ho_ten" type="text" maxlength="150" required
                           value="<?= htmlspecialchars($hoTen, ENT_QUOTES, 'UTF-8') ?>" autocomplete="name">
                </div>

                <div class="form-group form-group-full mb-3">
                    <label class="form-label" for="ac_sdt">Số điện thoại</label>
                    <input class="form-control" id="ac_sdt" name="so_dien_thoai" type="tel" maxlength="20"
                           placeholder="0901234567" value="<?= htmlspecialchars($sdt, ENT_QUOTES, 'UTF-8') ?>" autocomplete="tel">
                </div>

                <div class="form-group form-group-full mb-4">
                    <label class="form-label" for="ac_dia_chi">Địa chỉ</label>
                    <textarea class="form-control" id="ac_dia_chi" name="dia_chi" rows="2" maxlength="500"
                              placeholder="Số nhà, đường, quận/huyện, tỉnh/thành"
                              autocomplete="street-address"><?= htmlspecialchars($diaChi, ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>

                <button class="btn btn-brand" type="submit">
                    <i class="bi bi-check-lg"></i> Lưu thay đổi
                </button>
            </form>
        </div>

        <!-- ================= TAB MAT KHAU ================= -->
        <div class="checkout-card account-panel" id="panel-password" <?= $tab === 'password' ? '' : 'hidden' ?>>
            <h3 class="checkout-card-title">
                <i class="bi bi-shield-lock"></i> Đổi mật khẩu
            </h3>

            <?php if (!empty($error) && ($tab ?? '') === 'password'): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="bi bi-exclamation-circle-fill"></i>
                    <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <form method="post" action="<?= BASE_URL ?>?action=account-password">
                <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">

                <div class="form-group form-group-full mb-3">
                    <label class="form-label" for="pw_cu">Mật khẩu hiện tại <span class="required">*</span></label>
                    <input class="form-control" id="pw_cu" name="mat_khau_cu" type="password" required autocomplete="current-password">
                </div>

                <div class="form-group form-group-full mb-3">
                    <label class="form-label" for="pw_moi">Mật khẩu mới <span class="required">*</span></label>
                    <input class="form-control" id="pw_moi" name="mat_khau_moi" type="password" required minlength="8"
                           autocomplete="new-password">
                    <small class="text-muted">Từ 8 ký tự trở lên.</small>
                </div>

                <div class="form-group form-group-full mb-4">
                    <label class="form-label" for="pw_xn">Xác nhận mật khẩu mới <span class="required">*</span></label>
                    <input class="form-control" id="pw_xn" name="xac_nhan_mat_khau_moi" type="password" required autocomplete="new-password">
                </div>

                <button class="btn btn-brand" type="submit">
                    <i class="bi bi-key"></i> Đổi mật khẩu
                </button>
            </form>
        </div>

        <!-- ================= TAB SO DIA CHI ================= -->
        <div class="checkout-card account-panel" id="panel-address" hidden>
            <h3 class="checkout-card-title">
                <i class="bi bi-geo-alt"></i> Sổ địa chỉ nhận hàng
            </h3>

            <?php if (empty($dsDiaChi)): ?>
                <p class="text-muted mb-0">
                    Bạn chưa lưu địa chỉ nào. Địa chỉ sẽ tự lưu khi bạn tick
                    <b>“Lưu vào sổ địa chỉ”</b> ở trang đặt hàng.
                </p>
            <?php else: ?>
                <div class="addr-book">
                    <?php foreach ($dsDiaChi as $dc):
                        $dcId = (int) $dc['id'];
                        $isDefault = (int) ($dc['mac_dinh'] ?? 0) === 1;
                    ?>
                        <div class="addr-card <?= $isDefault ? 'is-active' : '' ?>">
                            <div class="addr-card-main" style="cursor: default;">
                                <div class="addr-card-body">
                                    <div class="addr-card-head">
                                        <b><?= htmlspecialchars($dc['ten_nguoi_nhan'], ENT_QUOTES, 'UTF-8') ?></b>
                                        <span class="addr-card-phone"><?= htmlspecialchars($dc['so_dien_thoai'], ENT_QUOTES, 'UTF-8') ?></span>
                                        <?php if ($isDefault): ?>
                                            <span class="addr-card-badge">Mặc định</span>
                                        <?php endif; ?>
                                    </div>
                                    <p class="addr-card-address mb-0">
                                        <?= htmlspecialchars($dc['dia_chi_chi_tiet'], ENT_QUOTES, 'UTF-8') ?>
                                    </p>
                                    <?php if (!empty($dc['ghi_chu'])): ?>
                                        <p class="addr-card-note mb-0">
                                            <i class="bi bi-sticky"></i>
                                            <?= htmlspecialchars($dc['ghi_chu'], ENT_QUOTES, 'UTF-8') ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="d-flex flex-column gap-2 justify-content-center">
                                <form method="post" action="<?= BASE_URL ?>?action=address-set-default">
                                    <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="hidden" name="id" value="<?= $dcId ?>">
                                    <input type="hidden" name="back" value="account">
                                    <button class="btn btn-sm btn-outline-secondary w-100" type="submit"
                                            title="Đặt làm mặc định" <?= $isDefault ? 'disabled' : '' ?>>
                                        <i class="bi bi-star<?= $isDefault ? '-fill' : '' ?>"></i>
                                    </button>
                                </form>
                                <form method="post" action="<?= BASE_URL ?>?action=address-delete">
                                    <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="hidden" name="id" value="<?= $dcId ?>">
                                    <input type="hidden" name="back" value="account">
                                    <button class="btn btn-sm btn-outline-danger w-100" type="submit" title="Xóa địa chỉ">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <p class="text-muted small mt-3 mb-0">
                    <i class="bi bi-info-circle"></i>
                    Để thêm địa chỉ mới, hãy tick “Lưu vào sổ địa chỉ” khi đặt hàng.
                </p>
            <?php endif; ?>
        </div>

    </div>
</section>

<script>
(function () {
    var chips = document.querySelectorAll('.filter-chips .chip[data-tab]');
    var panels = {
        profile: document.getElementById('panel-profile'),
        password: document.getElementById('panel-password'),
        address: document.getElementById('panel-address')
    };

    function show(name) {
        Object.keys(panels).forEach(function (k) {
            if (panels[k]) panels[k].hidden = (k !== name);
        });
        chips.forEach(function (c) {
            c.classList.toggle('is-active', c.getAttribute('data-tab') === name);
        });
    }

    chips.forEach(function (chip) {
        chip.addEventListener('click', function (e) {
            e.preventDefault();
            show(chip.getAttribute('data-tab'));
            if (history.replaceState) {
                history.replaceState(null, '', '#' + chip.getAttribute('href').replace('#', ''));
            }
        });
    });

    // Mo theo hash (#mat-khau, #so-dia-chi)
    var hash = (location.hash || '').replace('#', '');
    if (hash === 'mat-khau') show('password');
    else if (hash === 'so-dia-chi') show('address');
    else if (hash === 'thong-tin') show('profile');
})();
</script>
