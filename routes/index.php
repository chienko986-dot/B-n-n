<?php

$action = $_GET['action'] ?? '/';

match ($action) {
    '/'                     => (new HomeController)->index(),
    'register'              => (new AuthController)->register(),
    'login'                 => (new AuthController)->login(),
    'logout'                => (new AuthController)->logout(),

    // Admin Routes
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

    // Quản lý người dùng
    'admin/users'             => (new AdminController)->users(),
    'admin/users/toggle-role' => (new AdminController)->userToggleRole(),

    // Quản lý đánh giá
    'admin/reviews'        => (new AdminController)->reviews(),
    'admin/reviews/delete' => (new AdminController)->reviewDelete(),

    // Nạp dữ liệu mẫu
    'admin/seed'           => (new AdminController)->seedData(),

    default                => (new HomeController)->index(),
};

