<?php

class CategoryModel extends BaseModel
{
    public function getAll()
    {
        $sql = 'SELECT d.*, COUNT(m.id) AS so_luong_mon 
                FROM DANHMUC d 
                LEFT JOIN MONAN m ON d.id = m.danhmuc_id 
                GROUP BY d.id 
                ORDER BY d.id ASC';
        return $this->pdo->query($sql)->fetchAll();
    }

    public function getActive()
    {
        $sql = 'SELECT * FROM DANHMUC WHERE trang_thai = 1 ORDER BY ten_danh_muc ASC';
        return $this->pdo->query($sql)->fetchAll();
    }

    public function getById($id)
    {
        $stmt = $this->pdo->prepare('SELECT * FROM DANHMUC WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create($name, $desc = '', $status = 1)
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO DANHMUC (ten_danh_muc, mo_ta, trang_thai) VALUES (?, ?, ?)'
        );
        $stmt->execute([$name, $desc, $status]);
        return (int) $this->pdo->lastInsertId();
    }

    public function update($id, $name, $desc = '', $status = 1)
    {
        $stmt = $this->pdo->prepare(
            'UPDATE DANHMUC SET ten_danh_muc = ?, mo_ta = ?, trang_thai = ? WHERE id = ?'
        );
        return $stmt->execute([$name, $desc, $status, $id]);
    }

    public function delete($id)
    {
        // Kiểm tra xem danh mục có món ăn không
        $check = $this->pdo->prepare('SELECT COUNT(*) FROM MONAN WHERE danhmuc_id = ?');
        $check->execute([$id]);
        if ($check->fetchColumn() > 0) {
            throw new Exception('Không thể xóa danh mục đang có món ăn!');
        }

        $stmt = $this->pdo->prepare('DELETE FROM DANHMUC WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public function count()
    {
        return (int) $this->pdo->query('SELECT COUNT(*) FROM DANHMUC')->fetchColumn();
    }
}
