<section class="container py-5">
    <div class="auth-card mx-auto">
        <div class="auth-icon"><i class="bi bi-person-check"></i></div>
        <p class="auth-eyebrow">RẤT VUI ĐƯỢC GẶP LẠI</p>
        <h1 class="auth-title">Đăng nhập</h1>
        <p class="auth-intro">Đăng nhập để tiếp tục đặt những món ngon yêu thích.</p>
        <?php if ($error): ?>
            <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>
        <form method="post" action="<?= BASE_URL ?>?action=login">
            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
            <label class="form-label" for="email">Email</label>
            <input class="form-control auth-input mb-3" id="email" name="email" type="email"
                autocomplete="email" maxlength="255" required value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>">
            <label class="form-label" for="mat_khau">Mật khẩu</label>
            <input class="form-control auth-input mb-4" id="mat_khau" name="mat_khau" type="password"
                autocomplete="current-password" required>
            <button class="btn auth-submit w-100" type="submit">
                Đăng nhập <i class="bi bi-arrow-right"></i>
            </button>
        </form>
        <p class="auth-switch">Chưa có tài khoản? <a href="<?= BASE_URL ?>?action=register">Đăng ký ngay</a></p>
    </div>
</section>
