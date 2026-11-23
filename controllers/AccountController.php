<?php

/**
 * Controller TAI KHOAN KHACH HANG
 * - Xem / sua thong tin ca nhan
 * - Doi mat khau
 * - Xem so dia chi
 * - Lich su don hang + chi tiet don (chi cua chinh minh)
 */
class AccountController
{
    /**
     * Trang tai khoan: GET ?action=account
     */
    public function index()
    {
        if (!$this->canAccess()) {
            return;
        }

        $userId = (int) $_SESSION['user']['id'];
        $user   = (new UserModel())->findById($userId);

        if (!$user) {
            set_flash('error', 'Không tìm thấy tài khoản.');
            redirect_to(BASE_URL);
            return;
        }

        $dsDiaChi = (new AddressModel())->getByUser($userId);

        $title = 'Tài khoản - AnLaMe';
        $view  = 'account';
        $error = '';
        $tab   = 'profile';

        require PATH_VIEW_MAIN;
    }

    /**
     * Cap nhat thong tin ca nhan: POST ?action=account-update
     */
    public function updateProfile()
    {
        if (!$this->canAccess()) {
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect_to(BASE_URL . '?action=account');
            return;
        }

        if (!is_valid_csrf_token($_POST['_csrf_token'] ?? null)) {
            set_flash('error', 'Phiên làm việc đã hết hạn. Vui lòng tải lại trang và thử lại.');
            redirect_to(BASE_URL . '?action=account');
            return;
        }

        $userId  = (int) $_SESSION['user']['id'];
        $hoTen   = trim((string) ($_POST['ho_ten'] ?? ''));
        $sdt     = trim((string) ($_POST['so_dien_thoai'] ?? ''));
        $diaChi  = trim((string) ($_POST['dia_chi'] ?? ''));
        $nameLen = preg_match_all('/./us', $hoTen);

        $error = '';

        if ($hoTen === '' || $nameLen === false || $nameLen > 150) {
            $error = 'Họ tên không được để trống và tối đa 150 ký tự.';
        } elseif ($sdt !== '' && !preg_match('/^(0|\+84)[0-9]{9,10}$/', $sdt)) {
            $error = 'Số điện thoại không đúng định dạng (ví dụ: 0901234567).';
        } elseif (mb_strlen($diaChi) > 500) {
            $error = 'Địa chỉ không được quá 500 ký tự.';
        }

        if ($error === '') {
            (new UserModel())->updateProfile($userId, $hoTen, $sdt ?: null, $diaChi ?: null);
            $_SESSION['user']['ho_ten'] = $hoTen;
            set_flash('success', 'Đã cập nhật thông tin tài khoản.');
            redirect_to(BASE_URL . '?action=account');
            return;
        }

        // Co loi: render lai trang voi thong bao
        $user    = (new UserModel())->findById($userId);
        $dsDiaChi = (new AddressModel())->getByUser($userId);
        $old = [
            'ho_ten'        => $hoTen,
            'so_dien_thoai' => $sdt,
            'dia_chi'       => $diaChi,
        ];

        $title = 'Tài khoản - AnLaMe';
        $view  = 'account';
        $tab   = 'profile';

        require PATH_VIEW_MAIN;
    }

    /**
     * Doi mat khau: POST ?action=account-password
     */
    public function changePassword()
    {
        if (!$this->canAccess()) {
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect_to(BASE_URL . '?action=account');
            return;
        }

        if (!is_valid_csrf_token($_POST['_csrf_token'] ?? null)) {
            set_flash('error', 'Phiên làm việc đã hết hạn. Vui lòng tải lại trang và thử lại.');
            redirect_to(BASE_URL . '?action=account');
            return;
        }

        $userId = (int) $_SESSION['user']['id'];
        $cu     = (string) ($_POST['mat_khau_cu'] ?? '');
        $moi    = (string) ($_POST['mat_khau_moi'] ?? '');
        $xacNhan = (string) ($_POST['xac_nhan_mat_khau_moi'] ?? '');

        $error = '';
        $user  = (new UserModel())->findById($userId);

        // UserModel::findById khong lay mat_khau -> tim bang email
        $userFull = (new UserModel())->findByEmail($user['email'] ?? '');

        if ($cu === '' || $moi === '') {
            $error = 'Vui lòng nhập đủ mật khẩu cũ và mật khẩu mới.';
        } elseif (!$userFull || !password_verify($cu, $userFull['mat_khau'])) {
            $error = 'Mật khẩu cũ không đúng.';
        } elseif (strlen($moi) < 8 || strlen($moi) > 72) {
            $error = 'Mật khẩu mới cần có từ 8 đến 72 ký tự.';
        } elseif ($moi !== $xacNhan) {
            $error = 'Mật khẩu xác nhận không khớp.';
        } else {
            (new UserModel())->updatePassword($userId, $moi);
            set_flash('success', 'Đã đổi mật khẩu thành công.');
            redirect_to(BASE_URL . '?action=account');
            return;
        }

        $dsDiaChi = (new AddressModel())->getByUser($userId);

        $title = 'Tài khoản - AnLaMe';
        $view  = 'account';
        $tab   = 'password';

        require PATH_VIEW_MAIN;
    }

    /**
     * Lich su don hang: GET ?action=orders
     */
    public function orders()
    {
        if (!$this->canAccess('orders')) {
            return;
        }

        $userId = (int) $_SESSION['user']['id'];
        $dsDon  = (new OrderModel())->getByUser($userId);

        $title = 'Đơn hàng của tôi - AnLaMe';
        $view  = 'orders';

        require PATH_VIEW_MAIN;
    }

    /**
     * Chi tiet 1 don hang: GET ?action=order-detail&id=...
     * Chi xem duoc don cua chinh minh.
     */
    public function orderDetail()
    {
        if (!$this->canAccess('orders')) {
            return;
        }

        $userId = (int) $_SESSION['user']['id'];
        $id     = (int) ($_GET['id'] ?? 0);
        $model  = new OrderModel();

        $don = $id > 0 ? $model->getById($id) : null;

        if (!$don || (int) ($don['nguoidung_id'] ?? 0) !== $userId) {
            set_flash('error', 'Không tìm thấy đơn hàng hoặc bạn không có quyền xem.');
            redirect_to(BASE_URL . '?action=orders');
            return;
        }

        $chiTiet = $model->getItems($id);
        $don['tong_thanh_toan'] = (float) $don['tong_tien'] + (float) $don['phi_giao_hang'];

        $title = 'Chi tiết đơn hàng - AnLaMe';
        $view  = 'order-detail';

        require PATH_VIEW_MAIN;
    }

    /**
     * Kiem tra da dang nhap chua (chua thi dua ve trang login).
     * @param string $redirect action can redirect sau khi dang nhap
     * @return bool
     */
    private function canAccess($redirect = 'account')
    {
        if (empty($_SESSION['user']['id'])) {
            set_flash('error', 'Vui lòng đăng nhập để xem trang này.');
            redirect_to(BASE_URL . '?action=login&redirect=' . urlencode($redirect));
            return false;
        }

        return true;
    }
}
