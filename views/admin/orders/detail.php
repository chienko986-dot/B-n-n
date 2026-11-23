<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>?action=admin/orders" class="text-decoration-none">Đơn hàng</a></li>
                <li class="breadcrumb-item active">#<?= $order['id'] ?></li>
            </ol>
        </nav>
        <div class="d-flex align-items-center gap-3">
            <h1 class="h3 fw-bold text-dark mb-0">Đơn hàng <?= !empty($order['ma_don']) ? htmlspecialchars($order['ma_don']) : '#' . $order['id'] ?></h1>
            <?= order_status_badge($order['trang_thai']) ?>
        </div>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1" onclick="window.print();">
            <i class="bi bi-printer"></i> In hóa đơn
        </button>
        <a href="<?= BASE_URL ?>?action=admin/orders" class="btn btn-outline-primary d-inline-flex align-items-center gap-1">
            <i class="bi bi-arrow-left"></i> Quay lại danh sách
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- Cột chính: Danh sách món ăn & Hóa đơn (8 cols) -->
    <div class="col-lg-8">
        <div class="admin-card mb-4">
            <div class="admin-card-header">
                <h2 class="admin-card-title">
                    <i class="bi bi-basket2-fill text-primary"></i>
                    Các món ăn trong đơn
                </h2>
                <span class="badge bg-light text-dark border"><?= count($items) ?> món</span>
            </div>
            <div class="table-responsive">
                <table class="table admin-table mb-0">
                    <thead>
                        <tr>
                            <th style="width: 60px;">Ảnh</th>
                            <th>Tên món ăn</th>
                            <th class="text-end">Đơn giá</th>
                            <th class="text-center">Số lượng</th>
                            <th class="text-end">Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($items)): ?>
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">
                                    Không có món ăn nào trong đơn hàng này.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($items as $item): ?>
                                <tr>
                                    <td>
                                        <img src="<?= htmlspecialchars(dish_image_url($item['hinh_anh'])) ?>" 
                                             alt="<?= htmlspecialchars($item['ten_mon']) ?>" 
                                             class="dish-thumb"
                                             onerror="this.src='<?= BASE_URL ?>assets/images/hero-bowl.jpg'">
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($item['ten_mon']) ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($item['ten_danh_muc'] ?? '') ?></small>
                                        <?php if (!empty($item['size_ten']) || !empty($item['topping_list']) || !empty($item['ghi_chu'])): ?>
                                            <div class="small mt-1">
                                                <?php if (!empty($item['size_ten'])): ?>
                                                    <span class="badge bg-light text-dark border me-1">
                                                        <i class="bi bi-rulers"></i> <?= htmlspecialchars($item['size_ten']) ?>
                                                    </span>
                                                <?php endif; ?>
                                                <?php if (!empty($item['topping_list'])): ?>
                                                    <span class="badge bg-light text-dark border me-1">
                                                        <i class="bi bi-plus-circle"></i> <?= htmlspecialchars($item['topping_list']) ?>
                                                    </span>
                                                <?php endif; ?>
                                                <?php if (!empty($item['ghi_chu'])): ?>
                                                    <div class="text-muted fst-italic mt-1">
                                                        <i class="bi bi-sticky"></i> <?= htmlspecialchars($item['ghi_chu']) ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end"><?= format_price($item['don_gia']) ?></td>
                                    <td class="text-center">
                                        <span class="badge bg-secondary-subtle text-dark border px-2 py-1">
                                            x<?= (int) $item['so_luong'] ?>
                                        </span>
                                    </td>
                                    <td class="text-end fw-bold text-dark"><?= format_price($item['thanh_tien']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="p-4 bg-light border-top">
                <div class="row justify-content-end">
                    <div class="col-md-6 col-lg-5">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Tiền món:</span>
                            <span class="fw-semibold"><?= format_price($order['tong_tien']) ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Phí giao hàng:</span>
                            <span class="fw-semibold"><?= format_price($order['phi_giao_hang']) ?></span>
                        </div>
                        <hr class="my-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold fs-6">Tổng thanh toán:</span>
                            <span class="fw-bold fs-5 text-primary"><?= format_price($order['tong_tien'] + $order['phi_giao_hang']) ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Box Ghi chú đơn hàng -->
        <?php if (!empty($order['ghi_chu']) || !empty($order['ghi_chu_diachi'])): ?>
            <div class="admin-card">
                <div class="admin-card-header">
                    <h2 class="admin-card-title">
                        <i class="bi bi-chat-left-text-fill text-warning"></i>
                        Ghi chú từ khách hàng
                    </h2>
                </div>
                <div class="admin-card-body">
                    <?php if (!empty($order['ghi_chu'])): ?>
                        <p class="mb-2"><strong>Ghi chú món:</strong> <?= htmlspecialchars($order['ghi_chu']) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($order['ghi_chu_diachi'])): ?>
                        <p class="mb-0 text-muted"><strong>Ghi chú giao hàng:</strong> <?= htmlspecialchars($order['ghi_chu_diachi']) ?></p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Cột phụ: Xử lý trạng thái & Thông tin giao hàng (4 cols) -->
    <div class="col-lg-4">
        <!-- Box Cập nhật trạng thái -->
        <div class="admin-card mb-4 border-primary">
            <div class="admin-card-header bg-primary-subtle">
                <h2 class="admin-card-title text-primary">
                    <i class="bi bi-arrow-repeat"></i>
                    Cập nhật trạng thái
                </h2>
            </div>
            <div class="admin-card-body">
                <form method="post" action="<?= BASE_URL ?>?action=admin/orders/update-status">
                    <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="id" value="<?= $order['id'] ?>">
                    <input type="hidden" name="redirect_detail" value="1">

                    <div class="mb-3">
                        <label for="trang_thai" class="form-label fw-semibold">Trạng thái đơn hàng:</label>
                        <select name="trang_thai" id="trang_thai" class="form-select">
                            <option value="cho_xac_nhan" <?= $order['trang_thai'] === 'cho_xac_nhan' ? 'selected' : '' ?>>⏳ Chờ xác nhận</option>
                            <option value="dang_giao" <?= $order['trang_thai'] === 'dang_giao' ? 'selected' : '' ?>>🚚 Đang giao hàng</option>
                            <option value="hoan_thanh" <?= $order['trang_thai'] === 'hoan_thanh' ? 'selected' : '' ?>>✅ Đã hoàn thành</option>
                            <option value="huy" <?= $order['trang_thai'] === 'huy' ? 'selected' : '' ?>>❌ Đã hủy đơn</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-check2-circle me-1"></i> Lưu trạng thái
                    </button>
                </form>
            </div>
        </div>

        <!-- Box Thông tin người nhận -->
        <div class="admin-card mb-4">
            <div class="admin-card-header">
                <h2 class="admin-card-title">
                    <i class="bi bi-geo-alt-fill text-danger"></i>
                    Địa chỉ nhận hàng
                </h2>
            </div>
            <div class="admin-card-body">
                <div class="mb-3">
                    <label class="text-muted small d-block">Người nhận hàng:</label>
                    <strong class="text-dark fs-6"><?= htmlspecialchars($order['ten_khach_hang'] ?? $order['ten_nguoi_nhan'] ?? $order['ho_ten'] ?? 'Chưa rõ') ?></strong>
                </div>
                <div class="mb-3">
                    <label class="text-muted small d-block">Số điện thoại:</label>
                    <a href="tel:<?= htmlspecialchars($order['sdt_khach_hang'] ?? $order['sdt_nhan'] ?? '') ?>" class="fw-bold text-primary text-decoration-none">
                        <i class="bi bi-telephone-fill me-1"></i><?= htmlspecialchars($order['sdt_khach_hang'] ?? $order['sdt_nhan'] ?? $order['sdt_nguoidung'] ?? 'Chưa có') ?>
                    </a>
                </div>
                <div class="mb-3">
                    <label class="text-muted small d-block">Địa chỉ giao chi tiết:</label>
                    <div class="p-2 rounded bg-light border text-dark">
                        <?= htmlspecialchars($order['dia_chi_giao_hang'] ?? $order['dia_chi_chi_tiet'] ?? 'Chưa cập nhật') ?>
                    </div>
                </div>
                <div>
                    <label class="text-muted small d-block">Tài khoản đặt đơn:</label>
                    <span class="small"><?= htmlspecialchars($order['ho_ten'] ?? $order['ten_khach_hang'] ?? '') ?> (<?= htmlspecialchars($order['email_khach_hang'] ?? $order['email'] ?? '') ?>)</span>
                </div>
            </div>
        </div>

        <!-- Box Thông tin thanh toán -->
        <div class="admin-card">
            <div class="admin-card-header">
                <h2 class="admin-card-title">
                    <i class="bi bi-credit-card-fill text-success"></i>
                    Thông tin thanh toán
                </h2>
            </div>
            <div class="admin-card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Phương thức:</span>
                    <strong class="text-dark">
                        <?= ($order['phuong_thuc'] ?? '') === 'chuyen_khoan' ? 'Chuyển khoản ngân hàng' : 'Tiền mặt khi nhận hàng (COD)' ?>
                    </strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Tình trạng:</span>
                    <?php if (($order['trang_thai_tt'] ?? '') === 'da_tt'): ?>
                        <span class="badge bg-success-subtle text-success">Đã thanh toán</span>
                    <?php else: ?>
                        <span class="badge bg-warning-subtle text-warning">Chưa thanh toán</span>
                    <?php endif; ?>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Thời gian đặt:</span>
                    <small class="text-muted"><?= date('H:i d/m/Y', strtotime($order['ngay_dat'])) ?></small>
                </div>
            </div>
        </div>
    </div>
</div>
