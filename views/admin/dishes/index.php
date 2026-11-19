<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h1 class="h3 fw-bold text-dark mb-1">Quản lý Món ăn</h1>
        <p class="text-muted mb-0">Quản lý danh sách các món ăn, hình ảnh, giá cả và tình trạng kho hàng.</p>
    </div>
    <a href="<?= BASE_URL ?>?action=admin/dishes/create" class="btn btn-admin-primary d-inline-flex align-items-center gap-2">
        <i class="bi bi-plus-circle-fill"></i>
        <span>Thêm món ăn mới</span>
    </a>
</div>

<!-- Filter Bar -->
<div class="admin-filter-bar">
    <form method="get" action="<?= BASE_URL ?>" class="row g-2 align-items-center">
        <input type="hidden" name="action" value="admin/dishes">
        <div class="col-md-4">
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Tìm theo tên món hoặc mô tả..." value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
            </div>
        </div>
        <div class="col-md-3">
            <select name="category" class="form-select">
                <option value="">-- Tất cả danh mục --</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= ($filters['danhmuc_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['ten_danh_muc']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <select name="status" class="form-select">
                <option value="">-- Tất cả trạng thái --</option>
                <option value="1" <?= ($filters['trang_thai'] ?? '') === '1' ? 'selected' : '' ?>>Đang bán</option>
                <option value="0" <?= ($filters['trang_thai'] ?? '') === '0' ? 'selected' : '' ?>>Tạm ngừng</option>
            </select>
        </div>
        <div class="col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-primary flex-grow-1">
                <i class="bi bi-filter"></i> Lọc
            </button>
            <?php if (!empty($filters['search']) || !empty($filters['danhmuc_id']) || ($filters['trang_thai'] !== '')): ?>
                <a href="<?= BASE_URL ?>?action=admin/dishes" class="btn btn-outline-secondary" title="Đặt lại bộ lọc">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Dishes Table Card -->
<div class="admin-card">
    <div class="admin-card-header">
        <h2 class="admin-card-title">
            <i class="bi bi-egg-fried text-primary"></i>
            Danh sách Món ăn (<?= count($dishes) ?> món)
        </h2>
    </div>
    <div class="table-responsive">
        <table class="table admin-table mb-0">
            <thead>
                <tr>
                    <th style="width: 50px;">#ID</th>
                    <th style="width: 70px;">Hình ảnh</th>
                    <th>Tên món ăn</th>
                    <th>Danh mục</th>
                    <th>Giá bán</th>
                    <th>Kho hàng</th>
                    <th>Trạng thái</th>
                    <th class="text-end" style="width: 110px;">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($dishes)): ?>
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
                            Không tìm thấy món ăn nào phù hợp với điều kiện tìm kiếm.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($dishes as $dish): ?>
                        <tr>
                            <td class="text-muted fw-bold">#<?= $dish['id'] ?></td>
                            <td>
                                <img src="<?= htmlspecialchars(dish_image_url($dish['hinh_anh'])) ?>" 
                                     alt="<?= htmlspecialchars($dish['ten_mon']) ?>" 
                                     class="dish-thumb"
                                     onerror="this.src='<?= BASE_URL ?>assets/images/hero-bowl.jpg'">
                            </td>
                            <td>
                                <div class="fw-bold text-dark"><?= htmlspecialchars($dish['ten_mon']) ?></div>
                                <?php if (!empty($dish['mo_ta'])): ?>
                                    <small class="text-muted text-truncate d-inline-block" style="max-width: 280px;">
                                        <?= htmlspecialchars($dish['mo_ta']) ?>
                                    </small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    <?= htmlspecialchars($dish['ten_danh_muc'] ?? 'Chưa phân loại') ?>
                                </span>
                            </td>
                            <td>
                                <strong class="text-primary"><?= format_price($dish['gia']) ?></strong>
                            </td>
                            <td>
                                <?php if ($dish['so_luong_ton'] <= 5): ?>
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                        <?= (int) $dish['so_luong_ton'] ?> (Sắp hết)
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-secondary-subtle text-secondary border">
                                        <?= (int) $dish['so_luong_ton'] ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($dish['trang_thai'] == 1): ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">
                                        <i class="bi bi-check-circle me-1"></i>Đang bán
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-secondary text-white">
                                        <i class="bi bi-pause-circle me-1"></i>Tạm ngừng
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    <a href="<?= BASE_URL ?>?action=admin/dishes/edit&id=<?= $dish['id'] ?>" class="btn-action edit" title="Chỉnh sửa món">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form method="post" action="<?= BASE_URL ?>?action=admin/dishes/delete" onsubmit="return confirm('Bạn có chắc chắn muốn xóa món: <?= htmlspecialchars($dish['ten_mon'], ENT_QUOTES) ?>?');" class="d-inline">
                                        <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                                        <input type="hidden" name="id" value="<?= $dish['id'] ?>">
                                        <button type="submit" class="btn-action delete" title="Xóa món">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
