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
     * Form co the gui them lua chon: size_id, topping_ids[], ghi_chu (tu trang chi tiet mon).
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

        // 3. Doc + loc lua chon (chi giu size / topping hop le cua mon nay)
        $options = $this->docLuaChon($idMon);

        // 4. Them vao session (cung lua chon thi gop dong, khac lua chon thi dong rieng)
        CartModel::add($idMon, $soLuong, $options);
        $key = CartModel::taoKey($idMon, $options);

        // 5. Khong cho phep so luong trong gio vuot ton kho
        $items = CartModel::items();
        $trongGio = (int) ($items[$key]['so_luong'] ?? 0);

        $thongBao = 'Đã thêm "' . $mon['ten_mon'] . '" vào giỏ hàng';

        if ($trongGio > $tonKho) {
            CartModel::update($key, $tonKho);
            $thongBao = 'Chỉ còn ' . $tonKho . ' phần "' . $mon['ten_mon'] . '", giỏ hàng đã được cập nhật.';
        }

        return $this->traVe([
            'success'    => true,
            'message'    => $thongBao,
            'cart_count' => CartModel::count(),
        ]);
    }

    /**
     * Cap nhat so luong 1 dong gio hang: POST ?action=cart-update
     * Gui `key` (dong gio hang) hoac `id` (id mon - phu hop form cu).
     * So luong nho hon 1 se bi dua ve 1.
     */
    public function update()
    {
        $items = CartModel::items();
        $key   = CartModel::timKey((string) ($_POST['key'] ?? $_POST['id'] ?? ''));

        if ($key === null || !isset($items[$key])) {
            return $this->traVe(['success' => false, 'message' => 'Sản phẩm không có trong giỏ hàng.']);
        }

        $idMon = (int) ($items[$key]['id'] ?? 0);

        // Huong dieu chien: 'tang' / 'giam' / '' (nhap truc tiep so luong)
        $huong = (string) ($_POST['huong'] ?? '');

        if ($huong === 'tang') {
            $soLuong = (int) $items[$key]['so_luong'] + 1;
        } elseif ($huong === 'giam') {
            $soLuong = (int) $items[$key]['so_luong'] - 1;
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
            CartModel::remove($key);
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

        CartModel::update($key, $soLuong);

        return $this->traVe([
            'success'    => true,
            'message'    => $thongBao,
            'so_luong'   => $soLuong,
            'cart_count' => CartModel::count(),
        ]);
    }

    /**
     * Xoa 1 dong khoi gio hang: POST ?action=cart-remove
     * Gui `key` hoac `id` (phu hop form cu).
     */
    public function remove()
    {
        $key = (string) ($_POST['key'] ?? $_POST['id'] ?? '');

        CartModel::remove($key);

        return $this->traVe([
            'success'    => true,
            'message'    => 'Đã xóa sản phẩm khỏi giỏ hàng.',
            'cart_count' => CartModel::count(),
        ]);
    }

    /**
     * Doc lua chon gui len (size / topping / ghi chu) va loc theo du lieu CSDL.
     * Chi giu cac lua chon con ban cua mon -> khong tin du lieu tu trinh duyet.
     * @param int $idMon
     * @return array ['size_id'=>int, 'topping_ids'=>[], 'ghi_chu'=>string]
     */
    private function docLuaChon($idMon)
    {
        $dishModel = new DishModel();

        // Size: chon 1 -> neu khong con ban thi bo qua
        $sizeChon = (int) ($_POST['size_id'] ?? 0);
        $sizeHopLe = 0;

        if ($sizeChon > 0) {
            foreach ($dishModel->getSizes($idMon) as $size) {
                if ((int) $size['id'] === $sizeChon) {
                    $sizeHopLe = $sizeChon;
                    break;
                }
            }
        }

        // Topping: chon nhieu -> chi giu cac id con ban
        $topHopLe  = [];
        $topIds = array_map('intval', (array) ($_POST['topping_ids'] ?? []));

        foreach ($dishModel->getToppings($idMon) as $top) {
            if (in_array((int) $top['id'], $topIds, true)) {
                $topHopLe[] = (int) $top['id'];
            }
        }

        return [
            'size_id'     => $sizeHopLe,
            'topping_ids' => $topHopLe,
            'ghi_chu'     => mb_substr(trim((string) ($_POST['ghi_chu'] ?? '')), 0, 255, 'UTF-8'),
        ];
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
