<?php
$formatPrice = fn($price) => number_format($price, 0, ',', '.') . 'đ';

$categories = [
    ['name' => 'Cơm & Món Việt', 'icon' => 'bi-basket2-fill', 'count' => 42, 'tone' => 'tone-1'],
    ['name' => 'Phở & Bún',      'icon' => 'bi-cup-hot-fill',  'count' => 28, 'tone' => 'tone-2'],
    ['name' => 'Burger & Fastfood', 'icon' => 'bi-egg-fried',   'count' => 36, 'tone' => 'tone-3'],
    ['name' => 'Pizza & Pasta',  'icon' => 'bi-circle-fill',    'count' => 19, 'tone' => 'tone-4'],
    ['name' => 'Tráng miệng',    'icon' => 'bi-cake2-fill',     'count' => 24, 'tone' => 'tone-5'],
    ['name' => 'Đồ uống',        'icon' => 'bi-cup-straw',      'count' => 31, 'tone' => 'tone-6'],
];

$dishes = [
    [
        'name' => 'Cơm tấm sườn nướng',
        'category' => 'Món Việt',
        'desc' => 'Sườn nướng mật ong, cơm tấm mềm, ăn kèm bì và chả trứng.',
        'price' => 45000,
        'old_price' => 55000,
        'rating' => '4.9',
        'sold' => 320,
        'tag' => 'Bán chạy',
        'icon' => 'bi-basket2',
        'tone' => 'tone-1',
    ],
    [
        'name' => 'Phở bò đặc biệt',
        'category' => 'Phở & Bún',
        'desc' => 'Nước dùng hầm xương 12 tiếng, bò tái, bò viên và gầu giòn.',
        'price' => 59000,
        'old_price' => 0,
        'rating' => '4.8',
        'sold' => 210,
        'tag' => 'Món mới',
        'icon' => 'bi-cup-hot',
        'tone' => 'tone-2',
    ],
    [
        'name' => 'Burger bò phô mai',
        'category' => 'Fastfood',
        'desc' => 'Bò Úc nướng than, phô mai cheddar tan chảy, sốt đặc biệt.',
        'price' => 69000,
        'old_price' => 89000,
        'rating' => '4.7',
        'sold' => 180,
        'tag' => '-22%',
        'icon' => 'bi-egg-fried',
        'tone' => 'tone-3',
    ],
    [
        'name' => 'Pizza hải sản',
        'category' => 'Pizza & Pasta',
        'desc' => 'Đế mỏng giòn, tôm, mực, thanh cua và phô mai mozzarella.',
        'price' => 159000,
        'old_price' => 199000,
        'rating' => '4.8',
        'sold' => 96,
        'tag' => 'Combo',
        'icon' => 'bi-circle-square',
        'tone' => 'tone-4',
    ],
    [
        'name' => 'Bún chả Hà Nội',
        'category' => 'Phở & Bún',
        'desc' => 'Chả viên nướng thơm lừng, nước mắm chua ngọt chuẩn vị Hà thành.',
        'price' => 49000,
        'old_price' => 0,
        'rating' => '4.9',
        'sold' => 275,
        'tag' => 'Bán chạy',
        'icon' => 'bi-bowl-hot',
        'tone' => 'tone-2',
    ],
    [
        'name' => 'Gà rán giòn cay',
        'category' => 'Fastfood',
        'desc' => 'Gà ướp sốt cay Hàn Quốc, lớp vỏ giòn rụm, ăn là mê.',
        'price' => 79000,
        'old_price' => 99000,
        'rating' => '4.7',
        'sold' => 340,
        'tag' => 'Hot',
        'icon' => 'bi-fire',
        'tone' => 'tone-3',
    ],
    [
        'name' => 'Bánh flan trứng',
        'category' => 'Tráng miệng',
        'desc' => 'Flan mềm mịn, caramel đắng nhẹ, thêm cà phê đá là hết sảy.',
        'price' => 25000,
        'old_price' => 0,
        'rating' => '4.9',
        'sold' => 150,
        'tag' => 'Món mới',
        'icon' => 'bi-cake2',
        'tone' => 'tone-5',
    ],
    [
        'name' => 'Trà đào cam sả',
        'category' => 'Đồ uống',
        'desc' => 'Trà đào thơm, cam tươi và sả, ly lớn 700ml giải nhiệt.',
        'price' => 35000,
        'old_price' => 45000,
        'rating' => '4.8',
        'sold' => 410,
        'tag' => '-22%',
        'icon' => 'bi-cup-straw',
        'tone' => 'tone-6',
    ],
];

