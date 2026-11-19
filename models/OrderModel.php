<?php

/**
 * Model DON HANG
 * - Tao don hang moi (ma_don rieng biet) + chi tiet don hang
 * - Su dung MySQL TRANSACTION: hoac thanh toan toan bo, hoac khong giu giay to nao
 * - Gia tien lay tu bang MONAN (khong nhan gia tu trinh duyet)
 * - Tru so luong ton kho cung ben trong transaction
 *
 * Quy uoc tien:
 *   donhang.tong_tien     = tong tien hang (chua phi giao hang)
 *   donhang.phi_giao_hang = phi giao hang
 *   Tong thanh toan        = tong_tien + phi_giao_hang
 */
class OrderModel extends BaseModel
{
    protected $table = 'donhang';

    /**
     * Tao don hang moi.
     *
     * @param array $khachHang ['ho_ten','so_dien_thoai','email','dia_chi','ghi_chu']
     * @param array $chiTiet   [['id' => id_mon, 'so_luong' => 2], ...]
     * @param int   $phiGiaoHang
     * @param string $phuongThuc 'tien_mat' | 'chuyen_khoan'
     * @return array thong tin don hang vua tao
     * @throws Exception neu du lieu khong hop le hoac khong du ton kho
     */
    public function createOrder(array $khachHang, array $chiTiet, $phiGiaoHang = 0, $phuongThuc = 'tien_mat')
    {
        if (empty($chiTiet)) {
            throw new Exception('Giỏ hàng đang trống, vui lòng chọn món trước khi đặt hàng.');
        }

        // Bat dau transaction
        $this->pdo->beginTransaction();

        try {
            $tongTienHang = 0;
            $monDaChon     = [];

            // ---- Buoc 1: doc gia tu CSDL va kiem tra ton kho (khoa dong FOR UPDATE) ----
            foreach ($chiTiet as $item) {
                $idMon = (int) ($item['id'] ?? 0);
                $soLuong = (int) ($item['so_luong'] ?? 0);

                if ($idMon <= 0) {
                    throw new Exception('Danh sách sản phẩm không hợp lệ.');
                }

                // So luong nho hon 1 thi khong cho phep
                if ($soLuong < 1) {
                    $soLuong = 1;
                }

                $sql = "SELECT id, ten_mon, gia, so_luong_ton, trang_thai
                        FROM monan
                        WHERE id = ?
                        FOR UPDATE";

                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([$idMon]);
                $mon = $stmt->fetch();

                if (!$mon || (int) $mon['trang_thai'] !== 1) {
                    throw new Exception('Món ăn không tồn tại hoặc đã ngừng bán.');
                }

                if ((int) $mon['so_luong_ton'] < $soLuong) {
                    throw new Exception(sprintf(
                        'Món "%s" chỉ còn %d phần, vui lòng giảm số lượng.',
                        $mon['ten_mon'],
                        (int) $mon['so_luong_ton']
                    ));
                }

                $donGia    = (float) $mon['gia'];
                $thanhTien = $donGia * $soLuong;

                $tongTienHang += $thanhTien;

                $monDaChon[$idMon] = [
                    'id'        => $idMon,
                    'ten_mon'   => $mon['ten_mon'],
                    'so_luong'  => $soLuong,
                    'don_gia'   => $donGia,
                    'thanh_tien'=> $thanhTien,
                ];
            }

            // ---- Buoc 2: tao ma don hang rieng biet ----
            $maDon = $this->taoMaDonHang();

            // ---- Buoc 3: luu thong tin don hang ----
            $sql = "INSERT INTO donhang
                        (ma_don, ho_ten, so_dien_thoai, email, dia_chi_giao,
                         nguoidung_id, diachi_id, ngay_dat,
                         tong_tien, phi_giao_hang, trang_thai, ghi_chu)
                    VALUES (?, ?, ?, ?, ?, NULL, NULL, NOW(), ?, ?, 'cho_xac_nhan', ?)";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                $maDon,
                $khachHang['ho_ten'],
                $khachHang['so_dien_thoai'],
                $khachHang['email'],
                $khachHang['dia_chi'],
                $tongTienHang,
                (float) $phiGiaoHang,
                $khachHang['ghi_chu'] ?? null,
            ]);

            $donhangId = (int) $this->pdo->lastInsertId();

