<?php

$action = $_GET['action'] ?? '/';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$isPost = ($method === 'POST');

match ($action) {
    // ---------- TRANG CHỦ ----------
    '/'                     => (new HomeController)->index(),

    // ---------- CHI TIẾT MÓN ĂN ----------
    'dish'                  => (new DishController)->detail(),
    'review-post'           => $isPost ? (new DishController)->postReview() : redirect_to(BASE_URL),

    // ---------- TÀI KHOẢN ----------
    'register'              => (new AuthController)->register(),
    'login'                 => (new AuthController)->login(),
    'logout'                => (new AuthController)->logout(),

    // ---------- GIỎ HÀNG (PHP Session) ----------
    'cart'                  => (new CartController)->index(),
    'cart-add'              => $isPost ? (new CartController)->add()    : redirect_to(BASE_URL . '?action=cart'),
    'cart-update'           => $isPost ? (new CartController)->update() : redirect_to(BASE_URL . '?action=cart'),
    'cart-remove'           => $isPost ? (new CartController)->remove() : redirect_to(BASE_URL . '?action=cart'),
    'cart-clear'            => $isPost ? (new CartController)->clear()  : redirect_to(BASE_URL . '?action=cart'),

    // ---------- ĐẶT HÀNG (CHECKOUT) ----------
    'checkout'              => (new CheckoutController)->index(),
    'checkout-post'         => $isPost ? (new CheckoutController)->placeOrder() : redirect_to(BASE_URL . '?action=cart'),
    'order-success'         => (new CheckoutController)->success(),

    // ---------- SO DIA CHI (chon nhanh khi dat hang) ----------
    'address-save'           => $isPost ? (new AddressController)->save()   : redirect_to(BASE_URL . '?action=checkout'),
    'address-delete'         => $isPost ? (new AddressController)->delete() : redirect_to(BASE_URL . '?action=checkout'),

    // ---------- QUẢN TRỊ VIÊN (ADMIN) ----------
    'admin'                 => (new AdminController)->dashboard(),
    'admin/dashboard'       => (new AdminController)->dashboard(),
    
    // Quản lý món ăn
    'admin/dishes'          => (new AdminController)->dishes(),
    'admin/dishes/create'   => (new AdminController)->dishCreate(),
    'admin/dishes/edit'     => (new AdminController)->dishEdit(),
    'admin/dishes/delete'   => (new AdminController)->dishDelete(),

    // Quản lý danh mục
    'admin/categories'        => (new AdminController)->categories(),
    'admin/categories/create' => (new AdminController)->categoryCreate(),
    'admin/categories/edit'   => (new AdminController)->categoryEdit(),
    'admin/categories/delete' => (new AdminController)->categoryDelete(),

    // Quản lý đơn hàng
    'admin/orders'               => (new AdminController)->orders(),
    'admin/orders/detail'        => (new AdminController)->orderDetail(),
    'admin/orders/update-status' => (new AdminController)->orderUpdateStatus(),

    // Quản lý người dùng & phân quyền
    'admin/users'             => (new AdminController)->users(),
    'admin/users/toggle-role' => (new AdminController)->userToggleRole(),

    // Quản lý đánh giá
    'admin/reviews'        => (new AdminController)->reviews(),
    'admin/reviews/delete' => (new AdminController)->reviewDelete(),

    // Nạp dữ liệu mẫu
    'admin/seed'           => (new AdminController)->seedData(),

    default                => (new HomeController)->index(),
};
