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

    public function delete($id)
    {
        $stmt = $this->pdo->prepare('DELETE FROM DANHGIA WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public function count()
    {
        return (int) $this->pdo->query('SELECT COUNT(*) FROM DANHGIA')->fetchColumn();
    }
}
