<section class="container py-5">
    <div class="auth-card mx-auto">
        <h1 class="h3 mb-4">Tạo tài khoản</h1>
        <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <form method="post" action="<?= BASE_URL ?>?action=register">
            <label class="form-label" for="ho_ten">Họ và tên</label>
            <input class="form-control mb-3" id="ho_ten" name="ho_ten" required value="<?= htmlspecialchars($_POST['ho_ten'] ?? '') ?>">
            <label class="form-label" for="email">Email</label>
            <input class="form-control mb-3" id="email" name="email" type="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            <label class="form-label" for="mat_khau">Mật khẩu</label>
            <input class="form-control mb-4" id="mat_khau" name="mat_khau" type="password" required>
            <button class="btn btn-login w-100" type="submit">Đăng ký</button>
        </form>
        <p class="mt-3 mb-0">Đã có tài khoản? <a href="<?= BASE_URL ?>?action=login">Đăng nhập</a></p>
    </div>
</section>
