<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h1 class="h3 fw-bold text-dark mb-1">Quản lý Đơn hàng</h1>
        <p class="text-muted mb-0">Theo dõi, duyệt đơn và cập nhật tiến độ giao hàng cho khách.</p>
    </div>
</div>

<!-- Status Tabs -->
<ul class="nav nav-pills mb-3 gap-2">
    <li class="nav-item">
        <a class="nav-link <?= $currentStatus === 'all' ? 'active' : 'bg-white border text-dark' ?>" 
           href="<?= BASE_URL ?>?action=admin/orders&status=all<?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">
            Tất cả đơn <span class="badge bg-secondary ms-1"><?= $statusCounts['all'] ?? 0 ?></span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $currentStatus === 'cho_xac_nhan' ? 'active bg-warning text-dark fw-bold' : 'bg-white border text-dark' ?>" 
           href="<?= BASE_URL ?>?action=admin/orders&status=cho_xac_nhan<?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">
            <i class="bi bi-clock-history me-1"></i>Chờ xác nhận 
            <span class="badge bg-danger ms-1"><?= $statusCounts['cho_xac_nhan'] ?? 0 ?></span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $currentStatus === 'dang_giao' ? 'active bg-primary' : 'bg-white border text-dark' ?>" 
           href="<?= BASE_URL ?>?action=admin/orders&status=dang_giao<?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">
            <i class="bi bi-truck me-1"></i>Đang giao 
            <span class="badge bg-light text-dark ms-1"><?= $statusCounts['dang_giao'] ?? 0 ?></span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $currentStatus === 'hoan_thanh' ? 'active bg-success' : 'bg-white border text-dark' ?>" 
           href="<?= BASE_URL ?>?action=admin/orders&status=hoan_thanh<?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">
            <i class="bi bi-check-circle me-1"></i>Hoàn thành 
            <span class="badge bg-light text-dark ms-1"><?= $statusCounts['hoan_thanh'] ?? 0 ?></span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $currentStatus === 'huy' ? 'active bg-danger' : 'bg-white border text-dark' ?>" 
           href="<?= BASE_URL ?>?action=admin/orders&status=huy<?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">
            <i class="bi bi-x-circle me-1"></i>Đã hủy 
            <span class="badge bg-light text-dark ms-1"><?= $statusCounts['huy'] ?? 0 ?></span>
        </a>
    </li>
</ul>

<!-- Search Bar -->
<div class="admin-filter-bar">
    <form method="get" action="<?= BASE_URL ?>" class="row g-2 align-items-center">
        <input type="hidden" name="action" value="admin/orders">
        <input type="hidden" name="status" value="<?= htmlspecialchars($currentStatus) ?>">

        <div class="col-md-9">
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                <input type="text" name="search" class="form-control border-start-0 ps-0" 
                       placeholder="Tìm theo mã đơn (#ID), tên người nhận, hoặc số điện thoại..." 
                       value="<?= htmlspecialchars($search) ?>">
            </div>
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-primary flex-grow-1">
                <i class="bi bi-search"></i> Tìm kiếm
            </button>
            <?php if (!empty($search)): ?>
                <a href="<?= BASE_URL ?>?action=admin/orders&status=<?= htmlspecialchars($currentStatus) ?>" class="btn btn-outline-secondary">
                    <i class="bi bi-x-lg"></i>
                </a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Orders Table -->
