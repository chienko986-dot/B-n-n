<?php

/**
 * Model DON HANG
 * - Quản lý đơn hàng cho cả Khách hàng (Tạo đơn, Tra cứu mã đơn) và Quản trị viên (Admin)
 * - Sử dụng MySQL TRANSACTION khi đặt hàng để đảm bảo toàn vẹn dữ liệu và trừ tồn kho
 */
class OrderModel extends BaseModel
{
    protected $table = 'donhang';

    private function hasColumn($table, $column)
    {
        static $colsCache = [];
        if (!isset($colsCache[$table])) {
            $stmt = $this->pdo->query("DESCRIBE $table");
            $colsCache[$table] = $stmt->fetchAll(PDO::FETCH_COLUMN);
        }
        return in_array($column, $colsCache[$table], true);
    }

    // ========================================================
    // 1. DÀNH CHO KHÁCH HÀNG / CHECKOUT (TỪ MAIN)
    // ========================================================

    /**
     * Tao don hang moi.
     *
     * @param array $khachHang ['ho_ten','so_dien_thoai','email','dia_chi','ghi_chu',
     *                         'tinh_id','huyen_id','xa_id','so_nha',
     *                         'nguoidung_id','diachi_id']
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
            $tonTheoMon    = [];   // tong so luong can cho tung mon (1 mon co the nhieu dong)
            $tenTheoMon    = [];

            // ---- Buoc 1: doc gia tu CSDL va kiem tra ton kho (khoa dong FOR UPDATE) ----
            foreach ($chiTiet as $item) {
                $idMon = (int) ($item['id'] ?? 0);
                $soLuong = (int) ($item['so_luong'] ?? 0);

                if ($idMon <= 0) {
                    throw new Exception('Danh sách sản phẩm không hợp lệ.');
                }

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

                // Don gia: cart_details() da tinh xong size / topping (lay tu CSDL) -> dung truc tiep;
                // khong co thi lay gia mon.
                $donGia = isset($item['don_gia']) ? (float) $item['don_gia'] : (float) $mon['gia'];

                if ($donGia < 0) {
                    $donGia = (float) $mon['gia'];
                }

                $thanhTien = $donGia * $soLuong;

                $tongTienHang += $thanhTien;

                $tonTheoMon[$idMon] = ($tonTheoMon[$idMon] ?? 0) + $soLuong;
                $tenTheoMon[$idMon] = $mon['ten_mon'];
                $tonKhoMon[$idMon]  = (int) $mon['so_luong_ton'];

                // Dong gio hang nay co lua chon size / topping / ghi chu (tu trang chi tiet mon)
                $monDaChon[] = [
                    'id'            => $idMon,
                    'ten_mon'       => $mon['ten_mon'],
                    'so_luong'      => $soLuong,
                    'don_gia'       => $donGia,
                    'thanh_tien'    => $thanhTien,
                    'size_ten'      => (string) ($item['size_ten'] ?? ''),
                    'topping_list'  => (string) ($item['topping_list'] ?? ''),
                    'ghi_chu'       => (string) ($item['ghi_chu'] ?? ''),
                ];
            }

            // Kiem tra ton kho theo TONG so luong cua tung mon (1 mon co the xuat hien nhieu dong)
            foreach ($tonTheoMon as $idMon => $tongCan) {
                if (($tonKhoMon[$idMon] ?? 0) < $tongCan) {
                    throw new Exception(sprintf(
                        'Món "%s" chỉ còn %d phần, vui lòng giảm số lượng.',
                        $tenTheoMon[$idMon],
                        $tonKhoMon[$idMon]
                    ));
                }
            }

            // ---- Buoc 2: tao ma don hang rieng biet ----
            $hasMaDon = $this->hasColumn('donhang', 'ma_don');
            $maDon = $hasMaDon ? $this->taoMaDonHang() : ('DH' . time());

            // ---- Buoc 3: luu thong tin don hang ----
            if ($hasMaDon) {
                // Cot dia chinh (tinh/huyen/xa/so_nha) chi co sau khi chay database_dia_chinh.sql
                $cotDiaChinh = $this->hasColumn('donhang', 'tinh_id');
                $themDiaChinh = $cotDiaChinh
                    ? ', tinh_id, huyen_id, xa_id, so_nha'
                    : '';
                $giaTriDiaChinh = $cotDiaChinh
                    ? [
                        $khachHang['tinh_id']  ?? null,
                        $khachHang['huyen_id'] ?? null,
                        $khachHang['xa_id']    ?? null,
                        $khachHang['so_nha']   ?? null,
                    ]
                    : [];

                $sql = "INSERT INTO donhang
                            (ma_don, ho_ten, so_dien_thoai, email, dia_chi_giao{$themDiaChinh},
                             nguoidung_id, diachi_id, ngay_dat,
                             tong_tien, phi_giao_hang, trang_thai, ghi_chu)
                        VALUES (?, ?, ?, ?, ?" . str_repeat(', ?', count($giaTriDiaChinh)) . ",
                                ?, ?, NOW(), ?, ?, 'cho_xac_nhan', ?)";

                $stmt = $this->pdo->prepare($sql);
                $stmt->execute(array_merge([
                    $maDon,
                    $khachHang['ho_ten'],
                    $khachHang['so_dien_thoai'],
                    $khachHang['email'],
                    $khachHang['dia_chi'],
                ], $giaTriDiaChinh, [
                    $khachHang['nguoidung_id'] ?? null,
                    $khachHang['diachi_id'] ?? null,
                    $tongTienHang,
                    (float) $phiGiaoHang,
                    $khachHang['ghi_chu'] ?? null,
                ]));
            } else {
                $sql = "INSERT INTO donhang
                            (nguoidung_id, diachi_id, ngay_dat,
                             tong_tien, phi_giao_hang, trang_thai, ghi_chu)
                        VALUES (?, ?, NOW(), ?, ?, 'cho_xac_nhan', ?)";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([
                    $khachHang['nguoidung_id'] ?? null,
                    $khachHang['diachi_id'] ?? null,
                    $tongTienHang,
                    (float) $phiGiaoHang,
                    $khachHang['ghi_chu'] ?? null,
                ]);
            }

            $donhangId = (int) $this->pdo->lastInsertId();

            // ---- Buoc 4: luu chi tiet don hang + tru ton kho ----
            $sqlChiTiet = "INSERT INTO chitiet_donhang
                                (donhang_id, monan_id, so_luong, don_gia, thanh_tien,
                                 size_ten, topping_list, ghi_chu)
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

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
                    $mon['size_ten'] !== '' ? mb_substr($mon['size_ten'], 0, 50, 'UTF-8') : null,
                    $mon['topping_list'] !== '' ? mb_substr($mon['topping_list'], 0, 500, 'UTF-8') : null,
                    $mon['ghi_chu'] !== '' ? mb_substr($mon['ghi_chu'], 0, 255, 'UTF-8') : null,
                ]);

                $stmt = $this->pdo->prepare($sqlTonKho);
                $stmt->execute([$mon['so_luong'], $mon['id'], $mon['so_luong']]);

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

        if ($this->hasColumn('donhang', 'ma_don')) {
            $stmt = $this->pdo->prepare("SELECT * FROM donhang WHERE ma_don = ? LIMIT 1");
            $stmt->execute([$maDon]);
        } else {
            $stmt = $this->pdo->prepare("SELECT * FROM donhang WHERE id = ? LIMIT 1");
            $stmt->execute([str_replace('DH', '', $maDon)]);
        }

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

    // ========================================================
    // 2. DÀNH CHO QUẢN TRỊ VIÊN (ADMIN)
    // ========================================================

    /**
     * Lay danh sach don hang cua 1 khach hang (lich su dat hang).
     * @param int $userId
     * @return array
     */
    public function getByUser($userId)
    {
        $hasMaDon = $this->hasColumn('donhang', 'ma_don');
        $selectMaDon = $hasMaDon ? 'dh.ma_don,' : "CONCAT('DH', dh.id) AS ma_don,";

        $sql = "SELECT dh.id, $selectMaDon dh.ngay_dat, dh.tong_tien, dh.phi_giao_hang,
                       dh.trang_thai, dh.ghi_chu, dh.ho_ten, dh.so_dien_thoai, dh.dia_chi_giao,
                       tt.phuong_thuc, tt.trang_thai AS trang_thai_tt,
                       (SELECT COUNT(*) FROM chitiet_donhang c WHERE c.donhang_id = dh.id) AS so_mon,
                       (SELECT COALESCE(SUM(c.so_luong), 0) FROM chitiet_donhang c WHERE c.donhang_id = dh.id) AS so_luong_tong
                FROM DONHANG dh
                LEFT JOIN THANHTOAN tt ON dh.id = tt.donhang_id
                WHERE dh.nguoidung_id = ?
                ORDER BY dh.ngay_dat DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([(int) $userId]);
        return $stmt->fetchAll();
    }

