<?php

/**
 * Model GIO HANG
 * - Gio hang luu trong PHP Session (khong luu vao CSDL)
 * - Chi luu: id mon an + so luong + lua chon (size / topping / ghi chu).
 *   Gia, ten, anh... luon lay tu CSDL khi hien thi (khong tin tien tu trinh duyet).
 *
 * Cau truc session:
 *   $_SESSION['cart'][key] = [
 *       'id'           => id_mon,
 *       'so_luong'     => 2,
 *       'size_id'      => 0,             // 0 = khong chon size
 *       'topping_ids'  => [3, 7],        // cac id topping da chon
 *       'ghi_chu'      => ' 少 it gung',   // ghi chu rieng (co the rong)
 *   ]
 *
 *   key = "<id>|<size_id>|<topping_ids,cach,dau,phay>|<md5 ghi_chu>"
 *   -> cung mon nhung size / topping / ghi chu khac la 2 dong rieng biet.
 */
class CartModel
{
    /**
     * Khoi tao gio hang neu chua co.
     */
    public static function init()
    {
        if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
    }

    /**
     * Tao key cho 1 dong gio hang tu id + lua chon.
     * @return string
     */
    public static function taoKey($productId, array $options = [])
    {
        $id      = (int) $productId;
        $sizeId  = (int) ($options['size_id'] ?? 0);
        $topping = array_values(array_unique(array_filter(array_map('intval', $options['topping_ids'] ?? []))));
        sort($topping);
        $note    = trim((string) ($options['ghi_chu'] ?? ''));

        return $id . '|' . $sizeId . '|' . implode(',', $topping) . '|' . md5($note);
    }

    /**
     * Tim key cua 1 dong: khop key truc tiep, hoac tim theo id mon (gio cu / form gui id).
     * @return string|null
     */
    public static function timKey($value)
    {
        self::init();

        $value = (string) $value;

        // Key truc tiep: key moi ("1|2|3|md5") hoac id so cua gio cu
        if ($value !== '' && isset($_SESSION['cart'][$value])) {
            return $value;
        }

        // Gui len id mon -> lay dong dau tien cua mon do (giu hanh vi cu)
        $idTim = (int) $value;
        if ($idTim > 0) {
            foreach ($_SESSION['cart'] as $key => $item) {
                if ((int) ($item['id'] ?? 0) === $idTim) {
                    return (string) $key;
                }
            }
        }

        return null;
    }

    /**
     * Lay toan bo gio hang.
     * @return array
     */
    public static function items()
    {
        self::init();
        return $_SESSION['cart'];
    }

    /**
     * Kiem tra gio hang co rong khong.
     * @return bool
     */
    public static function isEmpty()
    {
        self::init();
        return count($_SESSION['cart']) === 0;
    }

    /**
     * Tong so luong san pham trong gio (dung hien thi so o badge gio hang).
     * @return int
     */
    public static function count()
    {
        self::init();

        $total = 0;
        foreach ($_SESSION['cart'] as $item) {
            $total += (int) $item['so_luong'];
        }

        return $total;
    }

    /**
     * Them mon an vao gio hang (gop dong cung lua chon).
     * @param int $productId
     * @param int $quantity so luong them (mac dinh 1)
     * @param array $options ['size_id'=>int, 'topping_ids'=>[], 'ghi_chu'=>string]
     * @return void
     */
    public static function add($productId, $quantity = 1, array $options = [])
    {
        self::init();

        $productId = (int) $productId;
        $quantity  = max(1, (int) $quantity);

        if ($productId <= 0) {
            return;
        }

        $luu = [
            'id'           => $productId,
            'so_luong'     => $quantity,
            'size_id'      => (int) ($options['size_id'] ?? 0),
            'topping_ids'  => array_values(array_unique(array_filter(array_map('intval', $options['topping_ids'] ?? [])))),
            'ghi_chu'      => mb_substr(trim((string) ($options['ghi_chu'] ?? '')), 0, 255, 'UTF-8'),
        ];

        $key = self::taoKey($productId, $options);

        if (isset($_SESSION['cart'][$key])) {
            // Neu cung lua chon da co trong gio thi cong so luong
            $_SESSION['cart'][$key]['so_luong'] += $quantity;
        } else {
            $_SESSION['cart'][$key] = $luu;
        }
    }

    /**
     * Cap nhat so luong cua 1 dong gio hang (nhan key hoac id mon).
     * So luong nho hon 1 thi se bi dua ve 1 (khong cho phep so luong < 1).
     * @param string|int $key key dong hoac id mon (gio cu)
     * @param int $quantity
     * @return void
     */
    public static function update($key, $quantity)
    {
        self::init();

        $key = self::timKey($key);

        if ($key === null) {
            return;
        }

        $quantity = (int) $quantity;

        // Khong cho phep so luong nho hon 1
        if ($quantity < 1) {
            $quantity = 1;
        }

        $_SESSION['cart'][$key]['so_luong'] = $quantity;
    }

    /**
     * Xoa 1 dong khoi gio hang (nhan key hoac id mon).
     * @param string|int $key
     * @return void
     */
    public static function remove($key)
    {
        self::init();

        $key = self::timKey($key);

        if ($key !== null && isset($_SESSION['cart'][$key])) {
            unset($_SESSION['cart'][$key]);
        }
    }

    /**
     * Xoa het gio hang (dung sau khi dat hang thanh cong).
     * @return void
     */
    public static function clear()
    {
        $_SESSION['cart'] = [];
    }

    /**
     * Lay danh sach id mon an trong gio (khong trung).
     * @return array
     */
    public static function ids()
    {
        $ids = [];
        foreach (self::items() as $item) {
            $ids[] = (int) ($item['id'] ?? 0);
        }

        return array_values(array_unique(array_filter($ids)));
    }
}
