<?php

class ReviewModel extends BaseModel
{
    public function getAll()
    {
        $sql = 'SELECT dg.*, nd.ho_ten AS ten_nguoidung, nd.email, m.ten_mon, m.hinh_anh
                FROM DANHGIA dg
                LEFT JOIN NGUOIDUNG nd ON dg.nguoidung_id = nd.id
                LEFT JOIN MONAN m ON dg.monan_id = m.id
                ORDER BY dg.ngay_danh_gia DESC';
        return $this->pdo->query($sql)->fetchAll();
    }

    // Danh gia cua 1 mon an (ket noi ten khach hang)
    public function getByDish($monanId)
    {
        $stmt = $this->pdo->prepare(
            'SELECT dg.*, nd.ho_ten AS ten_nguoidung
             FROM DANHGIA dg
             LEFT JOIN NGUOIDUNG nd ON dg.nguoidung_id = nd.id
             WHERE dg.monan_id = ?
             ORDER BY dg.ngay_danh_gia DESC'
        );
        $stmt->execute([(int) $monanId]);
        return $stmt->fetchAll();
    }

    public function delete($id)
    {
        $stmt = $this->pdo->prepare('DELETE FROM DANHGIA WHERE id = ?');
        return $stmt->execute([$id]);
    }

    /**
     * Khach hang nay da mua mon nay chua?
     * (Don da dat, khong tinh don da huy)
     * @return bool
     */
    public function daMuaMon($nguoidungId, $monanId)
    {
        $stmt = $this->pdo->prepare(
            'SELECT 1
             FROM CHITIET_DONHANG cd
             JOIN DONHANG dh ON dh.id = cd.donhang_id
             WHERE dh.nguoidung_id = ?
               AND cd.monan_id = ?
               AND dh.trang_thai != "huy"
             LIMIT 1'
        );
        $stmt->execute([(int) $nguoidungId, (int) $monanId]);
        return (bool) $stmt->fetchColumn();
    }

    /**
     * Danh gia da co (neu co) cua 1 khach cho 1 mon.
     * @return array|null
     */
    public function getByUserAndDish($nguoidungId, $monanId)
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM DANHGIA WHERE nguoidung_id = ? AND monan_id = ? LIMIT 1'
        );
        $stmt->execute([(int) $nguoidungId, (int) $monanId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Luu danh gia (1 khach chi co 1 danh gia cho 1 mon).
     * Neu da co thi cap nhat, chua co thi them moi.
     * @return bool
     */
    public function luuDanhGia($nguoidungId, $monanId, $soSao, $binhLuan = '')
    {
        $soSao = (int) $soSao;
        $binhLuan = mb_substr(trim((string) $binhLuan), 0, 1000, 'UTF-8');

        $cu = $this->getByUserAndDish($nguoidungId, $monanId);

        if ($cu) {
            $stmt = $this->pdo->prepare(
                'UPDATE DANHGIA SET so_sao = ?, binh_luan = ?, ngay_danh_gia = NOW() WHERE id = ?'
            );
            return $stmt->execute([$soSao, $binhLuan !== '' ? $binhLuan : null, (int) $cu['id']]);
        }

        $stmt = $this->pdo->prepare(
            'INSERT INTO DANHGIA (nguoidung_id, monan_id, so_sao, binh_luan, ngay_danh_gia)
             VALUES (?, ?, ?, ?, NOW())'
        );
        return $stmt->execute([
            (int) $nguoidungId,
            (int) $monanId,
            $soSao,
            $binhLuan !== '' ? $binhLuan : null,
        ]);
    }

    public function count()
    {
        return (int) $this->pdo->query('SELECT COUNT(*) FROM DANHGIA')->fetchColumn();
    }
}
