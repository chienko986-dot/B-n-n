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
            'INSERT INTO MONAN (danhmuc_id, ten_mon, gia, mo_ta, hinh_anh, so_luong_ton, trang_thai) 
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $data['danhmuc_id'],
            $data['ten_mon'],
            $data['gia'],
            $data['mo_ta'] ?? '',
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
            'mo_ta = ?',
            'so_luong_ton = ?',
            'trang_thai = ?',
        ];
        $params = [
            $data['danhmuc_id'],
            $data['ten_mon'],
            $data['gia'],
            $data['mo_ta'] ?? '',
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
}
