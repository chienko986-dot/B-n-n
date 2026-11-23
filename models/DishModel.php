<?php

class DishModel extends BaseModel
{
    public function getAll($filters = [])
    {
        $sql = 'SELECT m.*, d.ten_danh_muc 
                FROM MONAN m 
                LEFT JOIN DANHMUC d ON m.danhmuc_id = d.id 
                WHERE 1=1';
        $params = [];

        if (!empty($filters['search'])) {
            $sql .= ' AND (m.ten_mon LIKE ? OR m.mo_ta LIKE ?)';
            $searchParam = '%' . trim($filters['search']) . '%';
            $params[] = $searchParam;
            $params[] = $searchParam;
        }

        if (!empty($filters['danhmuc_id'])) {
            $sql .= ' AND m.danhmuc_id = ?';
            $params[] = (int) $filters['danhmuc_id'];
        }

        if (isset($filters['trang_thai']) && $filters['trang_thai'] !== '') {
            $sql .= ' AND m.trang_thai = ?';
            $params[] = (int) $filters['trang_thai'];
        }

        $sql .= ' ORDER BY m.id DESC';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getById($id)
    {
        $stmt = $this->pdo->prepare(
            'SELECT m.*, d.ten_danh_muc 
             FROM MONAN m 
             LEFT JOIN DANHMUC d ON m.danhmuc_id = d.id 
             WHERE m.id = ? LIMIT 1'
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create($data)
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO MONAN (danhmuc_id, ten_mon, gia, gia_goc, mo_ta, thanh_phan, khau_phan, thoi_gian_chuan_bi, hinh_anh, so_luong_ton, trang_thai) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $data['danhmuc_id'],
            $data['ten_mon'],
            $data['gia'],
            $data['gia_goc'] ?? null,
            $data['mo_ta'] ?? '',
            $data['thanh_phan'] ?? '',
            $data['khau_phan'] ?? '',
            $data['thoi_gian_chuan_bi'] ?? '',
            $data['hinh_anh'] ?? null,
            $data['so_luong_ton'] ?? 0,
            $data['trang_thai'] ?? 1,
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function update($id, $data)
    {
        $fields = [
            'danhmuc_id = ?',
            'ten_mon = ?',
            'gia = ?',
            'gia_goc = ?',
            'mo_ta = ?',
            'thanh_phan = ?',
            'khau_phan = ?',
            'thoi_gian_chuan_bi = ?',
            'so_luong_ton = ?',
            'trang_thai = ?',
        ];
        $params = [
            $data['danhmuc_id'],
            $data['ten_mon'],
            $data['gia'],
            $data['gia_goc'] ?? null,
            $data['mo_ta'] ?? '',
            $data['thanh_phan'] ?? '',
            $data['khau_phan'] ?? '',
            $data['thoi_gian_chuan_bi'] ?? '',
            $data['so_luong_ton'] ?? 0,
            $data['trang_thai'] ?? 1,
        ];

        if (isset($data['hinh_anh'])) {
            $fields[] = 'hinh_anh = ?';
            $params[] = $data['hinh_anh'];
        }

        $params[] = $id;

        $sql = 'UPDATE MONAN SET ' . implode(', ', $fields) . ' WHERE id = ?';
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    public function delete($id)
    {
        // Kiểm tra xem món ăn đã có trong đơn hàng chưa
        $check = $this->pdo->prepare('SELECT COUNT(*) FROM CHITIET_DONHANG WHERE monan_id = ?');
        $check->execute([$id]);
        if ($check->fetchColumn() > 0) {
            // Thay vì xóa cứng gây lỗi khóa ngoại, ẩn món ăn đi
            $hide = $this->pdo->prepare('UPDATE MONAN SET trang_thai = 0 WHERE id = ?');
            $hide->execute([$id]);
            return 'hidden';
        }

        $stmt = $this->pdo->prepare('DELETE FROM MONAN WHERE id = ?');
        $stmt->execute([$id]);
        return 'deleted';
    }

    public function count()
    {
        return (int) $this->pdo->query('SELECT COUNT(*) FROM MONAN')->fetchColumn();
    }

    public function getTopSelling($limit = 5)
    {
        $sql = 'SELECT m.id, m.ten_mon, m.gia, m.hinh_anh, d.ten_danh_muc,
                       COALESCE(SUM(cd.so_luong), 0) AS tong_da_ban
                FROM MONAN m
                LEFT JOIN DANHMUC d ON m.danhmuc_id = d.id
                LEFT JOIN CHITIET_DONHANG cd ON m.id = cd.monan_id
                GROUP BY m.id
                ORDER BY tong_da_ban DESC, m.id DESC
                LIMIT ' . (int) $limit;
        return $this->pdo->query($sql)->fetchAll();
    }

    // Tong so phan da ban cua 1 mon (tu CHITIET_DONHANG)
    public function getSoldCount($id)
    {
        $stmt = $this->pdo->prepare(
            'SELECT COALESCE(SUM(cd.so_luong), 0)
             FROM CHITIET_DONHANG cd
             WHERE cd.monan_id = ?'
        );
        $stmt->execute([(int) $id]);
        return (int) $stmt->fetchColumn();
    }

    // Danh sach size dang ban cua 1 mon (sap xep thu tu)
    public function getSizes($id)
    {
        $map = $this->sizesFor([(int) $id]);
        return $map[(int) $id] ?? [];
    }

    // Danh sach topping dang ban cua 1 mon (sap xep thu tu)
    public function getToppings($id)
    {
        $map = $this->toppingsFor([(int) $id]);
        return $map[(int) $id] ?? [];
    }

    // Anh phu cua 1 mon (mon hien tai chua co anh phu -> mang rong)
    public function getImages($id)
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM monan_anh WHERE monan_id = ? ORDER BY thu_tu ASC, id ASC'
        );
        $stmt->execute([(int) $id]);
        return $stmt->fetchAll();
    }

    // Lay size nhieu mon (1 query) -> ['<monan_id>' => [rows]]
    public function sizesFor(array $ids)
    {
        return $this->nhomTheoMon('monan_size', $ids);
    }

    // Lay topping nhieu mon (1 query) -> ['<monan_id>' => [rows]]
    public function toppingsFor(array $ids)
    {
        return $this->nhomTheoMon('monan_topping', $ids);
    }

    private function nhomTheoMon($bang, array $ids)
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));

