<?php

if (!function_exists('debug')) {
    function debug($data)
    {
        echo '<pre>';
        print_r($data);
        die;
    }
}

if (!function_exists('format_price')) {
    /**
     * Dinh dang tien te: 45000 -> 45.000đ
     */
    function format_price($price)
    {
        return number_format((float) $price, 0, ',', '.') . 'đ';
    }
}

if (!function_exists('redirect_to')) {
    /**
     * Chuyen huong den 1 URL roi dung script
     */
    function redirect_to($url)
    {
        header('Location: ' . $url);
        exit;
    }
}

if (!function_exists('tinh_phi_giao_hang')) {
    /**
     * Tinh phi giao hang:
     *  - Don tu 99.000d tro len -> mien phi (0đ)
     *  - Nho hon -> 30.000đ
     */
    function tinh_phi_giao_hang($tongTienHang)
    {
        if ((float) $tongTienHang >= MIEN_PHI_GIAO_TU) {
            return 0;
        }

        return PHI_GIAO_HANG;
    }
}

if (!function_exists('cart_details')) {
    /**
     * Doc gio hang trong session va tinh tien TUYET DOI tu CSDL.
     * Khong lay gia/gui tu trinh duyet.
     *
     * @return array [
     *   'items'          => danh sach mon da format,
     *   'so_mon'         => so dong mon,
     *   'so_luong'       => tong so luong,
     *   'tong_tien_hang' => tong tien hang,
     *   'phi_giao_hang'  => phi giao hang,
     *   'tong_thanh_toan'=> tong tien phai tra,
     * ]
     */
    function cart_details()
    {
        $ketQua = [
            'items'           => [],
            'so_mon'          => 0,
            'so_luong'        => 0,
            'tong_tien_hang'  => 0,
            'phi_giao_hang'   => 0,
            'tong_thanh_toan' => 0,
        ];

        $gioHang = CartModel::items();

        if (empty($gioHang)) {
            return $ketQua;
        }

        $productModel = new ProductModel();
        $danhSachMon  = $productModel->findManyByIds(CartModel::ids());

        foreach ($gioHang as $idMon => $dong) {
            // Mon an da bi xoa hoac ngung ban -> xoa khoi gio hang
            if (!isset($danhSachMon[$idMon])) {
                CartModel::remove($idMon);
                continue;
            }

            $mon     = $danhSachMon[$idMon];
            $soLuong = max(1, (int) $dong['so_luong']);
            $donGia  = (float) $mon['gia'];

            $ketQua['items'][] = [
                'id'           => (int) $mon['id'],
                'ten_mon'      => $mon['ten_mon'],
                'ten_danh_muc' => $mon['ten_danh_muc'],
                'hinh_anh'     => $mon['hinh_anh'],
                'don_gia'      => $donGia,
                'so_luong'     => $soLuong,
                'thanh_tien'   => $donGia * $soLuong,
                'ton_kho'      => (int) $mon['so_luong_ton'],
                'het_hang'     => (int) $mon['so_luong_ton'] < $soLuong,
            ];

            $ketQua['so_mon']       += 1;
            $ketQua['so_luong']    += $soLuong;
            $ketQua['tong_tien_hang'] += $donGia * $soLuong;
        }

        $ketQua['phi_giao_hang']   = tinh_phi_giao_hang($ketQua['tong_tien_hang']);
        $ketQua['tong_thanh_toan'] = $ketQua['tong_tien_hang'] + $ketQua['phi_giao_hang'];

        return $ketQua;
    }
}

if (!function_exists('set_flash')) {
    /**
     * Luu thong bao 1 lan (hien thi xong thi mat)
     */
    function set_flash($type, $message)
    {
        $_SESSION['flash'] = [
            'type'    => $type,
            'message' => $message,
        ];

        if (!isset($_SESSION['_flash'])) {
            $_SESSION['_flash'] = [];
        }
        $_SESSION['_flash'][$type] = $message;
    }
}

if (!function_exists('get_flash')) {
    /**
     * Lay thong bao 1 lan
     * Ho tro ca get_flash() (tra ve mang) hoac get_flash('success') (tra ve chuoi)
     * @param string|null $key
     * @return array|string|null
     */
    function get_flash($key = null)
    {
        if ($key !== null) {
            if (isset($_SESSION['_flash'][$key])) {
                $msg = $_SESSION['_flash'][$key];
                unset($_SESSION['_flash'][$key]);
                return $msg;
            }
            if (isset($_SESSION['flash']) && is_array($_SESSION['flash']) && ($_SESSION['flash']['type'] ?? '') === $key) {
                $msg = $_SESSION['flash']['message'];
                unset($_SESSION['flash']);
                return $msg;
            }
            return null;
        }

        if (empty($_SESSION['flash'])) {
            return null;
        }

        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);

        return $flash;
    }
}

if (!function_exists('field_error')) {
    /**
     * Hien thi thong bao loi cua 1 o form
     * @param array  $errors mang loi
     * @param string $key    ten o
     * @return string
     */
    function field_error($errors, $key)
    {
        if (empty($errors[$key])) {
            return '';
        }

        return '<div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> '
            . htmlspecialchars($errors[$key], ENT_QUOTES, 'UTF-8') . '</div>';
    }
}

if (!function_exists('old_value')) {
    /**
     * Lay gia tri cu da nhap de hien thi lai khi co loi
     * @param array  $old
     * @param string $key
     * @return string
     */
    function old_value($old, $key)
    {
        return htmlspecialchars($old[$key] ?? '', ENT_QUOTES, 'UTF-8');
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

if (!function_exists('is_admin')) {
    function is_admin()
    {
        return !empty($_SESSION['user']) && ($_SESSION['user']['vai_tro'] ?? '') === 'admin';
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
