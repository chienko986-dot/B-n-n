<?php

/**
 * Controller SO DIA CHI
 * - Them / sua / xoa dia chi nhan hang de chon nhanh khi dat hang ( nhu Shopee )
 * - Chi nguoi da dang nhap va chi cua chinh minh moi thao tac duoc
 * - Moi form POST deu kiem tra CSRF, tra ve trang dat hang bang flash
 */
class AddressController
{
    /**
     * Them / sua dia chi: POST ?action=address-save
     * - id rong  -> them moi
     * - id co gia tri -> sua dia chi tuong ung (phai thuoc ve minh)
     */
    public function save()
    {
        if (!$this->nguoiDungDaDangNhap()) {
            return;
        }

        if (!is_valid_csrf_token($_POST['_csrf_token'] ?? null)) {
            set_flash('error', 'Phiên làm việc đã hết hạn. Vui lòng tải lại trang và thử lại.');
            redirect_to(BASE_URL . '?action=checkout');
            return;
        }

        $id       = (int) ($_POST['id'] ?? 0);
        $ketQua   = $this->kiemTraVaLayDuLieu();

        if (!empty($ketQua['loi'])) {
            set_flash('error', $ketQua['loi']);
            redirect_to(BASE_URL . '?action=checkout');
            return;
        }

        $duLieu = $ketQua['du_lieu'];
        $userId = (int) $_SESSION['user']['id'];
        $model  = new AddressModel();

        try {
            if ($id > 0) {
                if (!$model->update($id, $userId, $duLieu)) {
                    set_flash('error', 'Địa chỉ không tồn tại hoặc bạn không có quyền sửa.');
                    redirect_to(BASE_URL . '?action=checkout');
                    return;
                }
                set_flash('success', 'Đã cập nhật địa chỉ.');
            } else {
                $id = $model->create($userId, $duLieu);
                set_flash('success', 'Đã lưu địa chỉ vào sổ địa chỉ.');
            }
        } catch (Exception $e) {
            set_flash('error', 'Không lưu được địa chỉ, vui lòng thử lại.');
            redirect_to(BASE_URL . '?action=checkout');
            return;
        }

        // Tu chon dia chi vua luu de dat hang ngay
        $_SESSION['chon_diachi_id'] = $id;

        redirect_to(BASE_URL . '?action=checkout');
    }

    /**
     * Xoa dia chi: POST ?action=address-delete
     * - back=account -> ve trang tai khoan, nguoc lai ve trang dat hang
     */
    public function delete()
    {
        if (!$this->nguoiDungDaDangNhap()) {
            return;
        }

        if (!is_valid_csrf_token($_POST['_csrf_token'] ?? null)) {
            set_flash('error', 'Phiên làm việc đã hết hạn. Vui lòng tải lại trang và thử lại.');
            redirect_to($this->trangSauThaoTac());
            return;
        }

        $id     = (int) ($_POST['id'] ?? 0);
        $userId = (int) $_SESSION['user']['id'];
        $model  = new AddressModel();

        if ($id <= 0 || !$model->delete($id, $userId)) {
            set_flash(
                'error',
                'Không xóa được địa chỉ. Địa chỉ có thể đã được dùng trong đơn hàng hoặc không thuộc về bạn.'
            );
            redirect_to($this->trangSauThaoTac());
            return;
        }

        // Dang chon dia bi xoa thi bo chon
        if ((int) ($_SESSION['chon_diachi_id'] ?? 0) === $id) {
            unset($_SESSION['chon_diachi_id']);
        }

        set_flash('success', 'Đã xóa địa chỉ.');
        redirect_to($this->trangSauThaoTac());
    }

    /**
     * Dat dia chi thanh mac dinh: POST ?action=address-set-default
     */
    public function setDefault()
    {
        if (!$this->nguoiDungDaDangNhap()) {
            return;
        }

        if (!is_valid_csrf_token($_POST['_csrf_token'] ?? null)) {
            set_flash('error', 'Phiên làm việc đã hết hạn. Vui lòng tải lại trang và thử lại.');
            redirect_to($this->trangSauThaoTac());
            return;
        }

        $id     = (int) ($_POST['id'] ?? 0);
        $userId = (int) $_SESSION['user']['id'];

        if ($id <= 0 || !(new AddressModel())->setDefault($id, $userId)) {
            set_flash('error', 'Không đặt được địa chỉ mặc định.');
        } else {
            $_SESSION['chon_diachi_id'] = $id;
            set_flash('success', 'Đã đặt địa chỉ mặc định.');
        }

        redirect_to($this->trangSauThaoTac());
    }

    /**
     * URL tro ve sau khi them / sua / xoa dia chi.
     * - back=account -> trang tai khoan
     * - nguoc lai    -> trang dat hang
     * @return string
     */
    private function trangSauThaoTac()
    {
        $back = (string) ($_POST['back'] ?? $_GET['back'] ?? '');
        return $back === 'account'
            ? BASE_URL . '?action=account'
            : BASE_URL . '?action=checkout';
    }