        if (empty($ids)) {
            return [];
        }

        if (!in_array($bang, ['monan_size', 'monan_topping'], true)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->pdo->prepare(
            "SELECT * FROM {$bang} WHERE monan_id IN ({$placeholders}) AND trang_thai = 1
             ORDER BY thu_tu ASC, id ASC"
        );
        $stmt->execute($ids);

        $ketQua = [];
        foreach ($stmt->fetchAll() as $row) {
            $ketQua[(int) $row['monan_id']][] = $row;
        }

        return $ketQua;
    }

    // Món liên quan: cung danh muc truoc, con thieu thi bo sung mon dang ban khac
    public function getRelated($id, $danhmucId, $limit = 4)
    {
        $id = (int) $id;
        $danhmucId = (int) $danhmucId;
        $limit = (int) $limit;

        $sql = 'SELECT m.*, d.ten_danh_muc
                FROM MONAN m
                LEFT JOIN DANHMUC d ON m.danhmuc_id = d.id
                WHERE m.trang_thai = 1 AND m.id != ?';
        $params = [$id];

        if ($danhmucId > 0) {
            $sql .= ' AND m.danhmuc_id = ?';
            $params[] = $danhmucId;
        }

        $sql .= ' ORDER BY m.id DESC LIMIT ' . $limit;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $related = $stmt->fetchAll();

        if (count($related) < $limit) {
            $excludeIds = array_merge([$id], array_map(fn($r) => (int) $r['id'], $related));
            $placeholders = implode(',', array_fill(0, count($excludeIds), '?'));
            $sql2 = 'SELECT m.*, d.ten_danh_muc
                     FROM MONAN m
                     LEFT JOIN DANHMUC d ON m.danhmuc_id = d.id
                     WHERE m.trang_thai = 1 AND m.id NOT IN (' . $placeholders . ')
                     ORDER BY m.id DESC LIMIT ' . ($limit - count($related));
            $stmt2 = $this->pdo->prepare($sql2);
            $stmt2->execute($excludeIds);
            $related = array_merge($related, $stmt2->fetchAll());
        }

        return $related;
    }
}