<div class="admin-card">
    <div class="admin-card-header">
        <h2 class="admin-card-title">
            <i class="bi bi-receipt-cutoff text-primary"></i>
            Danh sách Đơn hàng (<?= count($orders) ?> đơn)
        </h2>
    </div>
    <div class="table-responsive">
        <table class="table admin-table mb-0">
            <thead>
                <tr>
                    <th style="width: 70px;">Mã đơn</th>
                    <th>Người nhận & SĐT</th>
                    <th>Địa chỉ giao</th>
                    <th>Thời gian</th>
                    <th>Thanh toán</th>
                    <th>Tổng tiền</th>
                    <th>Trạng thái đơn</th>
                    <th class="text-end" style="width: 80px;">Chi tiết</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($orders)): ?>
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-clipboard-x fs-1 d-block mb-2 text-secondary"></i>
                            Không có đơn hàng nào trong mục này.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td>
                                <a href="<?= BASE_URL ?>?action=admin/orders/detail&id=<?= $order['id'] ?>" class="fw-bold text-decoration-none text-primary">
                                    <?= !empty($order['ma_don']) ? htmlspecialchars($order['ma_don']) : '#' . $order['id'] ?>
                                </a>
                            </td>
                            <td>
                                <div class="fw-bold text-dark"><?= htmlspecialchars($order['ten_khach_hang'] ?? $order['ten_nguoi_nhan'] ?? $order['ho_ten'] ?? 'Khách hàng') ?></div>
                                <div class="small text-muted"><i class="bi bi-telephone me-1"></i><?= htmlspecialchars($order['sdt_khach_hang'] ?? $order['sdt_nhan'] ?? $order['sdt_nguoidung'] ?? '') ?></div>
                            </td>
                            <td>
                                <small class="text-muted text-truncate d-inline-block" style="max-width: 220px;" title="<?= htmlspecialchars($order['dia_chi_giao_hang'] ?? $order['dia_chi_chi_tiet'] ?? '') ?>">
                                    <?= htmlspecialchars($order['dia_chi_giao_hang'] ?? $order['dia_chi_chi_tiet'] ?? 'Chưa cập nhật') ?>
                                </small>
                            </td>
                            <td>
                                <small class="text-muted"><?= date('H:i - d/m/Y', strtotime($order['ngay_dat'])) ?></small>
                            </td>
                            <td>
                                <?php if (($order['phuong_thuc'] ?? '') === 'chuyen_khoan'): ?>
                                    <span class="badge bg-info-subtle text-info border border-info-subtle d-block mb-1">
                                        <i class="bi bi-credit-card me-1"></i>Chuyển khoản
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-secondary-subtle text-secondary border d-block mb-1">
                                        <i class="bi bi-cash me-1"></i>Tiền mặt (COD)
                                    </span>
                                <?php endif; ?>

                                <?php if (($order['trang_thai_tt'] ?? '') === 'da_tt'): ?>
                                    <span class="badge bg-success-subtle text-success small">Đã thanh toán</span>
                                <?php else: ?>
                                    <span class="badge bg-warning-subtle text-warning small">Chưa thanh toán</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong class="text-dark fs-6">
                                    <?= format_price($order['tong_tien'] + $order['phi_giao_hang']) ?>
                                </strong>
                            </td>
                            <td>
                                <form method="post" action="<?= BASE_URL ?>?action=admin/orders/update-status" class="d-inline-block">
                                    <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="hidden" name="id" value="<?= $order['id'] ?>">
                                    <select name="trang_thai" class="form-select form-select-sm" 
                                            onchange="if(confirm('Cập nhật trạng thái đơn #<?= $order['id'] ?>?')) { this.form.submit(); } else { this.value='<?= $order['trang_thai'] ?>'; }"
                                            style="font-size: 0.82rem; font-weight: 600;">
                                        <option value="cho_xac_nhan" <?= $order['trang_thai'] === 'cho_xac_nhan' ? 'selected' : '' ?>>⏳ Chờ xác nhận</option>
                                        <option value="dang_giao" <?= $order['trang_thai'] === 'dang_giao' ? 'selected' : '' ?>>🚚 Đang giao</option>
                                        <option value="hoan_thanh" <?= $order['trang_thai'] === 'hoan_thanh' ? 'selected' : '' ?>>✅ Hoàn thành</option>
                                        <option value="huy" <?= $order['trang_thai'] === 'huy' ? 'selected' : '' ?>>❌ Hủy đơn</option>
                                    </select>
                                </form>
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
