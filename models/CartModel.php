<?php

/**
 * Model GIO HANG
 * - Gio hang luu trong PHP Session (khong luu vao CSDL)
 * - Chi luu: id mon an + so luong.
 *   Gia, ten, anh... luon lay tu CSDL khi hien thi (khong tin tien tu trinh duyet).
 *
 * Cau truc session:
 *   $_SESSION['cart'][id_mon] = ['id' => id_mon, 'so_luong' => 2]
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
     * Them mon an vao gio hang.
     * @param int $productId
     * @param int $quantity so luong them (mac dinh 1)
     * @return void
     */
    public static function add($productId, $quantity = 1)
    {
        self::init();

        $productId = (int) $productId;
        $quantity  = max(1, (int) $quantity);

        if ($productId <= 0) {
            return;
        }

        if (isset($_SESSION['cart'][$productId])) {
            // Neu mon da co trong gio thi cong so luong
            $_SESSION['cart'][$productId]['so_luong'] += $quantity;
        } else {
            $_SESSION['cart'][$productId] = [
                'id'       => $productId,
                'so_luong' => $quantity,
            ];
        }
    }

    /**
     * Cap nhat so luong cua 1 mon.
     * So luong nho hon 1 thi se bi dua ve 1 (khong cho phep so luong < 1).
     * @param int $productId
     * @param int $quantity
     * @return void
     */
    public static function update($productId, $quantity)
    {
        self::init();

        $productId = (int) $productId;

        if (!isset($_SESSION['cart'][$productId])) {
            return;
        }

        $quantity = (int) $quantity;

        // Khong cho phep so luong nho hon 1
        if ($quantity < 1) {
            $quantity = 1;
        }

        $_SESSION['cart'][$productId]['so_luong'] = $quantity;
    }

    /**
     * Xoa 1 mon khoi gio hang.
     * @param int $productId
     * @return void
     */
    public static function remove($productId)
    {
        self::init();

        $productId = (int) $productId;

        if (isset($_SESSION['cart'][$productId])) {
            unset($_SESSION['cart'][$productId]);
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
     * Lay danh sach id mon an trong gio.
     * @return array
     */
    public static function ids()
    {
        return array_map('intval', array_keys(self::items()));
    }
}
