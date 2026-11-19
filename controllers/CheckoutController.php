<?php

/**
 * Controller DAT HANG
 * - Hien thi form thong tin khach hang + tom tat don hang
 * - Kiem tra du lieu (bat buoc, so dien thoai, email)
 * - Tinh tien TOAN BO tu CSDL (khong lay gia tu trinh duyet)
 * - Luu don hang bang MySQL TRANSACTION
 * - Trang dat hang thanh cong
 */
class CheckoutController
{
    /**
     * Trang dat hang (form): GET ?action=checkout
     */
    public function index()
    {
        $cart = cart_details();

        // Gio hang rong thi khong cho vao trang dat hang
        if ($cart['so_mon'] === 0) {
            set_flash('error', 'Giỏ hàng đang trống, vui lòng chọn món trước.');
            redirect_to(BASE_URL . '?action=cart');
        }

        $title  = 'Đặt hàng - AnLaMe';
        $view   = 'checkout';
        $errors = [];
        $old    = [];

        require_once PATH_VIEW_MAIN;
    }

    /**
     * Xuly dat hang: POST ?action=checkout-post
     */
    public function placeOrder()
    {
        $cart = cart_details();

        if ($cart['so_mon'] === 0) {
            set_flash('error', 'Giỏ hàng đang trống, vui lòng chọn món trước.');
            redirect_to(BASE_URL . '?action=cart');
        }

        // 1. Kiem tra thong tin khach hang
        $old    = $this->layDuLieuForm();
        $errors = $this->kiemTraDuLieu($old);

        // 2. Co loi thi hien thi lai form + thong bao loi
        if (!empty($errors)) {
            $title = 'Đặt hàng - AnLaMe';
            $view  = 'checkout';

            require_once PATH_VIEW_MAIN;
            return;
        }

        // 3. Tinh phi giao hang tu tong tien hang (lay tu CSDL)
        $phiGiaoHang = tinh_phi_giao_hang($cart['tong_tien_hang']);

        // 4. Tao don hang (transaction)
        try {
            $orderModel = new OrderModel();

            $donHang = $orderModel->createOrder(
                [
                    'ho_ten'        => $old['ho_ten'],
                    'so_dien_thoai' => $old['so_dien_thoai'],
                    'email'         => $old['email'],
                    'dia_chi'       => $old['dia_chi'],
                    'ghi_chu'       => $old['ghi_chu'],
                ],
                array_map(function ($item) {
                    return ['id' => $item['id'], 'so_luong' => $item['so_luong']];
                }, $cart['items']),
                $phiGiaoHang,
                (string) ($_POST['thanh_toan'] ?? 'tien_mat')
            );
        } catch (Exception $e) {
            // Loi (het ton kho, loi CSDL...) -> hien thi lai form
            $title  = 'Đặt hàng - AnLaMe';
            $view   = 'checkout';
            $errors = ['top' => $e->getMessage()];

            require_once PATH_VIEW_MAIN;
            return;
        }

        // 5. Thanh cong: xoa gio hang va ghi nho ma don de hien thi trang success
        CartModel::clear();
        $_SESSION['ma_don_hang'] = $donHang['ma_don'];

        // Post/Redirect/Get: tranh truong hop F5 tao don hang lan nua
        redirect_to(BASE_URL . '?action=order-success');
    }

    /**
     * Trang dat hang thanh cong: GET ?action=order-success
     */
    public function success()
    {
        $maDon = (string) ($_SESSION['ma_don_hang'] ?? '');

        $orderModel = new OrderModel();
        $donHang    = $orderModel->findByMaDon($maDon);

        // Khong tim thay don hang thi ve trang chu
        if (!$donHang) {
            set_flash('error', 'Không tìm thấy đơn hàng, vui lòng đặt lại.');
            redirect_to(BASE_URL);
        }

        $donHang['donhang']['tong_thanh_toan'] =
            (float) $donHang['donhang']['tong_tien'] + (float) $donHang['donhang']['phi_giao_hang'];

        $title = 'Đặt hàng thành công - AnLaMe';
        $view  = 'order-success';

        require_once PATH_VIEW_MAIN;
    }

    public function detail()
    {
        $maDonYeuCau = $_GET['ma_don'] ?? $_SESSION['ma_don_hang'] ?? '';
        $maDon = is_string($maDonYeuCau) ? trim($maDonYeuCau) : '';

        $donHang = null;

        if ($maDon !== '') {
            $donHang = (new OrderModel())->findByMaDon($maDon);

            if ($donHang) {
                $donHang['donhang']['tong_thanh_toan'] =
                    (float) $donHang['donhang']['tong_tien'] + (float) $donHang['donhang']['phi_giao_hang'];
            }
        }

        $title   = 'Chi tiết đơn hàng - AnLaMe';
        $view    = 'order-detail';
        $maDon   = $donHang ? $donHang['donhang']['ma_don'] : $maDon;

        require_once PATH_VIEW_MAIN;
    }

    /**
     * Lay du lieu tu form.
     * @return array
     */
    private function layDuLieuForm()
    {
        return [
            'ho_ten'        => trim((string) ($_POST['ho_ten'] ?? '')),
            'so_dien_thoai' => trim((string) ($_POST['so_dien_thoai'] ?? '')),
            'email'         => trim((string) ($_POST['email'] ?? '')),
            'dia_chi'       => trim((string) ($_POST['dia_chi'] ?? '')),
            'ghi_chu'       => trim((string) ($_POST['ghi_chu'] ?? '')),
        ];
    }

    /**
     * Kiem tra thong tin khach hang (thuc hien tren PHP).
     * @param array $data
     * @return array ['ten_field' => 'Thong bao loi', ...]
     */
    private function kiemTraDuLieu(array $data)
    {
        $errors = [];

        // Ho ten
        if ($data['ho_ten'] === '') {
            $errors['ho_ten'] = 'Vui lòng nhập họ và tên.';
        } elseif (mb_strlen($data['ho_ten']) < 2) {
            $errors['ho_ten'] = 'Họ và tên cần ít nhất 2 ký tự.';
        } elseif (mb_strlen($data['ho_ten']) > 150) {
            $errors['ho_ten'] = 'Họ và tên không được quá 150 ký tự.';
        }

        // So dien thoai (bat dau bang 0 hoac +84, 10 - 11 so)
        if ($data['so_dien_thoai'] === '') {
            $errors['so_dien_thoai'] = 'Vui lòng nhập số điện thoại.';
        } elseif (!preg_match('/^(0|\+84)[0-9]{9,10}$/', $data['so_dien_thoai'])) {
            $errors['so_dien_thoai'] = 'Số điện thoại không đúng định dạng (ví dụ: 0901234567).';
        }

        // Email
        if ($data['email'] === '') {
            $errors['email'] = 'Vui lòng nhập email.';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email không đúng định dạng (ví dụ: ban@gmail.com).';
        }

        // Dia chi giao hang
        if ($data['dia_chi'] === '') {
            $errors['dia_chi'] = 'Vui lòng nhập địa chỉ giao hàng.';
        } elseif (mb_strlen($data['dia_chi']) < 10) {
            $errors['dia_chi'] = 'Địa chỉ giao hàng cần ít nhất 10 ký tự.';
        } elseif (mb_strlen($data['dia_chi']) > 500) {
            $errors['dia_chi'] = 'Địa chỉ giao hàng không được quá 500 ký tự.';
        }

        // Ghi chu: khong bat buoc
        if (mb_strlen($data['ghi_chu']) > 500) {
            $errors['ghi_chu'] = 'Ghi chú không được quá 500 ký tự.';
        }

        return $errors;
    }
}
