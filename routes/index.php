<?php

$action = $_GET['action'] ?? '/';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$isPost = ($method === 'POST');

match ($action) {
    // ---------- TRANG CHU ----------
    '/'         => (new HomeController)->index(),

    // ---------- TAI KHOAN ----------
    'register'  => (new AuthController)->register(),
    'login'     => (new AuthController)->login(),
    'logout'    => (new AuthController)->logout(),

    // ---------- GIO HANG (PHP Session) ----------
    'cart'        => (new CartController)->index(),
    'cart-add'    => $isPost ? (new CartController)->add()    : redirect_to(BASE_URL . '?action=cart'),
    'cart-update' => $isPost ? (new CartController)->update() : redirect_to(BASE_URL . '?action=cart'),
    'cart-remove' => $isPost ? (new CartController)->remove() : redirect_to(BASE_URL . '?action=cart'),
    'cart-clear'  => $isPost ? (new CartController)->clear()  : redirect_to(BASE_URL . '?action=cart'),

    // ---------- DAT HANG ----------
    'checkout'      => (new CheckoutController)->index(),
    'checkout-post' => $isPost ? (new CheckoutController)->placeOrder() : redirect_to(BASE_URL . '?action=cart'),
    'order-success' => (new CheckoutController)->success(),

    // Action khong ton tai -> ve trang chu
    default     => (new HomeController)->index(),
};
