<?php

class OrderModel extends BaseModel
{
    public function getAll($filters = [])
    {
        $sql = 'SELECT dh.*, 
                       nd.ho_ten, nd.email, nd.so_dien_thoai AS sdt_nguoidung,
                       dc.ten_nguoi_nhan, dc.so_dien_thoai AS sdt_nhan, dc.dia_chi_chi_tiet,
                       tt.phuong_thuc, tt.trang_thai AS trang_thai_tt
                FROM DONHANG dh
                LEFT JOIN NGUOIDUNG nd ON dh.nguoidung_id = nd.id
                LEFT JOIN DIACHI_NHANHANG dc ON dh.diachi_id = dc.id
                LEFT JOIN THANHTOAN tt ON dh.id = tt.donhang_id
                WHERE 1=1';
        $params = [];

        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $sql .= ' AND dh.trang_thai = ?';
            $params[] = $filters['status'];
        }

        if (!empty($filters['search'])) {
            $sql .= ' AND (dh.id = ? OR dc.ten_nguoi_nhan LIKE ? OR dc.so_dien_thoai LIKE ? OR nd.ho_ten LIKE ?)';
            $searchVal = trim($filters['search']);
            $params[] = is_numeric($searchVal) ? (int) $searchVal : 0;
            $params[] = '%' . $searchVal . '%';
            $params[] = '%' . $searchVal . '%';
            $params[] = '%' . $searchVal . '%';
        }

        $sql .= ' ORDER BY dh.ngay_dat DESC';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getById($id)
    {
        $sql = 'SELECT dh.*, 
                       nd.ho_ten, nd.email, nd.so_dien_thoai AS sdt_nguoidung,
                       dc.ten_nguoi_nhan, dc.so_dien_thoai AS sdt_nhan, dc.dia_chi_chi_tiet, dc.ghi_chu AS ghi_chu_diachi,
                       tt.phuong_thuc, tt.trang_thai AS trang_thai_tt, tt.ngay_thanh_toan
                FROM DONHANG dh
                LEFT JOIN NGUOIDUNG nd ON dh.nguoidung_id = nd.id
                LEFT JOIN DIACHI_NHANHANG dc ON dh.diachi_id = dc.id
                LEFT JOIN THANHTOAN tt ON dh.id = tt.donhang_id
                WHERE dh.id = ? LIMIT 1';
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

        // Nếu hoàn thành đơn hàng, tự động cập nhật thanh toán là da_tt nếu chưa thanh toán
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
        // Doanh thu từ các đơn hoàn thành
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
        $sql = 'SELECT dh.*, 
                       nd.ho_ten,
                       dc.ten_nguoi_nhan, dc.so_dien_thoai AS sdt_nhan,
                       tt.phuong_thuc, tt.trang_thai AS trang_thai_tt
                FROM DONHANG dh
                LEFT JOIN NGUOIDUNG nd ON dh.nguoidung_id = nd.id
                LEFT JOIN DIACHI_NHANHANG dc ON dh.diachi_id = dc.id
                LEFT JOIN THANHTOAN tt ON dh.id = tt.donhang_id
                ORDER BY dh.ngay_dat DESC
                LIMIT ' . (int) $limit;
        return $this->pdo->query($sql)->fetchAll();
    }
}
