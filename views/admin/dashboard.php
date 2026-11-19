<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h1 class="h3 fw-bold text-dark mb-1">Bảng điều khiển kinh doanh</h1>
        <p class="text-muted mb-0">Theo dõi doanh thu, tình trạng đơn hàng và món ăn bán chạy trong ngày.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= BASE_URL ?>?action=admin/dishes/create" class="btn btn-admin-primary d-inline-flex align-items-center gap-2">
            <i class="bi bi-plus-circle-fill"></i>
            <span>Thêm món ăn mới</span>
        </a>
        <a href="<?= BASE_URL ?>?action=admin/seed" onclick="return confirm('Nạp thêm dữ liệu mẫu món ăn và đơn hàng?');" class="btn btn-outline-warning text-dark d-inline-flex align-items-center gap-1">
            <i class="bi bi-magic"></i>
            <span>Nạp dữ liệu mẫu</span>
        </a>
    </div>
</div>

<!-- If empty database, display quick notice banner -->
<?php if (($stats['total_dishes'] ?? 0) === 0): ?>
<div class="alert alert-warning border-0 shadow-sm p-4 rounded-4 mb-4">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="fs-1 text-warning"><i class="bi bi-lightbulb-fill"></i></div>
            <div>
                <h5 class="fw-bold mb-1">Hệ thống chưa có dữ liệu món ăn & đơn hàng!</h5>
                <p class="mb-0 text-muted">Bạn có thể bấm nút bên phải để tự động nạp danh mục, món ăn kèm hình ảnh và đơn hàng mẫu để kiểm tra ngay.</p>
            </div>
        </div>
        <a href="<?= BASE_URL ?>?action=admin/seed" class="btn btn-warning fw-bold text-dark px-4 py-2 text-nowrap rounded-pill">
            <i class="bi bi-lightning-charge-fill me-1"></i> Khởi tạo dữ liệu mẫu ngay
        </a>
    </div>
</div>
<?php endif; ?>

<!-- 1. KPI Metric Cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-card-top">
                <span class="stat-label">Doanh thu hoàn tất</span>
                <div class="stat-icon success">
                    <i class="bi bi-cash-stack"></i>
                </div>
            </div>
            <div class="stat-value text-success"><?= format_price($stats['revenue'] ?? 0) ?></div>
            <div class="stat-subtext"><i class="bi bi-shield-check text-success me-1"></i>Từ các đơn đã hoàn thành</div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-card-top">
                <span class="stat-label">Tổng đơn hàng</span>
                <div class="stat-icon primary">
                    <i class="bi bi-receipt"></i>
                </div>
            </div>
            <div class="stat-value"><?= number_format($stats['total_orders'] ?? 0) ?></div>
            <div class="stat-subtext">
                <span class="text-warning fw-bold"><?= $statusCounts['cho_xac_nhan'] ?? 0 ?></span> đơn đang chờ duyệt
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-card-top">
                <span class="stat-label">Món ăn trong Menu</span>
                <div class="stat-icon warning">
                    <i class="bi bi-egg-fried"></i>
                </div>
            </div>
            <div class="stat-value"><?= number_format($stats['total_dishes'] ?? 0) ?></div>
            <div class="stat-subtext">Thuộc <span class="fw-bold"><?= $stats['total_categories'] ?? 0 ?></span> danh mục món</div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-card-top">
                <span class="stat-label">Khách hàng đăng ký</span>
                <div class="stat-icon info">
                    <i class="bi bi-people"></i>
                </div>
            </div>
            <div class="stat-value"><?= number_format($stats['total_users'] ?? 0) ?></div>
            <div class="stat-subtext">Thành viên hệ thống</div>
        </div>
    </div>
</div>

<!-- 2. Order Status Pills -->
<div class="admin-card mb-4">
    <div class="admin-card-body p-3">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="fw-semibold text-secondary small text-uppercase">
                <i class="bi bi-funnel-fill text-primary me-1"></i> Trạng thái đơn hàng:
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="<?= BASE_URL ?>?action=admin/orders&status=cho_xac_nhan" class="btn btn-sm btn-outline-warning text-dark rounded-pill px-3 py-1">
                    <i class="bi bi-clock-history me-1"></i> Chờ xác nhận: <strong><?= $statusCounts['cho_xac_nhan'] ?? 0 ?></strong>
                </a>
                <a href="<?= BASE_URL ?>?action=admin/orders&status=dang_giao" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1">
                    <i class="bi bi-truck me-1"></i> Đang giao: <strong><?= $statusCounts['dang_giao'] ?? 0 ?></strong>
                </a>
                <a href="<?= BASE_URL ?>?action=admin/orders&status=hoan_thanh" class="btn btn-sm btn-outline-success rounded-pill px-3 py-1">
                    <i class="bi bi-check-circle me-1"></i> Đã hoàn thành: <strong><?= $statusCounts['hoan_thanh'] ?? 0 ?></strong>
                </a>
                <a href="<?= BASE_URL ?>?action=admin/orders&status=huy" class="btn btn-sm btn-outline-danger rounded-pill px-3 py-1">
                    <i class="bi bi-x-circle me-1"></i> Đã hủy: <strong><?= $statusCounts['huy'] ?? 0 ?></strong>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- 3. Main Data Section -->
