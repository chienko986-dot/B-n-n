<?php

/**
 * Model MON AN
 * - Ket noi CSDL qua BaseModel (PDO)
 * - Dung de: kiem tra mon an co ton tai hay khong, lay gia tu MySQL,
 *   kiem tra va tru so luong ton kho khi dat hang.
 */
class ProductModel extends BaseModel
{
    protected $table = 'monan';

    /**
     * Lay 1 mon an theo id.
     * Chi lay mon con dang ban (trang_thai = 1).
     * @return array|null
     */
    public function findById($id)
    {
        $id   = (int) $id;
        $sql  = "SELECT m.*, d.ten_danh_muc
                 FROM {$this->table} m
                 JOIN danhmuc d ON d.id = m.danhmuc_id
                 WHERE m.id = ? AND m.trang_thai = 1
                 LIMIT 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);

        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Lay nhieu mon an theo danh sach id (giu theo thu tu id truyen vao).
     * @param array $ids
     * @return array [id => row]
     */
    public function findManyByIds(array $ids)
    {
        $ids = array_values(array_unique(array_map('intval', $ids)));

        if (empty($ids)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sql = "SELECT m.*, d.ten_danh_muc
                FROM {$this->table} m
                JOIN danhmuc d ON d.id = m.danhmuc_id
                WHERE m.id IN ($placeholders) AND m.trang_thai = 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($ids);

        $result = [];
        foreach ($stmt->fetchAll() as $row) {
            $result[(int) $row['id']] = $row;
        }

        return $result;
    }

    /**
     * Tru so luong ton kho.
     * Chi tru duoc khi ton kho du (khong cho so luong am).
     * @return bool true = tru thanh cong, false = khong du ton kho
     */
    public function decreaseStock($id, $quantity)
    {
        $id       = (int) $id;
        $quantity = (int) $quantity;

        $sql = "UPDATE {$this->table}
                SET so_luong_ton = so_luong_ton - ?
                WHERE id = ? AND so_luong_ton >= ? AND trang_thai = 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$quantity, $id, $quantity]);

        return $stmt->rowCount() > 0;
    }
}
