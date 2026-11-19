<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h1 class="h3 fw-bold text-dark mb-1">Quản lý Tài khoản & Phân quyền</h1>
        <p class="text-muted mb-0">Danh sách người dùng đã đăng ký tài khoản trên hệ thống AnLaMe.</p>
    </div>
</div>

<!-- Search Bar -->
<div class="admin-filter-bar">
    <form method="get" action="<?= BASE_URL ?>" class="row g-2 align-items-center">
        <input type="hidden" name="action" value="admin/users">
        <div class="col-md-10">
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                <input type="text" name="search" class="form-control border-start-0 ps-0" 
                       placeholder="Tìm theo họ tên, email hoặc số điện thoại người dùng..." 
                       value="<?= htmlspecialchars($search ?? '') ?>">
            </div>
        </div>
        <div class="col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-primary flex-grow-1">
                <i class="bi bi-search"></i> Tìm kiếm
            </button>
            <?php if (!empty($search)): ?>
                <a href="<?= BASE_URL ?>?action=admin/users" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Users Table -->
<div class="admin-card">
    <div class="admin-card-header">
        <h2 class="admin-card-title">
            <i class="bi bi-people-fill text-primary"></i>
            Danh sách Người dùng (<?= count($users) ?> tài khoản)
        </h2>
    </div>
    <div class="table-responsive">
        <table class="table admin-table mb-0">
            <thead>
                <tr>
                    <th style="width: 60px;">#ID</th>
                    <th>Họ và tên</th>
                    <th>Email</th>
                    <th>Số điện thoại</th>
                    <th>Địa chỉ</th>
                    <th>Vai trò</th>
                    <th>Ngày tham gia</th>
                    <th class="text-end" style="width: 140px;">Phân quyền</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-person-x fs-1 d-block mb-2 text-secondary"></i>
                            Không tìm thấy tài khoản người dùng nào.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td class="text-muted fw-bold">#<?= $u['id'] ?></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="admin-avatar" style="width: 32px; height: 32px; font-size: 0.8rem;">
                                        <?= mb_strtoupper(mb_substr($u['ho_ten'], 0, 1)) ?>
                                    </div>
                                    <strong class="text-dark"><?= htmlspecialchars($u['ho_ten']) ?></strong>
                                </div>
                            </td>
                            <td>
                                <span class="text-primary"><?= htmlspecialchars($u['email']) ?></span>
                            </td>
                            <td><?= htmlspecialchars($u['so_dien_thoai'] ?? 'Chưa cập nhật') ?></td>
                            <td>
                                <small class="text-muted text-truncate d-inline-block" style="max-width: 200px;" title="<?= htmlspecialchars($u['dia_chi'] ?? '') ?>">
                                    <?= htmlspecialchars($u['dia_chi'] ?? 'Chưa có') ?>
                                </small>
                            </td>
                            <td>
                                <?php if ($u['vai_tro'] === 'admin'): ?>
                                    <span class="badge bg-danger rounded-pill px-3 py-1">
                                        <i class="bi bi-shield-lock-fill me-1"></i>Quản trị viên
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-secondary-subtle text-secondary rounded-pill px-3 py-1 border">
                                        <i class="bi bi-person me-1"></i>Khách hàng
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <small class="text-muted"><?= date('d/m/Y', strtotime($u['ngay_tao'])) ?></small>
                            </td>
                            <td class="text-end">
                                <?php if ((int)$u['id'] === (int)($_SESSION['user']['id'] ?? 0)): ?>
                                    <span class="badge bg-light text-muted border">Tài khoản này</span>
                                <?php else: ?>
                                    <form method="post" action="<?= BASE_URL ?>?action=admin/users/toggle-role" class="d-inline">
                                        <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                                        <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                        <?php if ($u['vai_tro'] === 'admin'): ?>
                                            <input type="hidden" name="vai_tro" value="khach">
                                            <button type="submit" class="btn btn-sm btn-outline-secondary py-1 px-2" 
                                                    onclick="return confirm('Hạ quyền người dùng này xuống làm Khách hàng?');"
                                                    title="Hạ quyền xuống khách hàng">
                                                <i class="bi bi-arrow-down-circle"></i> Về Khách
                                            </button>
                                        <?php else: ?>
                                            <input type="hidden" name="vai_tro" value="admin">
                                            <button type="submit" class="btn btn-sm btn-outline-danger py-1 px-2" 
                                                    onclick="return confirm('Thăng cấp người dùng này lên làm Quản trị viên (Admin)?');"
                                                    title="Nâng cấp lên Admin">
                                                <i class="bi bi-arrow-up-circle-fill"></i> Lên Admin
                                            </button>
                                        <?php endif; ?>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