    /**
     * Kiem tra da dang nhap chua (chua dang nhap thi dua ve trang login).
     * @return bool
     */
    private function nguoiDungDaDangNhap()
    {
        if (empty($_SESSION['user']['id'])) {
            set_flash('error', 'Vui lòng đăng nhập để lưu địa chỉ nhận hàng.');
            redirect_to(BASE_URL . '?action=login&redirect=checkout');
            return false;
        }

        return true;
    }

    /**
     * Kiem tra du lieu dia chi gui len va lay ra mang dung cho AddressModel.
     *
     * Co 2 che do nhap:
     *  - 'chon'  : chon Tinh / Huyen / Xa + so nha (lay ma dia chinh)
     *  - 'text'  : go tay toan bo dia chi (dia chi cu / dia chi dac biet)
     *
     * @return array ['loi' => '', 'du_lieu' => [...]]
     */
    private function kiemTraVaLayDuLieu()
    {
        $ten  = trim((string) ($_POST['ten_nguoi_nhan'] ?? ''));
        $sdt  = trim((string) ($_POST['so_dien_thoai'] ?? ''));
        $ghiChu = trim((string) ($_POST['ghi_chu'] ?? ''));
        $macDinh = isset($_POST['mac_dinh']) ? 1 : 0;
        $cheDo = (string) ($_POST['dia_chi_che_do'] ?? 'chon');

        // Ten nhan hang
        if ($ten === '') {
            return ['loi' => 'Vui lòng nhập tên người nhận.', 'du_lieu' => []];
        }
        if (mb_strlen($ten) > 150) {
            return ['loi' => 'Tên người nhận không được quá 150 ký tự.', 'du_lieu' => []];
        }

        // So dien thoai
        if ($sdt === '' || !preg_match('/^(0|\+84)[0-9]{9,10}$/', $sdt)) {
            return ['loi' => 'Số điện thoại không đúng định dạng (ví dụ: 0901234567).', 'du_lieu' => []];
        }

        // Ghi chu
        if (mb_strlen($ghiChu) > 500) {
            return ['loi' => 'Ghi chú không được quá 500 ký tự.', 'du_lieu' => []];
        }

        if ($cheDo === 'text') {
            // Go tay toan bo dia chi
            $text = trim((string) ($_POST['dia_chi_text'] ?? ''));

            if (mb_strlen($text) < 10) {
                return ['loi' => 'Địa chỉ giao hàng cần ít nhất 10 ký tự.', 'du_lieu' => []];
            }
            if (mb_strlen($text) > 500) {
                return ['loi' => 'Địa chỉ giao hàng không được quá 500 ký tự.', 'du_lieu' => []];
            }

            return [
                'loi' => '',
                'du_lieu' => [
                    'ten_nguoi_nhan'  => $ten,
                    'so_dien_thoai'   => $sdt,
                    'dia_chi_chi_tiet'=> $text,
                    'ghi_chu'         => $ghiChu !== '' ? $ghiChu : null,
                    'tinh_id'         => '',
                    'huyen_id'        => '',
                    'xa_id'           => '',
                    'so_nha'          => '',
                    'mac_dinh'        => $macDinh,
                ],
            ];
        }

        // Che do 'chon': Tinh / Huyen / Xa + so nha
        $tinhId  = trim((string) ($_POST['tinh_id'] ?? ''));
        $huyenId = trim((string) ($_POST['huyen_id'] ?? ''));
        $xaId    = trim((string) ($_POST['xa_id'] ?? ''));
        $soNha   = trim((string) ($_POST['so_nha'] ?? ''));

        if ($tinhId === '' || $huyenId === '' || $xaId === '') {
            return ['loi' => 'Vui lòng chọn đủ Tỉnh / Thành, Quận / Huyện, Phường / Xã.', 'du_lieu' => []];
        }

        $tenDiaChinh = dia_chinh_ten($tinhId, $huyenId, $xaId);
        if ($tenDiaChinh['tinh'] === '' || $tenDiaChinh['huyen'] === '' || $tenDiaChinh['xa'] === '') {
            return ['loi' => 'Địa chỉ vừa chọn không hợp lệ, vui lòng chọn lại.', 'du_lieu' => []];
        }

        if ($soNha === '') {
            return ['loi' => 'Vui lòng nhập số nhà, đường.', 'du_lieu' => []];
        }
        if (mb_strlen($soNha) > 255) {
            return ['loi' => 'Số nhà, đường không được quá 255 ký tự.', 'du_lieu' => []];
        }

        $diaChiDayDu = dia_chinh_ghep($soNha, $tenDiaChinh['xa'], $tenDiaChinh['huyen'], $tenDiaChinh['tinh']);

        return [
            'loi' => '',
            'du_lieu' => [
                'ten_nguoi_nhan'  => $ten,
                'so_dien_thoai'   => $sdt,
                'dia_chi_chi_tiet'=> $diaChiDayDu,
                'ghi_chu'         => $ghiChu !== '' ? $ghiChu : null,
                'tinh_id'         => $tinhId,
                'huyen_id'        => $huyenId,
                'xa_id'           => $xaId,
                'so_nha'          => $soNha,
                'mac_dinh'        => $macDinh,
            ],
        ];
    }
}
