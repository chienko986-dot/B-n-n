<div class="d-flex align-items-center justify-content-between gap-3 mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>?action=admin/dishes" class="text-decoration-none">Món ăn</a></li>
                <li class="breadcrumb-item active"><?= $isEdit ? 'Chỉnh sửa' : 'Thêm mới' ?></li>
            </ol>
        </nav>
        <h1 class="h3 fw-bold text-dark mb-0">
            <?= $isEdit ? 'Chỉnh sửa: ' . htmlspecialchars($dish['ten_mon']) : 'Thêm món ăn mới vào thực đơn' ?>
        </h1>
    </div>
    <a href="<?= BASE_URL ?>?action=admin/dishes" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1">
        <i class="bi bi-arrow-left"></i> Quay lại
    </a>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data" action="<?= BASE_URL ?>?action=admin/dishes/<?= $isEdit ? 'edit&id=' . (int)$dish['id'] : 'create' ?>">
    <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">

    <div class="row g-4">
        <!-- Thông tin chính -->
        <div class="col-lg-8">
            <div class="admin-card">
                <div class="admin-card-header">
                    <h2 class="admin-card-title">
                        <i class="bi bi-info-circle-fill text-primary"></i>
                        Thông tin chi tiết món ăn
                    </h2>
                </div>
                <div class="admin-card-body">
                    <div class="mb-3">
                        <label for="ten_mon" class="form-label fw-semibold">Tên món ăn <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="ten_mon" name="ten_mon" required
                               placeholder="Ví dụ: Phở bò đặc biệt, Cơm tấm sườn nướng..."
                               value="<?= htmlspecialchars($dish['ten_mon'] ?? '') ?>">
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="danhmuc_id" class="form-label fw-semibold">Danh mục <span class="text-danger">*</span></label>
                            <select class="form-select" id="danhmuc_id" name="danhmuc_id" required>
                                <option value="">-- Chọn danh mục món --</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" <?= ($dish['danhmuc_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['ten_danh_muc']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="gia" class="form-label fw-semibold">Giá bán (VNĐ) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="gia" name="gia" min="0" step="1000" required
                                       placeholder="Ví dụ: 45000"
                                       value="<?= htmlspecialchars($dish['gia'] ?? '') ?>">
                                <span class="input-group-text">đ</span>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="gia_goc" class="form-label fw-semibold">Giá gốc (VNĐ)</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="gia_goc" name="gia_goc" min="0" step="1000"
                                       placeholder="Để trống nếu không khuyến mãi"
                                       value="<?= htmlspecialchars($dish['gia_goc'] ?? '') ?>">
                                <span class="input-group-text">đ</span>
                            </div>
                            <div class="form-text">Điền giá gốc &gt; giá bán để hiển thị khuyến mãi trên trang món.</div>
                        </div>
                        <div class="col-md-6">
                            <label for="thoi_gian_chuan_bi" class="form-label fw-semibold">Thời gian chuẩn bị</label>
                            <input type="text" class="form-control" id="thoi_gian_chuan_bi" name="thoi_gian_chuan_bi"
                                   placeholder="Ví dụ: 15 - 20 phút"
                                   value="<?= htmlspecialchars($dish['thoi_gian_chuan_bi'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="khau_phan" class="form-label fw-semibold">Khẩu phần ăn</label>
                            <input type="text" class="form-control" id="khau_phan" name="khau_phan"
                                   placeholder="Ví dụ: Phần cho 1 người"
                                   value="<?= htmlspecialchars($dish['khau_phan'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="so_luong_ton" class="form-label fw-semibold">Số lượng trong kho</label>
                            <input type="number" class="form-control" id="so_luong_ton" name="so_luong_ton" min="0"
                                   placeholder="50"
                                   value="<?= htmlspecialchars($dish['so_luong_ton'] ?? 50) ?>">
                            <div class="form-text">Số lượng phần ăn có thể phục vụ mỗi ngày.</div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="mo_ta" class="form-label fw-semibold">Mô tả món ăn</label>
                        <textarea class="form-control" id="mo_ta" name="mo_ta" rows="3"
                                  placeholder="Mô tả hương vị, nguyên liệu chính để khách hàng dễ lựa chọn..."><?= htmlspecialchars($dish['mo_ta'] ?? '') ?></textarea>
                    </div>

                    <div class="mb-0">
                        <label for="thanh_phan" class="form-label fw-semibold">Thành phần nguyên liệu</label>
                        <textarea class="form-control" id="thanh_phan" name="thanh_phan" rows="3"
                                  placeholder="VD: Cơm tấm, sườn nướng heo than hoa, chả trứng, mỡ hành, nước mắm pha..."><?= htmlspecialchars($dish['thanh_phan'] ?? '') ?></textarea>
                        <div class="form-text">Hiển thị ở mục "Thành phần" trên trang chi tiết món ăn.</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cột Phụ: Ảnh & Trạng thái -->
        <div class="col-lg-4">
            <!-- Box Ảnh -->
            <div class="admin-card mb-4">
                <div class="admin-card-header">
                    <h2 class="admin-card-title">
                        <i class="bi bi-image-fill text-primary"></i>
                        Hình ảnh món ăn
                    </h2>
                </div>
                <div class="admin-card-body text-center">
                    <div class="d-flex justify-content-center mb-3">
                        <div class="image-preview-wrapper shadow-sm">
                            <?php $currentImgUrl = !empty($dish['hinh_anh']) ? dish_image_url($dish['hinh_anh']) : ''; ?>
                            <img id="imagePreview" src="<?= htmlspecialchars($currentImgUrl) ?>" 
                                 class="<?= empty($currentImgUrl) ? 'd-none' : '' ?>" alt="Xem trước">
                            <div id="imagePreviewPlaceholder" class="text-muted p-2 <?= !empty($currentImgUrl) ? 'd-none' : '' ?>">
                                <i class="bi bi-card-image fs-1 d-block mb-1"></i>
                                <span class="small">Chưa có ảnh</span>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3 text-start">
                        <label for="imageFileInput" class="form-label fw-semibold small">Tải ảnh từ máy tính:</label>
                        <input class="form-control form-control-sm" type="file" id="imageFileInput" name="hinh_anh" accept="image/*">
                    </div>

                    <div class="mb-2 text-start">
                        <label for="hinh_anh_url" class="form-label fw-semibold small">Hoặc đường dẫn ảnh:</label>
                        <input type="text" class="form-control form-control-sm" id="hinh_anh_url" name="hinh_anh_url" 
                               placeholder="assets/images/dishes/pho-bo.jpg hoặc link URL"
                               value="<?= htmlspecialchars($dish['hinh_anh'] ?? '') ?>">
                    </div>

                    <!-- Gợi ý nhanh các ảnh mẫu sẵn có trong dự án -->
                    <div class="text-start mt-3 pt-2 border-top">
                        <label class="form-label fw-semibold small text-muted">Chọn nhanh ảnh mẫu sẵn có:</label>
                        <div class="d-flex flex-wrap gap-1">
                            <?php 
                            $sampleImgs = [
                                'Cơm tấm' => 'assets/images/dishes/com-tam.jpg',
                                'Phở bò' => 'assets/images/dishes/pho-bo.jpg',
                                'Burger' => 'assets/images/dishes/burger.jpg',
                                'Pizza' => 'assets/images/dishes/pizza.jpg',
                                'Bún chả' => 'assets/images/dishes/bun-cha.jpg',
                                'Gà rán' => 'assets/images/dishes/ga-ran.jpg',
                                'Flan' => 'assets/images/dishes/flan.jpg',
                                'Trà đào' => 'assets/images/dishes/tra-dao.jpg',
                            ];
                            foreach ($sampleImgs as $sName => $sPath):
                            ?>
                                <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2" style="font-size: 0.75rem;"
                                        onclick="document.getElementById('hinh_anh_url').value='<?= $sPath ?>'; document.getElementById('imagePreview').src='<?= BASE_URL . $sPath ?>'; document.getElementById('imagePreview').classList.remove('d-none'); document.getElementById('imagePreviewPlaceholder').classList.add('d-none');">
                                    <?= $sName ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Box Trạng thái & Lưu -->
            <div class="admin-card">
                <div class="admin-card-header">
                    <h2 class="admin-card-title">
                        <i class="bi bi-toggle-on text-primary"></i>
                        Trạng thái hiển thị
                    </h2>
                </div>
                <div class="admin-card-body">
                    <div class="form-check form-switch mb-4">
                        <input class="form-check-input" type="checkbox" role="switch" id="trang_thai" name="trang_thai" value="1"
                               <?= (!isset($dish['trang_thai']) || $dish['trang_thai'] == 1) ? 'checked' : '' ?>>
                        <label class="form-check-label fw-semibold" for="trang_thai">Đang phục vụ (Bật bán)</label>
                        <div class="form-text small">Nếu tắt, món ăn sẽ ẩn khỏi thực đơn của khách hàng.</div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-admin-primary">
                            <i class="bi bi-save2 me-1"></i> <?= $isEdit ? 'Cập nhật món ăn' : 'Lưu món ăn' ?>
                        </button>
                        <a href="<?= BASE_URL ?>?action=admin/dishes" class="btn btn-outline-secondary">Hủy bỏ</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
