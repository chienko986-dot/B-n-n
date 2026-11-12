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
                        <a class="cart-btn" href="#" aria-label="Giỏ hàng">
                            <i class="bi bi-bag"></i>
                            <span class="cart-count">0</span>
                        </a>
                        <a class="btn btn-login" href="#">
                            <i class="bi bi-person-circle"></i>
                            <span>Đăng nhập</span>
                        </a>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <main>