    public function getAll($filters = [])
    {
        $hasGuestCols = $this->hasColumn('donhang', 'ho_ten');

        $tenKhachExpr = $hasGuestCols 
            ? 'COALESCE(dh.ho_ten, dc.ten_nguoi_nhan, nd.ho_ten, "Khách hàng")' 
            : 'COALESCE(dc.ten_nguoi_nhan, nd.ho_ten, "Khách hàng")';
        $sdtKhachExpr = $hasGuestCols 
            ? 'COALESCE(dh.so_dien_thoai, dc.so_dien_thoai, nd.so_dien_thoai, "")' 
            : 'COALESCE(dc.so_dien_thoai, nd.so_dien_thoai, "")';
        $diaChiExpr = $hasGuestCols 
            ? 'COALESCE(dh.dia_chi_giao, dc.dia_chi_chi_tiet, "")' 
            : 'COALESCE(dc.dia_chi_chi_tiet, "")';

        $sql = "SELECT dh.*, 
                       $tenKhachExpr AS ten_khach_hang,
                       $sdtKhachExpr AS sdt_khach_hang,
                       $diaChiExpr AS dia_chi_giao_hang,
                       nd.ho_ten, nd.email, nd.so_dien_thoai AS sdt_nguoidung,
                       dc.ten_nguoi_nhan, dc.so_dien_thoai AS sdt_nhan, dc.dia_chi_chi_tiet,
                       tt.phuong_thuc, tt.trang_thai AS trang_thai_tt
                FROM DONHANG dh
                LEFT JOIN NGUOIDUNG nd ON dh.nguoidung_id = nd.id
                LEFT JOIN DIACHI_NHANHANG dc ON dh.diachi_id = dc.id
                LEFT JOIN THANHTOAN tt ON dh.id = tt.donhang_id
                WHERE 1=1";
        $params = [];

        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $sql .= ' AND dh.trang_thai = ?';
            $params[] = $filters['status'];
        }

