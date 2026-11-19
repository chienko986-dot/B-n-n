<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h1 class="h3 fw-bold text-dark mb-1">Quản lý Đánh giá & Phản hồi</h1>
        <p class="text-muted mb-0">Xem và quản lý các đánh giá món ăn từ khách hàng sau khi dùng bữa.</p>
    </div>
</div>

<div class="admin-card">
    <div class="admin-card-header">
        <h2 class="admin-card-title">
            <i class="bi bi-star-fill text-warning"></i>
            Danh sách Đánh giá (<?= count($reviews) ?> đánh giá)
        </h2>
    </div>
    <div class="table-responsive">
        <table class="table admin-table mb-0">
            <thead>
                <tr>
                    <th style="width: 50px;">#ID</th>
                    <th style="width: 200px;">Món ăn</th>
                    <th>Khách hàng</th>
                    <th style="width: 130px;">Đánh giá</th>
                    <th>Nội dung nhận xét</th>
                    <th>Thời gian</th>
                    <th class="text-end" style="width: 80px;">Xóa</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($reviews)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-chat-square-dots fs-1 d-block mb-2 text-secondary"></i>
                            Chưa có đánh giá nào từ khách hàng.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($reviews as $rev): ?>
                        <tr>
                            <td class="text-muted fw-bold">#<?= $rev['id'] ?></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <img src="<?= htmlspecialchars(dish_image_url($rev['hinh_anh'])) ?>" 
                                         alt="<?= htmlspecialchars($rev['ten_mon'] ?? '') ?>" 
                                         class="dish-thumb"
                                         onerror="this.src='<?= BASE_URL ?>assets/images/hero-bowl.jpg'">
                                    <strong class="text-dark small text-truncate" style="max-width: 140px;">
                                        <?= htmlspecialchars($rev['ten_mon'] ?? 'Món không rõ') ?>
                                    </strong>
                                </div>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark"><?= htmlspecialchars($rev['ten_nguoidung'] ?? 'Khách giấu tên') ?></div>
                                <small class="text-muted"><?= htmlspecialchars($rev['email'] ?? '') ?></small>
                            </td>
                            <td>
                                <div class="text-warning small text-nowrap">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="bi bi-star<?= $i <= $rev['so_sao'] ? '-fill' : '' ?>"></i>
                                    <?php endfor; ?>
                                    <span class="ms-1 fw-bold text-dark">(<?= $rev['so_sao'] ?>/5)</span>
                                </div>
                            </td>
                            <td>
                                <div class="p-2 rounded bg-light border text-dark small" style="max-width: 400px;">
                                    <?= nl2br(htmlspecialchars($rev['binh_luan'] ?? 'Không có nhận xét chữ.')) ?>
                                </div>
                            </td>
                            <td>
                                <small class="text-muted"><?= date('H:i d/m/Y', strtotime($rev['ngay_danh_gia'])) ?></small>
                            </td>
                            <td class="text-end">
                                <form method="post" action="<?= BASE_URL ?>?action=admin/reviews/delete" onsubmit="return confirm('Bạn có chắc muốn xóa đánh giá này?');" class="d-inline">
                                    <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="hidden" name="id" value="<?= $rev['id'] ?>">
                                    <button type="submit" class="btn-action delete" title="Xóa đánh giá vi phạm">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
