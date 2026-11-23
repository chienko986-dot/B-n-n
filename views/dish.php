<?php
/**
 * VIEW: CHI TIET MON AN
 * - Anh lon (co the co anh phu), gia khuyen mai, thong tin mon
 * - Lua chon size / topping / ghi chu + so luong, tinh tien ngay tren trang
 * - Them vao gio (ajax) / Mua ngay
 * - Form danh gia (chi khach da mua), danh sach danh gia, mon lien quan
 *
 * Du lieu tu DishController::detail():
 *   $dish, $sizes, $toppings, $images,
 *   $reviews, $avgRating, $related, $soldCount,
 *   $daMuaMon, $danhGiaCuaToi
 */

$flash = get_flash();

$tonKho   = (int) $dish['so_luong_ton'];
$hetHang  = $tonKho <= 0;
$avgStr   = $avgRating > 0 ? number_format($avgRating, 1, '.', '') : '0.0';
$hasReviews = count($reviews) > 0;

$giaHienTai = (float) $dish['gia'];
$giaGoc     = ($dish['gia_goc'] ?? null) !== null ? (float) $dish['gia_goc'] : null;
$coSale     = $giaGoc !== null && $giaGoc > $giaHienTai;
$giamPhanTram = $coSale ? (int) round((1 - $giaHienTai / $giaGoc) * 100) : 0;

// Gia khoi diem = gia mon + gia tang size mac dinh (dung de hien thi ban dau)
$sizeMacDinh = null;
foreach ($sizes as $size) {
    if ((int) $size['mac_dinh'] === 1) {
        $sizeMacDinh = $size;
        break;
    }
}
if ($sizeMacDinh === null && count($sizes) > 0) {
    $sizeMacDinh = $sizes[0];
}
$giaKhoiDiem = $giaHienTai + (float) ($sizeMacDinh['gia_tang'] ?? 0);

$coDangNhap   = !empty($_SESSION['user']['id']);
$nguoiDungId  = (int) ($_SESSION['user']['id'] ?? 0);

$stars = function ($rating) {
    $full = (int) floor($rating);
    $half = ($rating - $full) >= 0.5;
    $html = '';
    for ($i = 1; $i <= 5; $i++) {
        if ($i <= $full) {
            $html .= '<i class="bi bi-star-fill"></i>';
        } elseif ($i === $full + 1 && $half) {
            $html .= '<i class="bi bi-star-half"></i>';
        } else {
            $html .= '<i class="bi bi-star"></i>';
        }
    }
    return $html;
};
?>

