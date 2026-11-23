<?php

/**
 * Controller CHI TIET MON AN
 * - Hien thi trang chi tiet 1 mon: ?action=dish&id=1
 *   + thong tin mon, gia khuyen mai, size / topping / ghi chu
 *   + danh gia (chi khach da mua moi duoc gui) va mon lien quan
 * - Nhan gui danh gia: POST ?action=review-post
 */
class DishController
{
    public function detail()
    {
        $id = (int) ($_GET['id'] ?? 0);

        $dishModel = new DishModel();
        $dish      = $id > 0 ? $dishModel->getById($id) : null;

        // Mon khong ton tai hoac da ngung ban -> ve trang chu
        if (!$dish || (int) $dish['trang_thai'] !== 1) {
            set_flash('error', 'Món ăn không tồn tại hoặc đã ngừng bán.');
            redirect_to(BASE_URL);
            return;
        }

        // Lua chon mua hang: size / topping / anh phu
        $sizes    = $dishModel->getSizes($id);
        $toppings = $dishModel->getToppings($id);
        $images   = $dishModel->getImages($id);

        $reviewModel = new ReviewModel();
        $reviews     = $reviewModel->getByDish((int) $dish['id']);

        $avgRating = 0;
        if (count($reviews) > 0) {
            $avgRating = round(array_sum(array_column($reviews, 'so_sao')) / count($reviews), 1);
        }

        $related = $dishModel->getRelated((int) $dish['id'], (int) $dish['danhmuc_id'], 4);
        $soldCount = $dishModel->getSoldCount((int) $dish['id']);

        // Trang thai form danh gia cua khach dang nhap
        $nguoiDungId   = (int) ($_SESSION['user']['id'] ?? 0);
        $daMuaMon      = false;
        $danhGiaCuaToi = null;

        if ($nguoiDungId > 0) {
            $daMuaMon = $reviewModel->daMuaMon($nguoiDungId, (int) $dish['id']);

            if ($daMuaMon) {
                $danhGiaCuaToi = $reviewModel->getByUserAndDish($nguoiDungId, (int) $dish['id']);
            }
        }

        $title = $dish['ten_mon'] . ' - AnLaMe';
        $view  = 'dish';

        require_once PATH_VIEW_MAIN;
    }

    /**
     * Nhan gui danh gia: POST ?action=review-post
     * - Phai dang nhap
     * - Phai da mua mon nay (don khong bi huy)
     * - 1 khach chi co 1 danh gia cho 1 mon (da co thi cap nhat)
     */
    public function postReview()
    {
        $idMon       = (int) ($_POST['monan_id'] ?? 0);
        $nguoiDungId = (int) ($_SESSION['user']['id'] ?? 0);
        $veMon       = BASE_URL . '?action=dish&id=' . $idMon;

        // Chua dang nhap -> bao va tro ve mon
        if ($nguoiDungId <= 0) {
            set_flash('error', 'Vui lòng đăng nhập để đánh giá món này.');
            redirect_to($veMon);
            return;
        }

        // CSRF
        if (!is_valid_csrf_token((string) ($_POST['_csrf_token'] ?? ''))) {
            set_flash('error', 'Phiên làm việc đã hết hạn, vui lòng thử lại.');
            redirect_to($veMon);
            return;
        }

        // Mon co ton tai khong
        $dishModel = new DishModel();
        $mon = $idMon > 0 ? $dishModel->getById($idMon) : null;

        if (!$mon || (int) $mon['trang_thai'] !== 1) {
            set_flash('error', 'Món ăn không tồn tại hoặc đã ngừng bán.');
            redirect_to(BASE_URL);
            return;
        }

        $reviewModel = new ReviewModel();

        // Chi khach da mua (don khong bi huy) moi duoc phep danh gia
        if (!$reviewModel->daMuaMon($nguoiDungId, $idMon)) {
            set_flash('error', 'Chỉ khách đã mua món này mới có thể đánh giá.');
            redirect_to($veMon);
            return;
        }

        $soSao    = (int) ($_POST['so_sao'] ?? 0);
        $binhLuan = (string) ($_POST['binh_luan'] ?? '');

        if ($soSao < 1 || $soSao > 5) {
            set_flash('error', 'Bạn hãy chọn số sao từ 1 đến 5.');
            redirect_to($veMon);
            return;
        }

        if (mb_strlen($binhLuan) > 1000) {
            $binhLuan = mb_substr($binhLuan, 0, 1000, 'UTF-8');
        }

        if ($reviewModel->luuDanhGia($nguoiDungId, $idMon, $soSao, $binhLuan)) {
            set_flash('success', 'Cảm ơn bạn đã đánh giá món này!');
        } else {
            set_flash('error', 'Không lưu được đánh giá, vui lòng thử lại.');
        }

        redirect_to($veMon);
    }
}