            // ---- Buoc 4: luu chi tiet don hang + tru ton kho ----
            $sqlChiTiet = "INSERT INTO chitiet_donhang
                                (donhang_id, monan_id, so_luong, don_gia, thanh_tien)
                           VALUES (?, ?, ?, ?, ?)";

            $sqlTonKho = "UPDATE monan
                          SET so_luong_ton = so_luong_ton - ?
                          WHERE id = ? AND so_luong_ton >= ?";

            foreach ($monDaChon as $mon) {
                $stmt = $this->pdo->prepare($sqlChiTiet);
                $stmt->execute([
                    $donhangId,
                    $mon['id'],
                    $mon['so_luong'],
                    $mon['don_gia'],
                    $mon['thanh_tien'],
                ]);

                $stmt = $this->pdo->prepare($sqlTonKho);
                $stmt->execute([$mon['so_luong'], $mon['id'], $mon['so_luong']]);

                // Neu khong tru duoc ton kho thi rollback toan bo
                if ($stmt->rowCount() === 0) {
                    throw new Exception(sprintf('Món "%s" vừa hết hàng, vui lòng thử lại.', $mon['ten_mon']));
                }
            }

            // ---- Buoc 5: luu phuong thuc thanh toan ----
            if (!in_array($phuongThuc, ['tien_mat', 'chuyen_khoan'], true)) {
                $phuongThuc = 'tien_mat';
            }

            $sqlTT = "INSERT INTO thanhtoan (donhang_id, phuong_thuc, so_tien, trang_thai)
                      VALUES (?, ?, ?, 'chua_tt')";
            $stmt = $this->pdo->prepare($sqlTT);
            $stmt->execute([$donhangId, $phuongThuc, $tongTienHang + (float) $phiGiaoHang]);

            // ---- Buoc 6: hoan tat ----
            $this->pdo->commit();

            return [
                'id'             => $donhangId,
                'ma_don'         => $maDon,
                'ho_ten'         => $khachHang['ho_ten'],
                'so_dien_thoai'  => $khachHang['so_dien_thoai'],
                'email'          => $khachHang['email'],
                'dia_chi'        => $khachHang['dia_chi'],
                'ghi_chu'        => $khachHang['ghi_chu'] ?? null,
                'tong_tien'      => $tongTienHang,
                'phi_giao_hang'  => (float) $phiGiaoHang,
                'tong_thanh_toan'=> $tongTienHang + (float) $phiGiaoHang,
                'ngay_dat'       => date('Y-m-d H:i:s'),
                'trang_thai'     => 'cho_xac_nhan',
            ];
        } catch (Exception $e) {
            // Co loi thi huy toan bo transaction (khong luu gi trung gian)
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Lay don hang bang ma don.
     * @param string $maDon
     * @return array|null ['donhang' => ..., 'chi_tiet' => [...]]
     */
    public function findByMaDon($maDon)
    {
        $maDon = trim((string) $maDon);

        if ($maDon === '') {
            return null;
        }

        $sql = "SELECT d.*, tt.phuong_thuc, tt.trang_thai AS trang_thai_thanh_toan
                FROM donhang d
                LEFT JOIN thanhtoan tt ON tt.donhang_id = d.id
                WHERE d.ma_don = ?
                LIMIT 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$maDon]);
        $donhang = $stmt->fetch();

        if (!$donhang) {
            return null;
        }

        $sql = "SELECT c.*, m.ten_mon, m.hinh_anh
                FROM chitiet_donhang c
                JOIN monan m ON m.id = c.monan_id
                WHERE c.donhang_id = ?
                ORDER BY c.id ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([(int) $donhang['id']]);

        return [
            'donhang'  => $donhang,
            'chi_tiet' => $stmt->fetchAll(),
        ];
    }

    /**
     * Tao ma don hang duy nhat, vi du: DH261117A3F9C2
     * @return string
     * @throws Exception neu khong tao duoc ma sau 20 lan
     */
    private function taoMaDonHang()
    {
        for ($i = 0; $i < 20; $i++) {
            $maDon = 'DH' . date('ymd') . strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));

            $stmt = $this->pdo->prepare("SELECT id FROM donhang WHERE ma_don = ? LIMIT 1");
            $stmt->execute([$maDon]);

            if (!$stmt->fetch()) {
                return $maDon;
            }
        }

        throw new Exception('Không tạo được mã đơn hàng, vui lòng thử lại.');
    }
}
