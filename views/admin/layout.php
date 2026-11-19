<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Quản Trị Hệ Thống - AnLaMe', ENT_QUOTES, 'UTF-8') ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@600;700;800&family=Be+Vietnam+Pro:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 & Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <!-- Admin CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/admin.css">
</head>
<body class="admin-body">

    <!-- Mobile Sidebar Overlay -->
    <div class="admin-sidebar-overlay" id="adminOverlay"></div>

    <!-- Sidebar -->
    <aside class="admin-sidebar" id="adminSidebar">
        <a href="<?= BASE_URL ?>?action=admin" class="admin-sidebar-brand">
            <div class="admin-brand-icon">
                <i class="bi bi-bowl-hot-fill"></i>
            </div>
            <div>
                <div class="admin-brand-name">An<span>LaMe</span></div>
                <span class="admin-brand-badge">Admin Panel</span>
            </div>
        </a>

        <ul class="admin-sidebar-nav">
            <li class="admin-nav-header">Tổng quan</li>
            <li>
                <a href="<?= BASE_URL ?>?action=admin" class="admin-nav-link <?= ($currentPage ?? '') === 'dashboard' ? 'active' : '' ?>">
                    <i class="bi bi-speedometer2"></i>
                    <span>Bảng điều khiển</span>
                </a>
            </li>

            <li class="admin-nav-header">Quản lý thực đơn</li>
            <li>
                <a href="<?= BASE_URL ?>?action=admin/dishes" class="admin-nav-link <?= ($currentPage ?? '') === 'dishes' ? 'active' : '' ?>">
                    <i class="bi bi-egg-fried"></i>
                    <span>Món ăn</span>
                </a>
            </li>
            <li>
                <a href="<?= BASE_URL ?>?action=admin/categories" class="admin-nav-link <?= ($currentPage ?? '') === 'categories' ? 'active' : '' ?>">
                    <i class="bi bi-grid-fill"></i>
                    <span>Danh mục</span>
                </a>
            </li>

            <li class="admin-nav-header">Kinh doanh & Bán hàng</li>
            <li>
                <a href="<?= BASE_URL ?>?action=admin/orders" class="admin-nav-link <?= ($currentPage ?? '') === 'orders' ? 'active' : '' ?>">
                    <i class="bi bi-receipt-cutoff"></i>
                    <span>Đơn hàng</span>
                </a>
            </li>
            <li>
                <a href="<?= BASE_URL ?>?action=admin/reviews" class="admin-nav-link <?= ($currentPage ?? '') === 'reviews' ? 'active' : '' ?>">
                    <i class="bi bi-star-fill"></i>
                    <span>Đánh giá</span>
                </a>
            </li>

            <li class="admin-nav-header">Hệ thống</li>
            <li>
                <a href="<?= BASE_URL ?>?action=admin/users" class="admin-nav-link <?= ($currentPage ?? '') === 'users' ? 'active' : '' ?>">
                    <i class="bi bi-people-fill"></i>
                    <span>Khách hàng & User</span>
                </a>
            </li>
            <li>
                <a href="<?= BASE_URL ?>?action=admin/seed" onclick="return confirm('Bạn có muốn nạp dữ liệu món ăn và đơn hàng mẫu vào hệ thống?');" class="admin-nav-link text-warning">
                    <i class="bi bi-database-fill-gear"></i>
                    <span>Nạp dữ liệu mẫu</span>
                </a>
            </li>
        </ul>

        <!-- Sidebar Footer -->
        <div class="admin-sidebar-footer">
            <div class="admin-user-pill">
                <div class="admin-avatar">
                    <i class="bi bi-person-fill"></i>
                </div>
                <div class="admin-user-info">
                    <div class="admin-user-name"><?= htmlspecialchars($_SESSION['user']['ho_ten'] ?? 'Quản trị viên') ?></div>
                    <div class="admin-user-role"><?= htmlspecialchars($_SESSION['user']['email'] ?? 'admin@anlame.vn') ?></div>
                </div>
            </div>
            <div class="d-flex gap-2 mt-3">
                <a href="<?= BASE_URL ?>" target="_blank" class="btn btn-sm btn-outline-light flex-grow-1" title="Mở trang web bán hàng">
                    <i class="bi bi-box-arrow-up-right me-1"></i>Xem Web
                </a>
                <form method="post" action="<?= BASE_URL ?>?action=logout" class="d-inline">
                    <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Đăng xuất">
                        <i class="bi bi-power"></i>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Main Content Wrapper -->
    <div class="admin-main-wrapper">
        <!-- Topbar -->
        <header class="admin-topbar">
            <div class="admin-topbar-left">
                <button type="button" class="admin-topbar-toggle d-lg-none" id="adminSidebarToggle" aria-label="Mở Menu">
                    <i class="bi bi-list"></i>
                </button>
                <div class="admin-breadcrumb d-none d-md-flex align-items-center gap-2">
                    <a href="<?= BASE_URL ?>?action=admin"><i class="bi bi-house-door"></i> Quản trị</a>
                    <i class="bi bi-chevron-right small text-muted"></i>
                    <span class="text-dark fw-medium"><?= htmlspecialchars($title ?? 'Bảng điều khiển') ?></span>
                </div>
            </div>

            <div class="admin-topbar-right">
                <a href="<?= BASE_URL ?>" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill px-3 d-none d-sm-inline-flex align-items-center gap-1">
                    <i class="bi bi-shop"></i> Xem trang bán hàng
                </a>
                <div class="dropdown">
                    <button class="btn btn-light rounded-pill border dropdown-toggle d-flex align-items-center gap-2 py-1 px-3" type="button" data-bs-toggle="dropdown">
                        <span class="badge bg-danger rounded-pill">Admin</span>
                        <span class="fw-semibold small d-none d-sm-inline"><?= htmlspecialchars($_SESSION['user']['ho_ten'] ?? 'Quản trị viên') ?></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                        <li><h6 class="dropdown-header">Tài khoản quản trị</h6></li>
                        <li><span class="dropdown-item-text text-muted small"><?= htmlspecialchars($_SESSION['user']['email'] ?? '') ?></span></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>" target="_blank"><i class="bi bi-globe me-2"></i>Vào trang chủ bán hàng</a></li>
                        <li>
                            <form method="post" action="<?= BASE_URL ?>?action=logout" class="d-inline">
                                <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="bi bi-box-arrow-right me-2"></i>Đăng xuất
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        <!-- Main Workspace -->
        <main class="admin-content-area">
            <!-- Flash Message Alerts -->
            <?php $successMsg = get_flash('success'); if ($successMsg): ?>
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm d-flex align-items-center gap-2" role="alert">
                    <i class="bi bi-check-circle-fill fs-5"></i>
                    <div><?= htmlspecialchars($successMsg, ENT_QUOTES, 'UTF-8') ?></div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php $errorMsg = get_flash('error'); if ($errorMsg): ?>
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm d-flex align-items-center gap-2" role="alert">
                    <i class="bi bi-exclamation-octagon-fill fs-5"></i>
                    <div><?= htmlspecialchars($errorMsg, ENT_QUOTES, 'UTF-8') ?></div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php $infoMsg = get_flash('info'); if ($infoMsg): ?>
                <div class="alert alert-info alert-dismissible fade show border-0 shadow-sm d-flex align-items-center gap-2" role="alert">
                    <i class="bi bi-info-circle-fill fs-5"></i>
                    <div><?= htmlspecialchars($infoMsg, ENT_QUOTES, 'UTF-8') ?></div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- Render specific sub-view -->
            <?php
            $viewFile = PATH_VIEW . 'admin/' . $adminView . '.php';
            if (file_exists($viewFile)) {
                require $viewFile;
            } else {
                echo '<div class="alert alert-warning">Không tìm thấy file view: ' . htmlspecialchars($adminView) . '</div>';
            }
            ?>
        </main>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Admin JS -->
    <script src="<?= BASE_URL ?>assets/js/admin.js"></script>
</body>
</html>
