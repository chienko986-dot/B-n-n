<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h1 class="h3 fw-bold text-dark mb-1">Quản lý Danh mục Món ăn</h1>
        <p class="text-muted mb-0">Phân loại món ăn theo danh mục giúp thực khách dễ dàng tìm kiếm.</p>
    </div>
</div>

<div class="row g-4">
    <!-- Form thêm mới danh mục (4 cols) -->
    <div class="col-lg-4">
        <div class="admin-card">
            <div class="admin-card-header">
                <h2 class="admin-card-title">
                    <i class="bi bi-plus-circle-fill text-primary"></i>
                    Thêm danh mục mới
                </h2>
            </div>
            <div class="admin-card-body">
                <form method="post" action="<?= BASE_URL ?>?action=admin/categories/create">
                    <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                    
                    <div class="mb-3">
                        <label for="ten_danh_muc" class="form-label fw-semibold">Tên danh mục <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="ten_danh_muc" name="ten_danh_muc" required
                               placeholder="Ví dụ: Cơm trưa, Đồ uống, Tráng miệng...">
                    </div>

                    <div class="mb-3">
                        <label for="mo_ta" class="form-label fw-semibold">Mô tả ngắn</label>
                        <textarea class="form-control" id="mo_ta" name="mo_ta" rows="3"
                                  placeholder="Mô tả về các món trong danh mục này..."></textarea>
                    </div>

                    <div class="form-check form-switch mb-4">
                        <input class="form-check-input" type="checkbox" role="switch" id="trang_thai" name="trang_thai" value="1" checked>
                        <label class="form-check-label fw-semibold" for="trang_thai">Hoạt động (Hiển thị)</label>
                    </div>

                    <button type="submit" class="btn btn-admin-primary w-100">
                        <i class="bi bi-plus-lg me-1"></i> Thêm danh mục
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Bảng danh sách danh mục (8 cols) -->
    <div class="col-lg-8">
        <div class="admin-card">
            <div class="admin-card-header">
                <h2 class="admin-card-title">
                    <i class="bi bi-grid-fill text-primary"></i>
                    Danh sách Danh mục (<?= count($categories) ?> danh mục)
                </h2>
            </div>
            <div class="table-responsive">
                <table class="table admin-table mb-0">
                    <thead>
                        <tr>
                            <th style="width: 50px;">#ID</th>
                            <th>Tên danh mục</th>
                            <th>Mô tả</th>
                            <th class="text-center">Số món</th>
                            <th>Trạng thái</th>
                            <th class="text-end" style="width: 100px;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($categories)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    Chưa có danh mục nào. Hãy thêm danh mục đầu tiên ở bên trái!
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($categories as $cat): ?>
                                <tr>
                                    <td class="text-muted fw-bold">#<?= $cat['id'] ?></td>
                                    <td>
                                        <strong class="text-dark"><?= htmlspecialchars($cat['ten_danh_muc']) ?></strong>
                                    </td>
                                    <td>
                                        <small class="text-muted text-truncate d-inline-block" style="max-width: 250px;">
                                            <?= htmlspecialchars($cat['mo_ta'] ?? '') ?>
                                        </small>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2">
                                            <?= (int) ($cat['so_luong_mon'] ?? 0) ?> món
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($cat['trang_thai'] == 1): ?>
                                            <span class="badge bg-success-subtle text-success">Đang dùng</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Tạm ẩn</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-inline-flex gap-1">
                                            <button type="button" class="btn-action edit" 
                                                    data-bs-toggle="modal" data-bs-target="#editCategoryModal"
                                                    data-id="<?= $cat['id'] ?>"
                                                    data-name="<?= htmlspecialchars($cat['ten_danh_muc'], ENT_QUOTES) ?>"
                                                    data-desc="<?= htmlspecialchars($cat['mo_ta'] ?? '', ENT_QUOTES) ?>"
                                                    data-status="<?= $cat['trang_thai'] ?>"
                                                    title="Chỉnh sửa danh mục">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <form method="post" action="<?= BASE_URL ?>?action=admin/categories/delete" 
                                                  onsubmit="return confirm('Bạn có chắc chắn muốn xóa danh mục: <?= htmlspecialchars($cat['ten_danh_muc'], ENT_QUOTES) ?>?');" class="d-inline">
                                                <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                                                <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                                                <button type="submit" class="btn-action delete" title="Xóa danh mục">
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
    </div>
</div>

<!-- Modal Chỉnh Sửa Danh Mục -->
<div class="modal fade" id="editCategoryModal" tabindex="-1" aria-labelledby="editCatModalTitle" aria-hidden="true">
    <div class="modal-dialog">
        <form method="post" action="<?= BASE_URL ?>?action=admin/categories/edit" class="modal-content">
            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="id" id="editModalCatId">

            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="editCatModalTitle">
                    <i class="bi bi-pencil-square text-primary me-2"></i>Chỉnh sửa Danh mục
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="editModalCatName" class="form-label fw-semibold">Tên danh mục <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="editModalCatName" name="ten_danh_muc" required>
                </div>
                <div class="mb-3">
                    <label for="editModalCatDesc" class="form-label fw-semibold">Mô tả</label>
                    <textarea class="form-control" id="editModalCatDesc" name="mo_ta" rows="3"></textarea>
                </div>
                <div class="form-check form-switch mb-2">
                    <input class="form-check-input" type="checkbox" role="switch" id="editModalCatStatus" name="trang_thai" value="1">
                    <label class="form-check-label fw-semibold" for="editModalCatStatus">Đang hiển thị</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Đóng</button>
                <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const editModal = document.getElementById('editCategoryModal');
    if (editModal) {
        editModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const id = button.getAttribute('data-id');
            const name = button.getAttribute('data-name');
            const desc = button.getAttribute('data-desc');
            const status = button.getAttribute('data-status');

            document.getElementById('editModalCatId').value = id;
            document.getElementById('editModalCatName').value = name;
            document.getElementById('editModalCatDesc').value = desc;
            document.getElementById('editModalCatStatus').checked = (status == '1');
        });
    }
});
</script>
