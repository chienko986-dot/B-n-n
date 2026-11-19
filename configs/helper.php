<?php

if (!function_exists('debug')) {
    function debug($data)
    {
        echo '<pre>';
        print_r($data);
        die;
    }
}

if (!function_exists('upload_file')) {
    function upload_file($folder, $file)
    {
        $uploadDir = PATH_ASSETS_UPLOADS . trim($folder, '/');
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Tạo tên file an toàn
        $extension = pathinfo($file["name"], PATHINFO_EXTENSION);
        $safeName = time() . '-' . bin2hex(random_bytes(4)) . '.' . strtolower($extension);
        $targetPath = $uploadDir . '/' . $safeName;
        $relativePath = trim($folder, '/') . '/' . $safeName;

        if (move_uploaded_file($file["tmp_name"], $targetPath)) {
            return $relativePath;
        }

        throw new Exception('Upload file không thành công!');
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token()
    {
        if (empty($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['_csrf_token'];
    }
}

if (!function_exists('is_valid_csrf_token')) {
    function is_valid_csrf_token($token)
    {
        return is_string($token)
            && isset($_SESSION['_csrf_token'])
            && is_string($_SESSION['_csrf_token'])
            && hash_equals($_SESSION['_csrf_token'], $token);
    }
}

if (!function_exists('set_flash')) {
    function set_flash($key, $message)
    {
        if (!isset($_SESSION['_flash'])) {
            $_SESSION['_flash'] = [];
        }
        $_SESSION['_flash'][$key] = $message;
    }
}

if (!function_exists('get_flash')) {
    function get_flash($key)
    {
        if (isset($_SESSION['_flash'][$key])) {
            $msg = $_SESSION['_flash'][$key];
            unset($_SESSION['_flash'][$key]);
            return $msg;
        }
        return null;
    }
}

if (!function_exists('is_admin')) {
    function is_admin()
    {
        return !empty($_SESSION['user']) && ($_SESSION['user']['vai_tro'] ?? '') === 'admin';
    }
}

if (!function_exists('format_price')) {
    function format_price($number)
    {
        return number_format((float) $number, 0, ',', '.') . 'đ';
    }
}

if (!function_exists('dish_image_url')) {
    function dish_image_url($path)
    {
        if (empty($path)) {
            return BASE_URL . 'assets/images/hero-bowl.jpg';
        }
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }
        if (str_starts_with($path, 'assets/')) {
            return BASE_URL . $path;
        }
        return BASE_ASSETS_UPLOADS . $path;
    }
}

if (!function_exists('order_status_label')) {
    function order_status_label($status)
    {
        return match ($status) {
            'cho_xac_nhan' => 'Chờ xác nhận',
            'dang_giao'    => 'Đang giao',
            'hoan_thanh'   => 'Hoàn thành',
            'huy'          => 'Đã hủy',
            default        => 'Không xác định',
        };
    }
}

if (!function_exists('order_status_badge')) {
    function order_status_badge($status)
    {
        return match ($status) {
            'cho_xac_nhan' => '<span class="badge bg-warning text-dark"><i class="bi bi-clock-history me-1"></i>Chờ xác nhận</span>',
            'dang_giao'    => '<span class="badge bg-primary"><i class="bi bi-truck me-1"></i>Đang giao</span>',
            'hoan_thanh'   => '<span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Hoàn thành</span>',
            'huy'          => '<span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Đã hủy</span>',
            default        => '<span class="badge bg-secondary">' . htmlspecialchars($status) . '</span>',
        };
    }
}
