<?php

/**
 * Controller GIO HANG
 * - Xem gio hang
 * - Them / cap nhat so luong / xoa mon
 * - Gio hang luu trong PHP Session (xem models/CartModel.php)
 */
class CartController
{
    /**
     * Trang gio hang: GET ?action=cart
     */
    public function index()
    {
        $title = 'Giỏ hàng - AnLaMe';
        $view  = 'cart';
        $cart  = cart_details();

        require_once PATH_VIEW_MAIN;
    }

    /**
     * Them mon an vao gio hang: POST ?action=cart-add
     * Co 2 kieu tra ve:
     *  - Neu goi tu JavaScript (ajax) -> tra JSON
     *  - Neu submit bang form thuong -> chuyen huong ve trang gio hang
     */
    public function add()
    {
        $idMon   = (int) ($_POST['id'] ?? 0);
        $soLuong = (int) ($_POST['so_luong'] ?? 1);

        if ($soLuong < 1) {
            $soLuong = 1;
        }

        // 1. Kiem tra mon an co ton tai trong database khong
        $productModel = new ProductModel();
        $mon = $productModel->findById($idMon);

        if (!$mon) {
            return $this->traVe(['success' => false, 'message' => 'Món ăn không tồn tại hoặc đã ngừng bán.']);
        }

        // 2. Kiem tra ton kho
        $tonKho = (int) $mon['so_luong_ton'];

        if ($tonKho <= 0) {
            return $this->traVe(['success' => false, 'message' => 'Món "' . $mon['ten_mon'] . '" đã hết hàng.']);
        }

        // 3. Them vao session
        CartModel::add($idMon, $soLuong);

        // 4. Khong cho phep so luong trong gio vuot ton kho
        $items = CartModel::items();
        $trongGio = (int) ($items[$idMon]['so_luong'] ?? 0);

        $thongBao = 'Đã thêm "' . $mon['ten_mon'] . '" vào giỏ hàng';

        if ($trongGio > $tonKho) {
            CartModel::update($idMon, $tonKho);
            $thongBao = 'Chỉ còn ' . $tonKho . ' phần "' . $mon['ten_mon'] . '", giỏ hàng đã được cập nhật.';
        }

        return $this->traVe([
            'success'    => true,
            'message'    => $thongBao,
            'cart_count' => CartModel::count(),
        ]);
    }

    /**
     * Cap nhat so luong 1 mon: POST ?action=cart-update
     * So luong nho hon 1 se bi dua ve 1.
     */
    public function update()
    {
        $idMon = (int) ($_POST['id'] ?? 0);
        $items = CartModel::items();

        if (!isset($items[$idMon])) {
            return $this->traVe(['success' => false, 'message' => 'Sản phẩm không có trong giỏ hàng.']);
        }

        // Huong dieu chien: 'tang' / 'giam' / '' (nhap truc tiep so luong)
        $huong = (string) ($_POST['huong'] ?? '');

        if ($huong === 'tang') {
            $soLuong = (int) $items[$idMon]['so_luong'] + 1;
        } elseif ($huong === 'giam') {
            $soLuong = (int) $items[$idMon]['so_luong'] - 1;
        } else {
            $soLuong = (int) ($_POST['so_luong'] ?? 1);
        }

        // Khong cho phep so luong < 1
        if ($soLuong < 1) {
            $soLuong = 1;
        }

        // Khong cho vuot ton kho
        $productModel = new ProductModel();
        $mon = $productModel->findById($idMon);

        if (!$mon) {
            CartModel::remove($idMon);
            return $this->traVe([
                'success'    => true,
                'message'    => 'Sản phẩm không còn bán, đã xóa khỏi giỏ hàng.',
                'cart_count' => CartModel::count(),
            ]);
        }

        $tonKho = (int) $mon['so_luong_ton'];
        $thongBao = 'Đã cập nhật giỏ hàng';

        if ($tonKho < 1) {
            $soLuong = 1;
            $thongBao = 'Món "' . $mon['ten_mon'] . '" đã hết hàng.';
        } elseif ($soLuong > $tonKho) {
            $soLuong = $tonKho;
            $thongBao = 'Chỉ còn ' . $tonKho . ' phần "' . $mon['ten_mon'] . '".';
        }

        CartModel::update($idMon, $soLuong);

        return $this->traVe([
            'success'    => true,
            'message'    => $thongBao,
            'so_luong'   => $soLuong,
            'cart_count' => CartModel::count(),
        ]);
    }

    /**
     * Xoa 1 mon khoi gio hang: POST ?action=cart-remove
     */
    public function remove()
    {
        $idMon = (int) ($_POST['id'] ?? 0);

        CartModel::remove($idMon);

        return $this->traVe([
            'success'    => true,
            'message'    => 'Đã xóa sản phẩm khỏi giỏ hàng.',
            'cart_count' => CartModel::count(),
        ]);
    }

    /**
     * Xoa het gio hang: POST ?action=cart-clear
     */
    public function clear()
    {
        CartModel::clear();
        set_flash('success', 'Đã xóa toàn bộ giỏ hàng.');

        redirect_to(BASE_URL . '?action=cart');
    }

    /**
     * Tra ve JSON neu la yeu cau ajax, nguoc lai chuyen huong.
     * @param array $data
     * @return void
     */
    private function traVe(array $data)
    {
        if ($this->laAjax()) {
            // Cong them thong tin gio hang moi nhat de trang gio hang cap nhat tien
            if (!empty($data['success'])) {
                $data = array_merge($this->duLieuGioHang(), $data);
            }

            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($data, JSON_UNESCAPED_UNICODE);
            exit;
        }

        // Truong hop submit bang form thuong (khong co JavaScript)
        if (!empty($data['success'])) {
            set_flash('success', $data['message'] ?? 'Cập nhật giỏ hàng thành công.');
        } else {
            set_flash('error', $data['message'] ?? 'Có lỗi xảy ra, vui lòng thử lại.');
        }

        redirect_to(BASE_URL . '?action=cart');
    }

    /**
     * Thong tin gio hang de tra ve cho JavaScript.
     * @return array
     */
    private function duLieuGioHang()
    {
        $chiTiet = cart_details();

        return [
            'cart_count'      => $chiTiet['so_luong'],
            'so_mon'          => $chiTiet['so_mon'],
            'tong_tien_hang'  => $chiTiet['tong_tien_hang'],
            'phi_giao_hang'   => $chiTiet['phi_giao_hang'],
            'tong_thanh_toan' => $chiTiet['tong_thanh_toan'],
        ];
    }

    /**
     * Kiem tra yeu co phai gui tu JavaScript khong.
     * @return bool
     */
    private function laAjax()
    {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            return true;
        }

        return ($_POST['ajax'] ?? '') === '1';
    }
}
