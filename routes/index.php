<?php

$action = $_GET['action'] ?? '/';

match ($action) {
    '/'         => (new HomeController)->index(),
    'register'  => (new AuthController)->register(),
    'login'     => (new AuthController)->login(),
    'logout'    => (new AuthController)->logout(),
    default     => (new HomeController)->index(),
};
