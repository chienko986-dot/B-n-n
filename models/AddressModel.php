<?php

/**
 * Model SO DIA CHI (DIACHI_NHANHANG)
 * - Luu nhieu dia chi cho moi nguoi dung, dat hang thi chon 1 dia chi da luu
 * - Moi thao tac deu kiem tra nguoi so huu (nguoidung_id) de khong sua/xoa cua nguoi khac
 * - mac_dinh = 1: dia chi duoc chon san khi vao trang dat hang
 */
class AddressModel extends BaseModel
{
    protected $table = 'diachi_nhanhang';

    /**
     * Lay toan bo dia chi cua 1 nguoi dung (dia chi mac dinh len dau).
     * @return array
     */
    public function getByUser($userId)
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM diachi_nhanhang
             WHERE nguoidung_id = ?
             ORDER BY mac_dinh DESC, id ASC'
        );
        $stmt->execute([(int) $userId]);
        return $stmt->fetchAll();
    }

    /**
     * Lay 1 dia chi nhung CHI khi no thuoc ve nguoi dung (phan quyen).
     * @return array|null
     */
    public function findOwned($id, $userId)
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM diachi_nhanhang
             WHERE id = ? AND nguoidung_id = ? LIMIT 1'
        );
        $stmt->execute([(int) $id, (int) $userId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Them dia chi moi.
     * @param int   $userId
     * @param array $data [ten_nguoi_nhan, so_dien_thoai, dia_chi_chi_tiet,
     *                     tinh_id, huyen_id, xa_id, so_nha, ghi_chu, mac_dinh]
     * @return int id dia chi vua tao
     */
    public function create($userId, array $data)
    {
        $this->pdo->beginTransaction();

        try {
            $stmt = $this->pdo->prepare(
                'INSERT INTO diachi_nhanhang
                    (nguoidung_id, ten_nguoi_nhan, so_dien_thoai, dia_chi_chi_tiet,
                     ghi_chu, tinh_id, huyen_id, xa_id, so_nha, mac_dinh)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 0)'
            );
            $stmt->execute([
                (int) $userId,
                $data['ten_nguoi_nhan'],
                $data['so_dien_thoai'],
                $data['dia_chi_chi_tiet'],
                $data['ghi_chu'] ?? null,
                $data['tinh_id'] ?: null,
                $data['huyen_id'] ?: null,
                $data['xa_id'] ?: null,
                $data['so_nha'] ?: null,
            ]);

            $id = (int) $this->pdo->lastInsertId();

            // Dia chi dau tien hoac khach tick "mac dinh" -> chay thanh mac dinh
            $stmtCount = $this->pdo->prepare('SELECT COUNT(*) FROM diachi_nhanhang WHERE nguoidung_id = ?');
            $stmtCount->execute([(int) $userId]);
            $soDiaChi = (int) $stmtCount->fetchColumn();

            if (!empty($data['mac_dinh']) || $soDiaChi === 1) {
                $this->setOnlyDefault($id, $userId);
            }

            $this->pdo->commit();
            return $id;
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Sua 1 dia chi (chi sua cua chinh minh).
     * @return bool
     */
    public function update($id, $userId, array $data)
    {
        if (!$this->findOwned($id, $userId)) {
            return false;
        }

        $this->pdo->beginTransaction();

        try {
            $stmt = $this->pdo->prepare(
                'UPDATE diachi_nhanhang
                 SET ten_nguoi_nhan = ?, so_dien_thoai = ?, dia_chi_chi_tiet = ?,
                     ghi_chu = ?, tinh_id = ?, huyen_id = ?, xa_id = ?, so_nha = ?
                 WHERE id = ? AND nguoidung_id = ?'
            );
            $stmt->execute([
                $data['ten_nguoi_nhan'],
                $data['so_dien_thoai'],
                $data['dia_chi_chi_tiet'],
                $data['ghi_chu'] ?? null,
                $data['tinh_id'] ?: null,
                $data['huyen_id'] ?: null,
                $data['xa_id'] ?: null,
                $data['so_nha'] ?: null,
                (int) $id,
                (int) $userId,
            ]);

            if (!empty($data['mac_dinh'])) {
                $this->setOnlyDefault($id, $userId);
            }

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Xoa 1 dia chi (chi xoa cua chinh minh).
     * Neu dia chi da dung trong don hang thi MySQL chan (FK RESTRICT)
     * -> tra ve false de controller bao loi dep.
     *
     * @return bool true = xoa thanh cong
     */
    public function delete($id, $userId)
    {
        $diaChi = $this->findOwned($id, $userId);
        if (!$diaChi) {
            return false;
        }

        try {
            $stmt = $this->pdo->prepare(
                'DELETE FROM diachi_nhanhang WHERE id = ? AND nguoidung_id = ?'
            );
            $stmt->execute([(int) $id, (int) $userId]);
        } catch (PDOException $e) {
            // 23000 = vi rang buoc (dia chi dang duoc don hang tham chieu)
            if ($e->getCode() === '23000') {
                return false;
            }
            throw $e;
        }

        // Xoa xong: neu do la dia chi mac dinh thi dua dia chi con lai len mac dinh
        if ((int) $diaChi['mac_dinh'] === 1) {
            $first = $this->pdo->prepare(
                'SELECT id FROM diachi_nhanhang WHERE nguoidung_id = ? ORDER BY id ASC LIMIT 1'
            );
            $first->execute([(int) $userId]);
            $nextId = $first->fetchColumn();

            if ($nextId) {
                $this->setOnlyDefault((int) $nextId, $userId);
            }
        }

        return true;
    }

    /**
     * Dat 1 dia chi thanh mac dinh, cac dia chi khac cua nguoi do ve 0.
     */
    public function setDefault($id, $userId)
    {
        if (!$this->findOwned($id, $userId)) {
            return false;
        }
        $this->setOnlyDefault($id, $userId);
        return true;
    }

    /**
     * Chi giu 1 dia chi mac dinh (goi ben trong transaction).
     */
    private function setOnlyDefault($id, $userId)
    {
        $stmt = $this->pdo->prepare(
            'UPDATE diachi_nhanhang SET mac_dinh = 0 WHERE nguoidung_id = ?'
        );
        $stmt->execute([(int) $userId]);

        $stmt = $this->pdo->prepare(
            'UPDATE diachi_nhanhang SET mac_dinh = 1 WHERE id = ? AND nguoidung_id = ?'
        );
        $stmt->execute([(int) $id, (int) $userId]);
    }
}
