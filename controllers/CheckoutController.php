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
        // Bat buoc dang nhap de dat hang (don hang gan voi tai khoan, xem lich su duoc)
        if (empty($_SESSION['user']['id'])) {
            set_flash('error', 'Vui lòng đăng nhập để đặt hàng.');
            redirect_to(BASE_URL . '?action=login&redirect=checkout');
            return;
        }

        $cart = cart_details();

        // Gio hang rong thi khong cho vao trang dat hang
        if ($cart['so_mon'] === 0) {
            set_flash('error', 'Giỏ hàng đang trống, vui lòng chọn món trước.');
            redirect_to(BASE_URL . '?action=cart');
            return;
        }

        $title  = 'Đặt hàng - AnLaMe';
        $view   = 'checkout';
        $errors = [];
        $old    = [];

        // So dia chi da luu (chi khi khach hang da dang nhap)
        $phienDiaChi        = $this->duLieuSoDiaChi(null);
        $dsDiaChi           = $phienDiaChi['dsDiaChi'];
        $diachiDuocChon     = $phienDiaChi['diachiDuocChon'];

        require_once PATH_VIEW_MAIN;
    }

    /**
     * Xuly dat hang: POST ?action=checkout-post
     */
    public function placeOrder()
    {
        // Bat buoc dang nhap (khong cho dat hang voi phien khach)
        if (empty($_SESSION['user']['id'])) {
            set_flash('error', 'Vui lòng đăng nhập để đặt hàng.');
            redirect_to(BASE_URL . '?action=login&redirect=checkout');
            return;
        }

        $cart = cart_details();

        if ($cart['so_mon'] === 0) {
            set_flash('error', 'Giỏ hàng đang trống, vui lòng chọn món trước.');
            redirect_to(BASE_URL . '?action=cart');
            return;
        }

        // 1. Kiem tra CSRF + thong tin khach hang
        $old    = $this->layDuLieuForm();
        $errors = [];

        if (!is_valid_csrf_token($_POST['_csrf_token'] ?? null)) {
            $errors['top'] = 'Phiên làm việc đã hết hạn. Vui lòng tải lại trang và thử lại.';
        }

        // Che do "dia chi da luu": lay ten / sdt / dia chi tu SO DIA CHI
        $diachiDaLuu = null;

        if ($old['che_do_dia_chi'] === 'da_luu') {
            $userId = (int) ($_SESSION['user']['id'] ?? 0);

            if ($userId > 0 && $old['diachi_id'] > 0) {
                $diachiDaLuu = (new AddressModel())->findOwned($old['diachi_id'], $userId);
            }

            if ($diachiDaLuu) {
                $old['ho_ten']       = $diachiDaLuu['ten_nguoi_nhan'];
                $old['so_dien_thoai'] = $diachiDaLuu['so_dien_thoai'];
                $old['dia_chi_full'] = $diachiDaLuu['dia_chi_chi_tiet'];
                $old['diachi_id']    = (int) $diachiDaLuu['id'];
            } else {
                $old['diachi_id']    = 0;
                $old['dia_chi_full'] = '';
                $errors['top']       = $errors['top'] ?? 'Bạn chưa chọn địa chỉ giao hàng hợp lệ. Vui lòng chọn lại.';
            }
        }

        $errors = array_merge($errors, $this->kiemTraDuLieu($old));

        // 2. Co loi thi hien thi lai form + thong bao loi
        if (!empty($errors)) {
            $title = 'Đặt hàng - AnLaMe';
            $view  = 'checkout';

            $phienDiaChi        = $this->duLieuSoDiaChi($old);
            $dsDiaChi           = $phienDiaChi['dsDiaChi'];
            $diachiDuocChon     = $phienDiaChi['diachiDuocChon'];

            require_once PATH_VIEW_MAIN;
            return;
        }

        // 3. Tinh phi giao hang tu tong tien hang (lay tu CSDL)
        $phiGiaoHang = tinh_phi_giao_hang($cart['tong_tien_hang']);

        // 4. Dia chi gan voi don hang:
        //    - Dang chon dia chi da luu -> dung luon id do
        //    - Nhap dia chi moi + tick "Luu vao so dia chi" -> luu roi gan vao don
        $diachiId = $old['che_do_dia_chi'] === 'da_luu' ? (int) $old['diachi_id'] : null;
        $userId   = (int) ($_SESSION['user']['id'] ?? 0);

        if ($diachiId === null && $userId > 0 && !empty($old['luu_diachi'])) {
            try {
                $diachiId = (new AddressModel())->create($userId, [
                    'ten_nguoi_nhan'   => $old['ho_ten'],
                    'so_dien_thoai'    => $old['so_dien_thoai'],
                    'dia_chi_chi_tiet' => $this->ghepDiaChi($old),
                    'ghi_chu'          => null,
                    'tinh_id'          => $old['tinh_id'] ?: null,
                    'huyen_id'         => $old['huyen_id'] ?: null,
                    'xa_id'            => $old['xa_id'] ?: null,
                    'so_nha'           => $old['so_nha'] ?: null,
                    'mac_dinh'         => 0,
                ]);
                $_SESSION['chon_diachi_id'] = $diachiId;
            } catch (Exception $e) {
                // Khong luu duoc so dia chi van dat binh thuong
                $diachiId = null;
            }
        }

        // 5. Tao don hang (transaction)
        try {
            $orderModel = new OrderModel();

            $donHang = $orderModel->createOrder(
                [
                    'ho_ten'        => $old['ho_ten'],
                    'so_dien_thoai' => $old['so_dien_thoai'],
                    'email'         => $old['email'],
                    'dia_chi'       => $this->ghepDiaChi($old),
                    'ghi_chu'       => $old['ghi_chu'],
                    'tinh_id'       => $old['tinh_id'] ?: null,
                    'huyen_id'      => $old['huyen_id'] ?: null,
                    'xa_id'         => $old['xa_id'] ?: null,
                    'so_nha'        => $old['so_nha'] ?: null,
                    'nguoidung_id'  => $userId > 0 ? $userId : null,
                    'diachi_id'     => $diachiId,
                ],
                array_map(function ($item) {
                    // don_gia da gom size / topping (cart_details tinh tu CSDL)
                    return [
                        'id'            => $item['id'],
                        'so_luong'      => $item['so_luong'],
                        'don_gia'       => $item['don_gia'],
                        'size_ten'      => (string) ($item['size_ten'] ?? ''),
                        'topping_list'  => (string) ($item['topping_list'] ?? ''),
                        'ghi_chu'       => (string) ($item['ghi_chu'] ?? ''),
                    ];
                }, $cart['items']),
                $phiGiaoHang,
                (string) ($_POST['thanh_toan'] ?? 'tien_mat')
            );
        } catch (Exception $e) {
            // Loi (het ton kho, loi CSDL...) -> hien thi lai form
            $title  = 'Đặt hàng - AnLaMe';
            $view   = 'checkout';
            $errors = ['top' => $e->getMessage()];

            $phienDiaChi        = $this->duLieuSoDiaChi($old);
            $dsDiaChi           = $phienDiaChi['dsDiaChi'];
            $diachiDuocChon     = $phienDiaChi['diachiDuocChon'];

            require_once PATH_VIEW_MAIN;
            return;
        }

        // 6. Thanh cong: xoa gio hang va ghi nho ma don de hien thi trang success
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

    /**
     * Lay du lieu tu form.
     * @return array
     */
    private function layDuLieuForm()
    {
        $cheDoRaw = (string) ($_POST['che_do_dia_chi'] ?? 'moi');

        // Radio dia chi da luu co gia tri "da_luu|<id>" (vi du: da_luu|5)
        $cheDo     = $cheDoRaw;
        $diachiId  = 0;

        if (str_starts_with($cheDoRaw, 'da_luu')) {
            $cheDo    = 'da_luu';
            $diachiId = (int) (explode('|', $cheDoRaw)[1] ?? 0);
        }

        $luuDiachi = !empty($_POST['luu_diachi']) ? 1 : 0;

        // Che do CU: khong luu id dia chinh (khach go tay toan bo)
        if ($cheDo === 'cu') {
            return [
                'ho_ten'         => trim((string) ($_POST['ho_ten'] ?? '')),
                'so_dien_thoai'  => trim((string) ($_POST['so_dien_thoai'] ?? '')),
                'email'          => trim((string) ($_POST['email'] ?? '')),
                'che_do_dia_chi' => 'cu',
                'diachi_id'      => 0,
                'luu_diachi'     => $luuDiachi,
                'dia_chi'        => trim((string) ($_POST['dia_chi'] ?? '')),
                'tinh_id'        => '',
                'huyen_id'       => '',
                'xa_id'          => '',
                'so_nha'         => '',
                'ghi_chu'        => trim((string) ($_POST['ghi_chu'] ?? '')),
            ];
        }

        return [
            'ho_ten'         => trim((string) ($_POST['ho_ten'] ?? '')),
            'so_dien_thoai'  => trim((string) ($_POST['so_dien_thoai'] ?? '')),
            'email'          => trim((string) ($_POST['email'] ?? '')),
            'che_do_dia_chi' => $cheDo,
            'diachi_id'      => $diachiId,
            'luu_diachi'     => $luuDiachi,
            'dia_chi'        => trim((string) ($_POST['dia_chi'] ?? '')),
            'tinh_id'        => trim((string) ($_POST['tinh_id'] ?? '')),
            'huyen_id'       => trim((string) ($_POST['huyen_id'] ?? '')),
            'xa_id'          => trim((string) ($_POST['xa_id'] ?? '')),
            'so_nha'         => trim((string) ($_POST['so_nha'] ?? '')),
            'ghi_chu'        => trim((string) ($_POST['ghi_chu'] ?? '')),
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
        $laDiaChiDaLuu = ($data['che_do_dia_chi'] ?? '') === 'da_luu';

        // Ho ten / so dien thoai: che do "dia chi da luu" lay tu so dia chi
        // (khong lay tu o nhap tren form)
        if (!$laDiaChiDaLuu) {
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
        }

        // Email
        if ($data['email'] === '') {
            $errors['email'] = 'Vui lòng nhập email.';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email không đúng định dạng (ví dụ: ban@gmail.com).';
        }

        if ($laDiaChiDaLuu) {
            // Dia chi lay tu SO DIA CHI da luu (controller da nap vao $data['dia_chi_full'])
            if ((int) ($data['diachi_id'] ?? 0) <= 0) {
                $errors['top'] = 'Vui lòng chọn một địa chỉ trong sổ địa chỉ.';
            } elseif (mb_strlen((string) ($data['dia_chi_full'] ?? '')) < 10) {
                $errors['top'] = 'Địa chỉ đã lưu không hợp lệ, vui lòng sửa lại địa chỉ.';
            }
        } elseif ($data['che_do_dia_chi'] === 'cu') {
            // Che do CU: khach go tay toan bo dia chi
            if ($data['dia_chi'] === '') {
                $errors['dia_chi'] = 'Vui lòng nhập địa chỉ giao hàng.';
            } elseif (mb_strlen($data['dia_chi']) < 10) {
                $errors['dia_chi'] = 'Địa chỉ giao hàng cần ít nhất 10 ký tự.';
            } elseif (mb_strlen($data['dia_chi']) > 500) {
                $errors['dia_chi'] = 'Địa chỉ giao hàng không được quá 500 ký tự.';
            }
        } else {
            // Che do MOI: chon Tinh / Huyen / Xa + nhap so nha, duong
            if ($data['tinh_id'] === '') {
                $errors['tinh_id'] = 'Vui lòng chọn Tỉnh / Thành phố.';
            }

            if ($data['huyen_id'] === '') {
                $errors['huyen_id'] = 'Vui lòng chọn Quận / Huyện.';
            }

            if ($data['xa_id'] === '') {
                $errors['xa_id'] = 'Vui lòng chọn Phường / Xã.';
            }

            if ($data['so_nha'] === '') {
                $errors['so_nha'] = 'Vui lòng nhập số nhà, đường.';
            } elseif (mb_strlen($data['so_nha']) > 255) {
                $errors['so_nha'] = 'Số nhà, đường không được quá 255 ký tự.';
            }

            // Id co ton tai trong du lieu dia chinh khong?
            if (empty($errors)) {
                $ten = dia_chinh_ten($data['tinh_id'], $data['huyen_id'], $data['xa_id']);

                if ($ten['tinh'] === '' || $ten['huyen'] === '' || $ten['xa'] === '') {
                    $errors['xa_id'] = 'Phường / Xã vừa chọn không hợp lệ, vui lòng chọn lại.';
                }
            }

            // Dia chi day du phai du 10 ky tu (giu quy tac cu)
            if (empty($errors)) {
                $ten        = dia_chinh_ten($data['tinh_id'], $data['huyen_id'], $data['xa_id']);
                $diaChiDayDu = dia_chinh_ghep($data['so_nha'], $ten['xa'], $ten['huyen'], $ten['tinh']);

                if (mb_strlen($diaChiDayDu) < 10) {
                    $errors['so_nha'] = 'Địa chỉ giao hàng cần ít nhất 10 ký tự.';
                }
            }
        }

        // Ghi chu: khong bat buoc
        if (mb_strlen($data['ghi_chu']) > 500) {
            $errors['ghi_chu'] = 'Ghi chú không được quá 500 ký tự.';
        }

        return $errors;
    }

    /**
     * Ghep dia chi day du (che do MOI) de luu vao donhang.dia_chi_giao.
     * Che do CU giu nguyen chuoi khach da go.
     *
     * @param array $data
     * @return string
     */
    private function ghepDiaChi(array $data)
    {
        // Dia chi da luu: dung luon chuoi dia chi da luu trong so dia chi
        if (($data['che_do_dia_chi'] ?? '') === 'da_luu') {
            return (string) ($data['dia_chi_full'] ?? '');
        }

        if ($data['che_do_dia_chi'] === 'cu') {
            return $data['dia_chi'];
        }

        $ten = dia_chinh_ten($data['tinh_id'], $data['huyen_id'], $data['xa_id']);

        return dia_chinh_ghep($data['so_nha'], $ten['xa'], $ten['huyen'], $ten['tinh']);
    }

    /**
     * Du lieu SO DIA CHI cho view (chi lay khi khach hang da dang nhap).
     *
     * @param array|null $old du lieu form da nhap (neu co) de giu nguyen lua chon
     * @return array ['dsDiaChi' => [...], 'diachiDuocChon' => row|null]
     */
    private function duLieuSoDiaChi(?array $old)
    {
        $dsDiaChi       = [];
        $diachiDuocChon = null;
        $userId         = (int) ($_SESSION['user']['id'] ?? 0);

        if ($userId <= 0) {
            return ['dsDiaChi' => $dsDiaChi, 'diachiDuocChon' => null];
        }

        $addressModel = new AddressModel();
        $dsDiaChi     = $addressModel->getByUser($userId);

        if (count($dsDiaChi) === 0) {
            return ['dsDiaChi' => $dsDiaChi, 'diachiDuocChon' => null];
        }

        // Dia chi dang duoc chon: lay tu form (neu dang loi) -> session -> mac dinh
        $chonId = 0;

        if ($old !== null && ($old['che_do_dia_chi'] ?? '') === 'da_luu') {
            $chonId = (int) ($old['diachi_id'] ?? 0);
        }

        if ($chonId <= 0) {
            $chonId = (int) ($_SESSION['chon_diachi_id'] ?? 0);
        }

        foreach ($dsDiaChi as $diaChi) {
            if ((int) $diaChi['id'] === $chonId) {
                $diachiDuocChon = $diaChi;
                break;
            }
        }

        // Chua chon thi lay dia chi mac dinh (vi tri dau danh sach da sort)
        if (!$diachiDuocChon) {
            $diachiDuocChon = $dsDiaChi[0];
        }

        return ['dsDiaChi' => $dsDiaChi, 'diachiDuocChon' => $diachiDuocChon];
    }
}