$steps = [
    ['icon' => 'bi-hand-index-thumb', 'title' => 'Chọn món yêu thích', 'desc' => 'Lướt thực đơn, thêm món vào giỏ chỉ với một chạm.'],
    ['icon' => 'bi-credit-card-2-front', 'title' => 'Thanh toán dễ dàng', 'desc' => 'Thanh toán khi nhận hàng hoặc chuyển khoản, ví điện tử.'],
    ['icon' => 'bi-scooter', 'title' => 'Nhận món tận cửa', 'desc' => 'Tài xế giao trong 20 phút, món nóng hổi, chuẩn vị.'],
];
?>

<section class="hero-wrap">
    <div class="container">
        <div class="hero">
            <div class="hero-copy">
                <span class="hero-badge">
                    <i class="bi bi-lightning-charge-fill"></i>
                    Giao nhanh trong 20 phút
                </span>
                <h1 class="hero-title">
                    Món ngon <span>chuẩn vị</span><br>
                    giao tận cửa nhà
                </h1>
                <p class="hero-desc">
                    Hàng trăm món ăn từ những căn bếp tận tâm, sẵn sàng phục vụ bạn
                    mỗi ngày. Đặt món dễ dàng, giao nhanh, ăn là mê.
                </p>

                <div class="hero-actions">
                    <a href="#thuc-don" class="btn btn-brand btn-lg">
                        Đặt món ngay <i class="bi bi-arrow-right"></i>
                    </a>
                    <a href="#thuc-don" class="btn btn-ghost btn-lg">
                        <i class="bi bi-play-circle"></i> Xem thực đơn
                    </a>
                </div>

                <div class="hero-stats">
                    <div>
                        <strong>500+</strong>
                        <span>Món ngon</span>
                    </div>
                    <div>
                        <strong>4.9/5</strong>
                        <span>Khách hài lòng</span>
                    </div>
                    <div>
                        <strong>20 phút</strong>
                        <span>Giao tận nơi</span>
                    </div>
                </div>
            </div>

            <div class="hero-visual">
                <span class="hero-blob"></span>
                <span class="plate-ring"></span>
                <div class="plate">
                    <i class="bi bi-bowl-hot"></i>
                </div>
                <div class="float-card float-rating">
                    <span class="float-icon float-icon-star"><i class="bi bi-star-fill"></i></span>
                    <div>
                        <strong>4.9</strong>
                        <small>12.000+ đánh giá</small>
                    </div>
                </div>
                <div class="float-card float-ship">
                    <span class="float-icon float-icon-truck"><i class="bi bi-scooter"></i></span>
                    <div>
                        <strong>20 phút</strong>
                        <small>Giao hàng siêu tốc</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="feature-strip-wrap">
    <div class="container">
        <div class="feature-strip">
            <div class="feature-item">
                <span class="feature-icon"><i class="bi bi-truck"></i></span>
                <div>
                    <h6>Miễn phí giao hàng</h6>
                    <p>Cho đơn từ 99.000đ</p>
                </div>
            </div>
            <div class="feature-item">
                <span class="feature-icon"><i class="bi bi-clock-history"></i></span>
                <div>
                    <h6>Giao nhanh 20 phút</h6>
                    <p>Cam kết đúng giờ</p>
                </div>
            </div>
            <div class="feature-item">
                <span class="feature-icon"><i class="bi bi-shield-check"></i></span>
                <div>
                    <h6>An toàn vệ sinh</h6>
                    <p>Bếp đạt chuẩn 5 sao</p>
                </div>
            </div>
            <div class="feature-item">
                <span class="feature-icon"><i class="bi bi-headset"></i></span>
                <div>
                    <h6>Hỗ trợ 24/7</h6>
                    <p>Luôn sẵn sàng giúp bạn</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section" id="thuc-don">
    <div class="container">
        <div class="section-head">
            <div>
                <span class="eyebrow">Danh mục</span>
                <h2 class="section-title">Bạn đang thèm món gì?</h2>
            </div>
            <a href="#" class="section-link">Xem tất cả <i class="bi bi-arrow-right"></i></a>
        </div>

        <div class="cat-grid">
            <?php foreach ($categories as $category): ?>
                <a href="#" class="cat-card">
                    <span class="cat-icon <?= $category['tone'] ?>"><i class="bi <?= $category['icon'] ?>"></i></span>
                    <strong><?= $category['name'] ?></strong>
                    <small><?= $category['count'] ?> món</small>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section section-muted">
    <div class="container">
        <div class="section-head">
            <div>
                <span class="eyebrow">Thực đơn hôm nay</span>
                <h2 class="section-title">Món ngon được yêu thích nhất</h2>
            </div>
            <a href="#" class="section-link">Xem tất cả <i class="bi bi-arrow-right"></i></a>
        </div>

        <div class="dish-grid">
            <?php foreach ($dishes as $dish): ?>
                <article class="dish-card reveal">
                    <div class="dish-thumb <?= $dish['tone'] ?>">
                        <i class="bi <?= $dish['icon'] ?>"></i>
                        <span class="dish-tag"><?= $dish['tag'] ?></span>
                        <button class="dish-fav" type="button" aria-label="Yêu thích">
                            <i class="bi bi-heart"></i>
                        </button>
                    </div>
                    <div class="dish-body">
                        <span class="dish-category"><?= $dish['category'] ?></span>
                        <h3 class="dish-name"><?= $dish['name'] ?></h3>
                        <p class="dish-desc"><?= $dish['desc'] ?></p>
                        <div class="dish-meta">
                            <span><i class="bi bi-star-fill"></i> <?= $dish['rating'] ?></span>
                            <span><i class="bi bi-bag-check"></i> <?= $dish['sold'] ?> đã bán</span>
                        </div>
                        <div class="dish-footer">
                            <div class="dish-price">
                                <strong><?= $formatPrice($dish['price']) ?></strong>
                                <?php if ($dish['old_price'] > 0): ?>
                                    <del><?= $formatPrice($dish['old_price']) ?></del>
                                <?php endif; ?>
                            </div>
                            <button class="btn btn-add" type="button" data-dish="<?= $dish['name'] ?>">
                                <i class="bi bi-plus-lg"></i>
                            </button>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section" id="khuyen-mai">
    <div class="container">
        <div class="promo">
            <div class="promo-copy">
                <span class="promo-badge"><i class="bi bi-ticket-perforated-fill"></i> Ưu đãi hôm nay</span>
                <h2>Giảm 30% cho đơn đầu tiên</h2>
                <p>Nhập mã <b>ANLAME30</b> khi thanh toán để nhận ưu đãi. Áp dụng cho đơn từ 99.000đ.</p>
                <div class="promo-actions">
                    <a href="#thuc-don" class="btn btn-light btn-lg">Đặt món ngay</a>
                    <span class="promo-code">ANLAME30</span>
                </div>
            </div>
            <div class="promo-art">
                <i class="bi bi-gift-fill"></i>
                <span class="promo-ring"></span>
            </div>
        </div>
    </div>
</section>

<section class="section section-muted">
    <div class="container">
        <div class="section-head section-head-center">
            <div>
                <span class="eyebrow">Đơn giản &amp; nhanh chóng</span>
                <h2 class="section-title">Đặt món chỉ với 3 bước</h2>
            </div>
        </div>

        <div class="step-grid">
            <?php foreach ($steps as $index => $step): ?>
                <div class="step-card reveal">
                    <span class="step-number">0<?= $index + 1 ?></span>
                    <span class="step-icon"><i class="bi <?= $step['icon'] ?>"></i></span>
                    <h3><?= $step['title'] ?></h3>
                    <p><?= $step['desc'] ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section cta-section">
    <div class="container">
        <div class="cta">
            <div>
                <h2>Đói bụng rồi? AnLaMe lo!</h2>
                <p>Đặt món ngay để nhận ưu đãi và trải nghiệm giao hàng siêu tốc.</p>
            </div>
            <a href="#thuc-don" class="btn btn-brand btn-lg">Đặt món ngay <i class="bi bi-arrow-right"></i></a>
        </div>
    </div>
</section>
