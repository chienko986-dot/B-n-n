<?php

// Tu dong phat hien BASE_URL de khong bi loi session khi chay tren port khac
// (vi du: localhost:8000 cua php -S hoac localhost cua Apache/Laragon)
$httpProtocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$httpHost = $_SERVER['HTTP_HOST'] ?? 'localhost';
$rawScriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/');
$scriptName = rtrim($rawScriptName, '/');
$scriptDir = ($rawScriptName !== $scriptName)
    ? ($scriptName ?: '/')
    : dirname($scriptName ?: '/');

// Một số cấu hình Apache chạy front controller như /index.php dù URL
// thực tế nằm trong thư mục con (ví dụ /B-n-n/). Khi đó dùng thư mục URL.
if ($scriptDir === '/' || $scriptDir === '\\') {
    $requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $requestPath = str_replace('\\', '/', $requestPath);

    if (str_ends_with($requestPath, '/')) {
        $scriptDir = rtrim($requestPath, '/') ?: '/';
    } elseif (basename($requestPath) === 'index.php') {
        $scriptDir = dirname($requestPath);
    }
}

$basePath = rtrim($scriptDir, '/');

if ($basePath === '/' || $basePath === '\\') {
    $basePath = '';
}

define('BASE_URL', $httpProtocol . '://' . $httpHost . $basePath . '/');

define('PATH_ROOT', __DIR__ . '/../');

define('PATH_VIEW', PATH_ROOT . 'views/');
define('PATH_VIEW_MAIN', PATH_ROOT . 'views/main.php');
define('BASE_ASSETS_UPLOADS', BASE_URL . 'assets/uploads/');
define('PATH_ASSETS_UPLOADS', PATH_ROOT . 'assets/uploads/');
define('PATH_CONTROLLER', PATH_ROOT . 'controllers/');
define('PATH_MODEL', PATH_ROOT . 'models/');

// Ket noi CSDL - co the ghi de bang bien moi truong, mac dinh la website_ban_do_an
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_USERNAME', getenv('DB_USERNAME') ?: 'root');
define('DB_PASSWORD', getenv('DB_PASSWORD') ?: '');
define('DB_NAME', getenv('DB_NAME') ?: 'website_ban_do_an');
define('DB_OPTIONS', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

// Quy tac phi giao hang (dung cho tinh nang dat hang)
define('PHI_GIAO_HANG',    30000);  // phi giao hang mac dinh
define('MIEN_PHI_GIAO_TU', 99000);  // mien phi giao hang voi don tu 99.000đ
