<?php
$currentAction = $_GET['action'] ?? '/';
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= $title ?? 'AnLaMe - Đặt món ngon online' ?></title>
    <meta name="description" content="AnLaMe - Đặt món ăn online, giao nhanh trong 20 phút. Hàng trăm món ngon chuẩn vị mỗi ngày.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@500;600;700;800&family=Be+Vietnam+Pro:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
    <noscript>
        <style>
            .reveal {
                opacity: 1;
                transform: none;
            }
        </style>
    </noscript>
</head>

<body>

    <header class="site-header" id="siteHeader">
        <div class="topbar d-none d-lg-block">
            <div class="container d-flex align-items-center justify-content-between">
                <span><i class="bi bi-telephone-fill"></i> Hotline: <b>1900 1234</b></span>
                <div class="topbar-links">
                    <span><i class="bi bi-truck"></i> Miễn phí giao hàng cho đơn từ 99.000đ</span>
                    <span class="topbar-sep"></span>
                    <a href="#"><i class="bi bi-shop"></i> Trở thành đối tác</a>
                </div>
            </div>
        </div>

        <nav class="navbar navbar-expand-xl main-nav">
            <div class="container">
                <a class="navbar-brand" href="<?= BASE_URL ?>">
                    <span class="brand-badge"><i class="bi bi-bowl-hot"></i></span>
                    <span class="brand-text">An<span>LaMe</span></span>
                </a>

                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainMenu"
                    aria-controls="mainMenu" aria-expanded="false" aria-label="Mở menu">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="mainMenu">
                    <form class="search-form my-3 my-lg-0" role="search" onsubmit="return false;">
                        <i class="bi bi-search"></i>
                        <input type="search" placeholder="Tìm món ngon hôm nay..." aria-label="Tìm món ăn">
                    </form>

                    <ul class="navbar-nav mx-lg-auto">
                        <li class="nav-item">
                            <a class="nav-link <?= $currentAction === '/' ? 'active' : '' ?>" href="<?= BASE_URL ?>">Trang chủ</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#thuc-don">Thực đơn</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#khuyen-mai">Khuyến mãi</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#lien-he">Liên hệ</a>
                        </li>
                    </ul>

                    <div class="header-actions d-flex align-items-center gap-2 mt-3 mt-lg-0">
                        <button class="cart-btn" type="button" data-bs-toggle="offcanvas" data-bs-target="#cartDrawer"
                            aria-controls="cartDrawer" aria-label="Mở giỏ hàng">
                            <i class="bi bi-bag"></i>
                            <span class="cart-count">0</span>
                        </button>
                        <a class="btn btn-login" href="#">
                            <i class="bi bi-person-circle"></i>
                            <span>Đăng nhập</span>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <aside class="offcanvas offcanvas-end cart-drawer" tabindex="-1" id="cartDrawer" aria-labelledby="cartDrawerTitle">
        <div class="offcanvas-header cart-drawer-header">
            <div>
                <span class="cart-eyebrow">ANLAME • GIỎ HÀNG</span>
                <h2 class="offcanvas-title" id="cartDrawerTitle">Món ngon của bạn</h2>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Đóng giỏ hàng"></button>
        </div>
        <div class="cart-shipping">
            <div class="cart-shipping-copy">
                <i class="bi bi-truck"></i>
                <span id="cartShippingMessage">Miễn phí giao hàng cho đơn từ 99.000đ</span>
            </div>
            <div class="cart-shipping-track" role="progressbar" aria-label="Tiến độ miễn phí giao hàng"
                aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
                <span id="cartShippingProgress"></span>
            </div>
        </div>
        <div class="offcanvas-body cart-drawer-body">
            <div class="cart-empty" id="cartEmpty">
                <div class="cart-empty-icon"><i class="bi bi-basket2"></i></div>
                <h3>Giỏ hàng đang trống</h3>
                <p>Chọn vài món ngon để bữa ăn hôm nay thêm trọn vị nhé.</p>
                <a class="btn btn-brand" href="#thuc-don" data-bs-dismiss="offcanvas">Khám phá thực đơn</a>
            </div>
            <div class="cart-items" id="cartItems" aria-live="polite"></div>
            <button class="cart-clear" id="cartClear" type="button" hidden>
                <i class="bi bi-trash3"></i> Xóa tất cả món
            </button>
        </div>
        <div class="cart-summary" id="cartSummary" hidden>
            <div class="cart-summary-line">
                <span>Tạm tính</span>
                <strong id="cartSubtotal">0đ</strong>
            </div>
            <div class="cart-summary-line">
                <span>Phí giao hàng</span>
                <strong id="cartDelivery">0đ</strong>
            </div>
            <div class="cart-summary-total">
                <span>Tổng cộng</span>
                <strong id="cartTotal">0đ</strong>
            </div>
            <a class="btn cart-continue-btn" href="#thuc-don" data-bs-dismiss="offcanvas">
                Tiếp tục chọn món <i class="bi bi-arrow-right"></i>
            </a>
            <p class="cart-payment-note"><i class="bi bi-shield-check"></i> Thanh toán an toàn khi nhận món</p>
        </div>
    </aside>

    <main>
