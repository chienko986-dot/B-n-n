<section class="container py-5">
    <div class="auth-card mx-auto">
        <h1 class="h3 mb-4">Đăng nhập</h1>
        <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <form method="post" action="<?= BASE_URL ?>?action=login">
            <label class="form-label" for="email">Email</label>
            <input class="form-control mb-3" id="email" name="email" type="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            <label class="form-label" for="mat_khau">Mật khẩu</label>
            <input class="form-control mb-4" id="mat_khau" name="mat_khau" type="password" required>
            <button class="btn btn-login w-100" type="submit">Đăng nhập</button>
        </form>
        <p class="mt-3 mb-0">Chưa có tài khoản? <a href="<?= BASE_URL ?>?action=register">Đăng ký</a></p>
    </div>
</section>