        if (!empty($filters['search'])) {
            $searchVal = trim($filters['search']);
            $idVal = is_numeric($searchVal) ? (int) $searchVal : 0;
            $likeVal = '%' . $searchVal . '%';

            if ($this->hasColumn('donhang', 'ma_don')) {
                $sql .= ' AND (dh.id = ? OR dh.ma_don LIKE ? OR dc.ten_nguoi_nhan LIKE ? OR dc.so_dien_thoai LIKE ? OR nd.ho_ten LIKE ?';
                $params[] = $idVal;
                $params[] = $likeVal;
                $params[] = $likeVal;
                $params[] = $likeVal;
                $params[] = $likeVal;
            } else {
                $sql .= ' AND (dh.id = ? OR dc.ten_nguoi_nhan LIKE ? OR dc.so_dien_thoai LIKE ? OR nd.ho_ten LIKE ?';
                $params[] = $idVal;
                $params[] = $likeVal;
                $params[] = $likeVal;
                $params[] = $likeVal;
            }

            if ($hasGuestCols) {
                $sql .= ' OR dh.ho_ten LIKE ? OR dh.so_dien_thoai LIKE ?';
                $params[] = $likeVal;
                $params[] = $likeVal;
            }
            $sql .= ')';
        }

        $sql .= ' ORDER BY dh.ngay_dat DESC';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getById($id)
    {
        $hasGuestCols = $this->hasColumn('donhang', 'ho_ten');

        $tenKhachExpr = $hasGuestCols 
            ? 'COALESCE(dh.ho_ten, dc.ten_nguoi_nhan, nd.ho_ten, "Khách hàng")' 
            : 'COALESCE(dc.ten_nguoi_nhan, nd.ho_ten, "Khách hàng")';
        $sdtKhachExpr = $hasGuestCols 
            ? 'COALESCE(dh.so_dien_thoai, dc.so_dien_thoai, nd.so_dien_thoai, "")' 
            : 'COALESCE(dc.so_dien_thoai, nd.so_dien_thoai, "")';
        $emailExpr = $hasGuestCols 
            ? 'COALESCE(dh.email, nd.email, "")' 
            : 'COALESCE(nd.email, "")';
        $diaChiExpr = $hasGuestCols 
            ? 'COALESCE(dh.dia_chi_giao, dc.dia_chi_chi_tiet, "")' 
            : 'COALESCE(dc.dia_chi_chi_tiet, "")';

        $sql = "SELECT dh.*, 
                       $tenKhachExpr AS ten_khach_hang,
                       $sdtKhachExpr AS sdt_khach_hang,
                       $emailExpr AS email_khach_hang,
                       $diaChiExpr AS dia_chi_giao_hang,
                       nd.ho_ten, nd.email, nd.so_dien_thoai AS sdt_nguoidung,
                       dc.ten_nguoi_nhan, dc.so_dien_thoai AS sdt_nhan, dc.dia_chi_chi_tiet, dc.ghi_chu AS ghi_chu_diachi,
                       tt.phuong_thuc, tt.trang_thai AS trang_thai_tt, tt.ngay_thanh_toan
                FROM DONHANG dh
                LEFT JOIN NGUOIDUNG nd ON dh.nguoidung_id = nd.id
                LEFT JOIN DIACHI_NHANHANG dc ON dh.diachi_id = dc.id
                LEFT JOIN THANHTOAN tt ON dh.id = tt.donhang_id
                WHERE dh.id = ? LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getItems($orderId)
    {
        $sql = 'SELECT cd.*, m.ten_mon, m.hinh_anh, d.ten_danh_muc 
                FROM CHITIET_DONHANG cd
                LEFT JOIN MONAN m ON cd.monan_id = m.id
                LEFT JOIN DANHMUC d ON m.danhmuc_id = d.id
                WHERE cd.donhang_id = ?
                ORDER BY cd.id ASC';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$orderId]);
        return $stmt->fetchAll();
    }

    public function updateStatus($id, $status)
    {
        $allowed = ['cho_xac_nhan', 'dang_giao', 'hoan_thanh', 'huy'];
        if (!in_array($status, $allowed, true)) {
            return false;
        }

        $stmt = $this->pdo->prepare('UPDATE DONHANG SET trang_thai = ? WHERE id = ?');
        $success = $stmt->execute([$status, $id]);

        if ($success && $status === 'hoan_thanh') {
            $ttStmt = $this->pdo->prepare(
                "UPDATE THANHTOAN SET trang_thai = 'da_tt', ngay_thanh_toan = NOW() WHERE donhang_id = ? AND trang_thai = 'chua_tt'"
            );
            $ttStmt->execute([$id]);
        }

        return $success;
    }

    public function countByStatus()
    {
        $counts = [
            'all'          => 0,
            'cho_xac_nhan' => 0,
            'dang_giao'    => 0,
            'hoan_thanh'   => 0,
            'huy'          => 0,
        ];

        $sql = 'SELECT trang_thai, COUNT(*) AS cnt FROM DONHANG GROUP BY trang_thai';
        $rows = $this->pdo->query($sql)->fetchAll();
        $total = 0;
        foreach ($rows as $row) {
            $st = $row['trang_thai'];
            $c = (int) $row['cnt'];
            if (isset($counts[$st])) {
                $counts[$st] = $c;
            }
            $total += $c;
        }
        $counts['all'] = $total;
        return $counts;
    }

    public function getStats()
    {
        $revSql = "SELECT COALESCE(SUM(tong_tien + phi_giao_hang), 0) FROM DONHANG WHERE trang_thai = 'hoan_thanh'";
        $revenue = (float) $this->pdo->query($revSql)->fetchColumn();

        $totalOrders = (int) $this->pdo->query('SELECT COUNT(*) FROM DONHANG')->fetchColumn();
        $pendingOrders = (int) $this->pdo->query("SELECT COUNT(*) FROM DONHANG WHERE trang_thai = 'cho_xac_nhan'")->fetchColumn();
        $shippingOrders = (int) $this->pdo->query("SELECT COUNT(*) FROM DONHANG WHERE trang_thai = 'dang_giao'")->fetchColumn();

        return [
            'revenue'        => $revenue,
            'total_orders'   => $totalOrders,
            'pending_orders' => $pendingOrders,
            'shipping_orders'=> $shippingOrders,
        ];
    }

    public function getRecentOrders($limit = 6)
    {
        $hasGuestCols = $this->hasColumn('donhang', 'ho_ten');

        $tenKhachExpr = $hasGuestCols 
            ? 'COALESCE(dh.ho_ten, dc.ten_nguoi_nhan, nd.ho_ten, "Khách hàng")' 
            : 'COALESCE(dc.ten_nguoi_nhan, nd.ho_ten, "Khách hàng")';
        $sdtKhachExpr = $hasGuestCols 
            ? 'COALESCE(dh.so_dien_thoai, dc.so_dien_thoai, nd.so_dien_thoai, "")' 
            : 'COALESCE(dc.so_dien_thoai, nd.so_dien_thoai, "")';

        $sql = "SELECT dh.*, 
                       $tenKhachExpr AS ten_khach_hang,
                       $sdtKhachExpr AS sdt_khach_hang,
                       nd.ho_ten,
                       dc.ten_nguoi_nhan, dc.so_dien_thoai AS sdt_nhan,
                       tt.phuong_thuc, tt.trang_thai AS trang_thai_tt
                FROM DONHANG dh
                LEFT JOIN NGUOIDUNG nd ON dh.nguoidung_id = nd.id
                LEFT JOIN DIACHI_NHANHANG dc ON dh.diachi_id = dc.id
                LEFT JOIN THANHTOAN tt ON dh.id = tt.donhang_id
                ORDER BY dh.ngay_dat DESC
                LIMIT " . (int) $limit;
        return $this->pdo->query($sql)->fetchAll();
    }
}
