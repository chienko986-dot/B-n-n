<section class="container py-5">
    <div class="auth-card mx-auto">
        <div class="auth-icon"><i class="bi bi-person-check"></i></div>
        <p class="auth-eyebrow">RẤT VUI ĐƯỢC GẶP LẠI</p>
        <h1 class="auth-title">Đăng nhập</h1>
        <p class="auth-intro">Đăng nhập để tiếp tục đặt những món ngon yêu thích hoặc quản lý hệ thống.</p>
        
        <?php $flashError = get_flash('error'); if ($flashError): ?>
            <div class="alert alert-warning" role="alert"><i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($flashError, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <?php $flashSuccess = get_flash('success'); if ($flashSuccess): ?>
            <div class="alert alert-success" role="alert"><i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($flashSuccess, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <form method="post" action="<?= BASE_URL ?>?action=login<?= !empty($_GET['redirect']) ? '&redirect=' . urlencode($_GET['redirect']) : '' ?>">
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

        <div class="p-3 mt-4 rounded-3 bg-light border text-center small text-muted">
            <span class="d-block fw-semibold text-dark mb-1"><i class="bi bi-shield-check text-primary me-1"></i>Tài khoản Quản trị (Admin) demo:</span>
            <code>admin@anlame.vn</code> / <code>admin123</code>
            <div class="mt-2">
                <a href="<?= BASE_URL ?>?action=admin/seed" class="btn btn-sm btn-outline-primary py-1 px-2" style="font-size: 0.8rem;">
                    <i class="bi bi-database-fill-gear me-1"></i>Khởi tạo dữ liệu & tài khoản Admin mẫu
                </a>
            </div>
        </div>

        <p class="auth-switch mt-3">Chưa có tài khoản? <a href="<?= BASE_URL ?>?action=register">Đăng ký ngay</a></p>
    </div>
</section>