<section class="section shop-page dish-detail-page">
    <div class="container">

        <!-- ================= BREADCRUMB ================= -->
        <nav class="dish-breadcrumb" aria-label="Điều hướng">
            <a href="<?= BASE_URL ?>">Trang chủ</a>
            <i class="bi bi-chevron-right"></i>
            <a href="<?= BASE_URL ?>#thuc-don">Thực đơn</a>
            <i class="bi bi-chevron-right"></i>
            <span><?= htmlspecialchars($dish['ten_mon'], ENT_QUOTES, 'UTF-8') ?></span>
        </nav>

        <!-- ================= THONG BAO (flash) ================= -->
        <?php if ($flash): ?>
            <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : 'success' ?> shop-alert" role="alert">
                <i class="bi <?= $flash['type'] === 'error' ? 'bi-exclamation-circle-fill' : 'bi-check-circle-fill' ?>"></i>
                <?= htmlspecialchars($flash['message'], ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <!-- ================= THONG TIN MON ================= -->
        <div class="dish-detail-layout">

            <!-- Anh mon -->
            <div class="dish-detail-media">
                <div class="dish-detail-thumb tone-1">
                    <i class="bi bi-bowl-hot dish-fallback"></i>
                    <img id="dishMainImg"
                         src="<?= htmlspecialchars(dish_image_url($dish['hinh_anh']), ENT_QUOTES, 'UTF-8') ?>"
                         alt="<?= htmlspecialchars($dish['ten_mon'], ENT_QUOTES, 'UTF-8') ?>"
                         onerror="this.style.display='none'">
                    <?php if ($hetHang): ?>
                        <span class="dish-detail-badge is-out">Hết hàng</span>
                    <?php elseif ($coSale): ?>
                        <span class="dish-detail-badge is-sale">Giảm <?= $giamPhanTram ?>%</span>
                    <?php elseif ($soldCount >= 100): ?>
                        <span class="dish-detail-badge">Bán chạy</span>
                    <?php endif; ?>
                </div>

                <?php if (count($images) > 0): ?>
                    <div class="dish-thumb-row">
                        <button type="button" class="dish-thumb-item is-active"
                                data-full="<?= htmlspecialchars(dish_image_url($dish['hinh_anh']), ENT_QUOTES, 'UTF-8') ?>">
                            <img src="<?= htmlspecialchars(dish_image_url($dish['hinh_anh']), ENT_QUOTES, 'UTF-8') ?>"
                                 alt="Ảnh chính" loading="lazy">
                        </button>
                        <?php foreach ($images as $anh): ?>
                            <button type="button" class="dish-thumb-item"
                                    data-full="<?= htmlspecialchars(dish_image_url($anh['hinh_anh']), ENT_QUOTES, 'UTF-8') ?>">
                                <img src="<?= htmlspecialchars(dish_image_url($anh['hinh_anh']), ENT_QUOTES, 'UTF-8') ?>"
                                     alt="Ảnh món" loading="lazy">
                            </button>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Thong tin -->
            <div class="dish-detail-info">
                <?php if (!empty($dish['ten_danh_muc'])): ?>
                    <span class="dish-category"><?= htmlspecialchars($dish['ten_danh_muc'], ENT_QUOTES, 'UTF-8') ?></span>
                <?php endif; ?>

                <h1 class="dish-detail-title"><?= htmlspecialchars($dish['ten_mon'], ENT_QUOTES, 'UTF-8') ?></h1>

                <div class="dish-detail-meta">
                    <span class="dish-detail-rating">
                        <?= $stars($avgRating) ?>
                        <b><?= $avgStr ?></b>
                        <small>(<?= count($reviews) ?> đánh giá)</small>
                    </span>
                    <span class="dish-detail-sold">
                        <i class="bi bi-bag-check"></i> <?= $soldCount ?> đã bán
                    </span>
                    <span class="dish-detail-stock <?= $hetHang ? 'is-out' : 'is-ok' ?>">
                        <i class="bi <?= $hetHang ? 'bi-x-circle' : 'bi-check-circle' ?>"></i>
                        <?= $hetHang ? 'Hết hàng' : 'Còn ' . $tonKho . ' phần' ?>
                    </span>
                </div>

                <div class="dish-detail-price <?= $coSale ? 'is-sale' : '' ?>">
                    <strong id="dishUnitPrice"><?= format_price($giaKhoiDiem) ?></strong>
                    <?php if ($coSale): ?>
                        <del><?= format_price($giaGoc) ?></del>
                        <span class="dish-sale-tag">Giảm <?= $giamPhanTram ?>%</span>
                    <?php endif; ?>
                </div>

                <?php if (!empty($dish['mo_ta'])): ?>
                    <p class="dish-detail-desc"><?= htmlspecialchars($dish['mo_ta'], ENT_QUOTES, 'UTF-8') ?></p>
                <?php endif; ?>

                <?php if (!empty($dish['thoi_gian_chuan_bi']) || !empty($dish['khau_phan']) || !empty($dish['thanh_phan'])): ?>
                    <ul class="dish-facts">
                        <?php if (!empty($dish['thoi_gian_chuan_bi'])): ?>
                            <li><i class="bi bi-clock"></i><span>Chuẩn bị: <?= htmlspecialchars($dish['thoi_gian_chuan_bi'], ENT_QUOTES, 'UTF-8') ?></span></li>
                        <?php endif; ?>
                        <?php if (!empty($dish['khau_phan'])): ?>
                            <li><i class="bi bi-people"></i><span>Khẩu phần: <?= htmlspecialchars($dish['khau_phan'], ENT_QUOTES, 'UTF-8') ?></span></li>
                        <?php endif; ?>
                        <?php if (!empty($dish['thanh_phan'])): ?>
                            <li><i class="bi bi-list-check"></i><span>Thành phần: <?= htmlspecialchars($dish['thanh_phan'], ENT_QUOTES, 'UTF-8') ?></span></li>
                        <?php endif; ?>
                    </ul>
                <?php endif; ?>

                <ul class="dish-detail-features">
                    <li><i class="bi bi-truck"></i> Giao nhanh trong 20 phút</li>
                    <li><i class="bi bi-shield-check"></i> An toàn vệ sinh thực phẩm</li>
                    <li><i class="bi bi-arrow-repeat"></i> Hoàn tiền nếu món không đạt chất lượng</li>
                </ul>

                <?php if ($hetHang): ?>
                    <div class="dish-detail-soldout">
                        <i class="bi bi-exclamation-circle-fill"></i>
                        Món này hiện đã hết hàng. Vui lòng chọn món khác nhé!
                    </div>
                <?php else: ?>
                    <!-- ================= KHU MUA HANG ================= -->
                    <div class="dish-buy-box"
                         id="dishBuyBox"
                         data-id="<?= (int) $dish['id'] ?>"
                         data-base="<?= (int) $giaHienTai ?>"
                         data-max="<?= $tonKho ?>">

                        <?php if (count($sizes) > 0): ?>
                            <fieldset class="dish-option-group">
                                <legend>Chọn size <span class="dish-option-req">bắt buộc</span></legend>
                                <div class="dish-size-list">
                                    <?php foreach ($sizes as $size): ?>
                                        <label class="dish-size-pill">
                                            <input type="radio"
                                                   name="size_id"
                                                   value="<?= (int) $size['id'] ?>"
                                                   data-gia-tang="<?= (int) $size['gia_tang'] ?>"
                                                   data-ten="<?= htmlspecialchars($size['ten_size'], ENT_QUOTES, 'UTF-8') ?>"
                                                   <?= $sizeMacDinh !== null && (int) $size['id'] === (int) $sizeMacDinh['id'] ? 'checked' : '' ?>>
                                            <span class="pill-name"><?= htmlspecialchars($size['ten_size'], ENT_QUOTES, 'UTF-8') ?></span>
                                            <span class="pill-price">
                                                <?= (float) $size['gia_tang'] > 0 ? '+' . format_price($size['gia_tang']) : 'Miễn phí' ?>
                                            </span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </fieldset>
                        <?php endif; ?>

                        <?php if (count($toppings) > 0): ?>
                            <fieldset class="dish-option-group">
                                <legend>Thêm topping <small>không bắt buộc</small></legend>
                                <div class="dish-topping-list">
                                    <?php foreach ($toppings as $top): ?>
                                        <label class="dish-topping-pill">
                                            <input type="checkbox"
                                                   name="topping_ids[]"
                                                   value="<?= (int) $top['id'] ?>"
                                                   data-gia="<?= (int) $top['gia'] ?>"
                                                   data-ten="<?= htmlspecialchars($top['ten_topping'], ENT_QUOTES, 'UTF-8') ?>">
                                            <span class="pill-name"><?= htmlspecialchars($top['ten_topping'], ENT_QUOTES, 'UTF-8') ?></span>
                                            <span class="pill-price">+<?= format_price($top['gia']) ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </fieldset>
                        <?php endif; ?>

                        <div class="dish-option-group">
                            <label for="dishNote">Ghi chú riêng</label>
                            <textarea id="dishNote" name="ghi_chu" class="dish-note-input" rows="2" maxlength="255"
                                      placeholder="VD: ít cay, không hành, giao trước cổng..."></textarea>
                        </div>

                        <!-- Chon so luong + mua -->
                        <div class="dish-detail-actions" data-max="<?= $tonKho ?>">
                            <div class="qty-picker" role="group" aria-label="Chọn số lượng">
                                <button type="button" class="qty-btn" id="qtyMinus" aria-label="Giảm số lượng">
                                    <i class="bi bi-dash"></i>
                                </button>
                                <input type="number" id="detailQty" class="qty-input" value="1" min="1"
                                       max="<?= $tonKho ?>" inputmode="numeric"
                                       aria-label="Số lượng">
                                <button type="button" class="qty-btn" id="qtyPlus" aria-label="Tăng số lượng">
                                    <i class="bi bi-plus"></i>
                                </button>
                            </div>

                            <button type="button" class="btn btn-brand btn-lg btn-add" id="detailAddBtn"
                                data-id="<?= (int) $dish['id'] ?>"
                                data-dish="<?= htmlspecialchars($dish['ten_mon'], ENT_QUOTES, 'UTF-8') ?>"
                                data-price="<?= (int) $giaHienTai ?>"
                                data-image="<?= htmlspecialchars(dish_image_url($dish['hinh_anh']), ENT_QUOTES, 'UTF-8') ?>"
                                data-use-qty="1"
                                data-use-opts="1"
                                title="Thêm vào giỏ hàng">
                                <i class="bi bi-bag-plus"></i> Thêm vào giỏ
                            </button>

                            <button type="button" class="btn btn-ghost btn-lg" id="buyNowBtn">
                                <i class="bi bi-lightning-charge-fill"></i> Mua ngay
                            </button>
                        </div>

                        <div class="dish-total-line">
                            <span>Tạm tính</span>
                            <strong id="dishTotalPrice"><?= format_price($giaKhoiDiem) ?></strong>
                            <small id="dishTotalQty">× 1 phần</small>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- ================= DANH GIA ================= -->
        <div class="dish-detail-section">
            <div class="section-head">
                <div>
                    <span class="eyebrow">Phản hồi</span>
                    <h2 class="section-title">Đánh giá của khách hàng</h2>
                </div>
            </div>

            <!-- Form danh gia (tinh trang phu hop voi tung khach) -->
            <?php if (!$coDangNhap): ?>
                <div class="dish-review-cta">
                    <i class="bi bi-box-arrow-in-right"></i>
                    <div>
                        <b>Đăng nhập để đánh giá món này</b>
                        <p>Chia sẻ cảm nhận của bạn sau khi thưởng thức nhé!</p>
                    </div>
                    <a href="<?= BASE_URL ?>?action=login" class="btn btn-brand">Đăng nhập</a>
                </div>
            <?php elseif (!$daMuaMon): ?>
                <div class="dish-review-cta is-locked">
                    <i class="bi bi-lock"></i>
                    <div>
                        <b>Chỉ khách đã mua mới có thể đánh giá</b>
                        <p>Hãy đặt món này để trở thành khách hàng và chia sẻ cảm nhận nhé!</p>
                    </div>
                </div>
            <?php else: ?>
                <form method="post" action="<?= BASE_URL ?>?action=review-post" class="dish-review-form">
                    <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="monan_id" value="<?= (int) $dish['id'] ?>">

                    <div class="dish-review-form-head">
                        <b><?= $danhGiaCuaToi ? 'Sửa đánh giá của bạn' : 'Viết đánh giá của bạn' ?></b>
                        <?php if ($danhGiaCuaToi): ?>
                            <span class="dish-review-mytag">Bạn đã đánh giá món này</span>
                        <?php endif; ?>
                    </div>

                    <div class="dish-review-form-row">
                        <span class="dish-review-form-label">Điểm của bạn</span>
                        <div class="dish-star-input" id="dishStarInput">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <input type="radio" name="so_sao" id="star<?= $i ?>" value="<?= $i ?>"
                                       <?= $danhGiaCuaToi ? ((int) $danhGiaCuaToi['so_sao'] === $i ? 'checked' : '') : ($i === 5 ? 'checked' : '') ?>>
                                <label for="star<?= $i ?>" title="<?= $i ?> sao"><i class="bi bi-star-fill"></i></label>
                            <?php endfor; ?>
                            <span class="dish-star-hint" id="dishStarHint"></span>
                        </div>
                    </div>

                    <div class="dish-review-form-row is-col">
                        <label for="dishReviewText">Nội dung</label>
                        <textarea id="dishReviewText" name="binh_luan" rows="3" maxlength="1000"
                                  placeholder="Món có ngon không? Nói cho mọi người cùng biết..."><?= $danhGiaCuaToi ? htmlspecialchars($danhGiaCuaToi['binh_luan'] ?? '', ENT_QUOTES, 'UTF-8') : '' ?></textarea>
                    </div>

                    <div class="dish-review-form-foot">
                        <button type="submit" class="btn btn-brand">
                            <i class="bi bi-send"></i> <?= $danhGiaCuaToi ? 'Cập nhật đánh giá' : 'Gửi đánh giá' ?>
                        </button>
                    </div>
                </form>
            <?php endif; ?>

            <?php if (!$hasReviews): ?>
                <div class="dish-review-empty">
                    <i class="bi bi-chat-square-text"></i>
                    <h3>Chưa có đánh giá nào</h3>
                    <p>Hãy là người đầu tiên thưởng thức và chia sẻ cảm nhận về món này nhé!</p>
                </div>
            <?php else: ?>
                <div class="dish-review-summary">
                    <div class="dish-review-score">
                        <strong><?= $avgStr ?></strong>
                        <div class="dish-review-stars"><?= $stars($avgRating) ?></div>
                        <span><?= count($reviews) ?> đánh giá</span>
                    </div>
                </div>

                <div class="dish-review-list">
                    <?php foreach ($reviews as $review): ?>
                        <article class="dish-review-item<?= $nguoiDungId > 0 && (int) ($review['nguoidung_id'] ?? 0) === $nguoiDungId ? ' is-mine' : '' ?>">
                            <div class="dish-review-avatar">
                                <?= htmlspecialchars(mb_strtoupper(mb_substr($review['ten_nguoidung'] ?? 'K', 0, 1, 'UTF-8'), 'UTF-8'), ENT_QUOTES, 'UTF-8') ?>
                            </div>
                            <div class="dish-review-body">
                                <div class="dish-review-head">
                                    <b><?= htmlspecialchars($review['ten_nguoidung'] ?? 'Khách hàng', ENT_QUOTES, 'UTF-8') ?></b>
                                    <span class="dish-review-stars"><?= $stars((int) $review['so_sao']) ?></span>
                                    <small><?= date('d/m/Y', strtotime($review['ngay_danh_gia'])) ?></small>
                                </div>
                                <?php if (!empty($review['binh_luan'])): ?>
                                    <p><?= nl2br(htmlspecialchars($review['binh_luan'], ENT_QUOTES, 'UTF-8')) ?></p>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- ================= MON LIEN QUAN ================= -->
        <?php if (count($related) > 0): ?>
            <div class="dish-detail-section">
                <div class="section-head">
                    <div>
                        <span class="eyebrow">Gợi ý cho bạn</span>
                        <h2 class="section-title">Món liên quan</h2>
                    </div>
                    <a href="<?= BASE_URL ?>#thuc-don" class="section-link">
                        Xem tất cả <i class="bi bi-arrow-right"></i>
                    </a>
                </div>

                <div class="dish-grid dish-grid-related">
                    <?php foreach ($related as $rel): ?>
                        <?php
                            $relGia     = (float) $rel['gia'];
                            $relGiaGoc  = ($rel['gia_goc'] ?? null) !== null ? (float) $rel['gia_goc'] : null;
                            $relCoSale  = $relGiaGoc !== null && $relGiaGoc > $relGia;
                        ?>
                        <article class="dish-card"
                            data-href="<?= BASE_URL ?>?action=dish&id=<?= (int) $rel['id'] ?>">
                            <div class="dish-thumb tone-1">
                                <i class="bi bi-bowl-hot dish-fallback"></i>
                                <img src="<?= htmlspecialchars(dish_image_url($rel['hinh_anh']), ENT_QUOTES, 'UTF-8') ?>"
                                     alt="<?= htmlspecialchars($rel['ten_mon'], ENT_QUOTES, 'UTF-8') ?>" loading="lazy"
                                     onerror="this.style.display='none'">
                                <?php if ($relCoSale): ?>
                                    <span class="dish-sale-badge">Giảm <?= (int) round((1 - $relGia / $relGiaGoc) * 100) ?>%</span>
                                <?php endif; ?>
                            </div>
                            <div class="dish-body">
                                <span class="dish-category"><?= htmlspecialchars($rel['ten_danh_muc'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
                                <h3 class="dish-name">
                                    <a href="<?= BASE_URL ?>?action=dish&id=<?= (int) $rel['id'] ?>">
                                        <?= htmlspecialchars($rel['ten_mon'], ENT_QUOTES, 'UTF-8') ?>
                                    </a>
                                </h3>
                                <div class="dish-footer">
                                    <div class="dish-price<?= $relCoSale ? ' is-sale' : '' ?>">
                                        <strong><?= format_price($relGia) ?></strong>
                                        <?php if ($relCoSale): ?>
                                            <del><?= format_price($relGiaGoc) ?></del>
                                        <?php endif; ?>
                                    </div>
                                    <?php if ((int) $rel['so_luong_ton'] > 0): ?>
                                        <button class="btn btn-add" type="button"
                                            data-id="<?= (int) $rel['id'] ?>"
                                            data-dish="<?= htmlspecialchars($rel['ten_mon'], ENT_QUOTES, 'UTF-8') ?>"
                                            data-price="<?= (int) $relGia ?>"
                                            data-image="<?= htmlspecialchars(dish_image_url($rel['hinh_anh']), ENT_QUOTES, 'UTF-8') ?>"
                                            title="Thêm vào giỏ hàng"
                                            aria-label="Thêm <?= htmlspecialchars($rel['ten_mon'], ENT_QUOTES, 'UTF-8') ?> vào giỏ hàng">
                                            <i class="bi bi-plus-lg"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

    </div>
</section>
