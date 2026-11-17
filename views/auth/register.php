<section class="container py-5">
    <div class="auth-card mx-auto">
        <div class="auth-icon"><i class="bi bi-person-plus"></i></div>
        <p class="auth-eyebrow">CHÀO MỪNG ĐẾN ANLAME</p>
        <h1 class="auth-title">Tạo tài khoản</h1>
        <p class="auth-intro">Đăng ký để lưu thông tin và đặt món nhanh hơn.</p>
        <?php if ($error): ?>
            <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>
        <form method="post" action="<?= BASE_URL ?>?action=register">
            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
            <label class="form-label" for="ho_ten">Họ và tên</label>
            <input class="form-control auth-input mb-3" id="ho_ten" name="ho_ten" autocomplete="name"
                maxlength="150" required value="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>">
            <label class="form-label" for="email">Email</label>
            <input class="form-control auth-input mb-3" id="email" name="email" type="email"
                autocomplete="email" maxlength="255" required value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>">
            <label class="form-label" for="mat_khau">Mật khẩu</label>
            <input class="form-control auth-input mb-3" id="mat_khau" name="mat_khau" type="password"
                autocomplete="new-password" minlength="8" maxlength="72" required>
            <label class="form-label" for="xac_nhan_mat_khau">Nhập lại mật khẩu</label>
            <input class="form-control auth-input mb-4" id="xac_nhan_mat_khau" name="xac_nhan_mat_khau"
                type="password" autocomplete="new-password" minlength="8" maxlength="72" required>
            <button class="btn auth-submit w-100" type="submit">
                Tạo tài khoản <i class="bi bi-arrow-right"></i>
            </button>
        </form>
        <p class="auth-switch">Đã có tài khoản? <a href="<?= BASE_URL ?>?action=login">Đăng nhập</a></p>
    </div>
</section>