<div class="row g-4">
    <!-- Recent Orders (8 cols) -->
    <div class="col-lg-8">
        <div class="admin-card h-100">
            <div class="admin-card-header">
                <h2 class="admin-card-title">
                    <i class="bi bi-clock-fill text-primary"></i>
                    Đơn hàng mới nhất
                </h2>
                <a href="<?= BASE_URL ?>?action=admin/orders" class="btn btn-sm btn-link text-decoration-none">
                    Xem tất cả <i class="bi bi-arrow-right"></i>
                </a>
            </div>
            <div class="table-responsive">
                <table class="table admin-table mb-0">
                    <thead>
                        <tr>
                            <th>Mã đơn</th>
                            <th>Khách hàng</th>
                            <th>Thời gian</th>
                            <th>Tổng tiền</th>
                            <th>Trạng thái</th>
                            <th class="text-end">Chi tiết</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recentOrders)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    <i class="bi bi-inbox fs-2 d-block mb-2 text-secondary"></i>
                                    Chưa có đơn hàng nào được ghi nhận.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recentOrders as $order): ?>
                                <tr>
                                    <td>
                                        <a href="<?= BASE_URL ?>?action=admin/orders/detail&id=<?= $order['id'] ?>" class="fw-bold text-decoration-none text-primary">
                                            <?= !empty($order['ma_don']) ? htmlspecialchars($order['ma_don']) : '#' . $order['id'] ?>
                                        </a>
                                    </td>
                                    <td>
                                        <div class="fw-semibold"><?= htmlspecialchars($order['ten_khach_hang'] ?? $order['ten_nguoi_nhan'] ?? $order['ho_ten'] ?? 'Khách vãng lai') ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($order['sdt_khach_hang'] ?? $order['sdt_nhan'] ?? '') ?></small>
                                    </td>
                                    <td>
                                        <small class="text-muted"><?= date('H:i d/m/Y', strtotime($order['ngay_dat'])) ?></small>
                                    </td>
                                    <td class="fw-bold text-dark">
                                        <?= format_price($order['tong_tien'] + $order['phi_giao_hang']) ?>
                                    </td>
                                    <td>
                                        <?= order_status_badge($order['trang_thai']) ?>
                                    </td>
                                    <td class="text-end">
                                        <a href="<?= BASE_URL ?>?action=admin/orders/detail&id=<?= $order['id'] ?>" class="btn-action view" title="Xem chi tiết đơn hàng">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Top Selling Dishes (4 cols) -->
    <div class="col-lg-4">
        <div class="admin-card h-100">
            <div class="admin-card-header">
                <h2 class="admin-card-title">
                    <i class="bi bi-trophy-fill text-warning"></i>
                    Món bán chạy
                </h2>
                <a href="<?= BASE_URL ?>?action=admin/dishes" class="btn btn-sm btn-link text-decoration-none">
                    Quản lý menu
                </a>
            </div>
            <div class="admin-card-body p-0">
                <div class="list-group list-group-flush">
                    <?php if (empty($topDishes)): ?>
                        <div class="p-4 text-center text-muted">
                            Chưa có dữ liệu món bán chạy.
                        </div>
                    <?php else: ?>
                        <?php foreach ($topDishes as $idx => $dish): ?>
                            <div class="list-group-item d-flex align-items-center gap-3 p-3">
                                <div class="position-relative">
                                    <img src="<?= htmlspecialchars(dish_image_url($dish['hinh_anh'])) ?>" 
                                         alt="<?= htmlspecialchars($dish['ten_mon']) ?>" 
                                         class="dish-thumb"
                                         onerror="this.src='<?= BASE_URL ?>assets/images/hero-bowl.jpg'">
                                    <span class="position-absolute top-0 start-0 translate-middle badge rounded-pill bg-dark" style="font-size: 0.65rem;">
                                        #<?= $idx + 1 ?>
                                    </span>
                                </div>
                                <div class="flex-grow-1 min-w-0">
                                    <div class="fw-semibold text-truncate mb-1" title="<?= htmlspecialchars($dish['ten_mon']) ?>">
                                        <?= htmlspecialchars($dish['ten_mon']) ?>
                                    </div>
                                    <div class="small text-muted d-flex justify-content-between align-items-center">
                                        <span class="text-primary fw-semibold"><?= format_price($dish['gia']) ?></span>
                                        <span class="badge bg-light text-dark border">Đã bán: <?= (int)$dish['tong_da_ban'] ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            <div class="p-3 bg-light border-top">
                <a href="<?= BASE_URL ?>?action=admin/dishes/create" class="btn btn-outline-primary btn-sm w-100">
                    <i class="bi bi-plus-lg me-1"></i> Thêm món ăn mới vào menu
                </a>
            </div>
        </div>
    </div>
</div>
