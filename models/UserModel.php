<?php

class UserModel extends BaseModel
{
    public function findByEmail($email)
    {
        $query = $this->pdo->prepare(
            'SELECT id, ho_ten, email, mat_khau, so_dien_thoai, dia_chi, vai_tro, ngay_tao 
             FROM NGUOIDUNG WHERE email = ? LIMIT 1'
        );
        $query->execute([$email]);
        return $query->fetch();
    }

    public function findById($id)
    {
        $query = $this->pdo->prepare(
            'SELECT id, ho_ten, email, so_dien_thoai, dia_chi, vai_tro, ngay_tao 
             FROM NGUOIDUNG WHERE id = ? LIMIT 1'
        );
        $query->execute([$id]);
        return $query->fetch();
    }

    public function create($name, $email, $password, $role = 'khach', $phone = null, $address = null)
    {
        $query = $this->pdo->prepare(
            'INSERT INTO NGUOIDUNG (ho_ten, email, mat_khau, vai_tro, so_dien_thoai, dia_chi) 
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $query->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT), $role, $phone, $address]);
        return (int) $this->pdo->lastInsertId();
    }

    public function getAll($search = '')
    {
        if (!empty($search)) {
            $query = $this->pdo->prepare(
                'SELECT id, ho_ten, email, so_dien_thoai, dia_chi, vai_tro, ngay_tao 
                 FROM NGUOIDUNG 
                 WHERE ho_ten LIKE ? OR email LIKE ? OR so_dien_thoai LIKE ?
                 ORDER BY id DESC'
            );
            $param = '%' . $search . '%';
            $query->execute([$param, $param, $param]);
            return $query->fetchAll();
        }

        $query = $this->pdo->query(
            'SELECT id, ho_ten, email, so_dien_thoai, dia_chi, vai_tro, ngay_tao 
             FROM NGUOIDUNG ORDER BY id DESC'
        );
        return $query->fetchAll();
    }

    public function count()
    {
        return (int) $this->pdo->query('SELECT COUNT(*) FROM NGUOIDUNG')->fetchColumn();
    }

    public function updateProfile($id, $name, $phone = null, $address = null)
    {
        $query = $this->pdo->prepare(
            'UPDATE NGUOIDUNG SET ho_ten = ?, so_dien_thoai = ?, dia_chi = ? WHERE id = ?'
        );
        return $query->execute([$name, $phone, $address, (int) $id]);
    }

    public function updatePassword($id, $newPassword)
    {
        $query = $this->pdo->prepare('UPDATE NGUOIDUNG SET mat_khau = ? WHERE id = ?');
        return $query->execute([password_hash($newPassword, PASSWORD_DEFAULT), (int) $id]);
    }

    public function updateRole($id, $role)
    {
        if (!in_array($role, ['khach', 'admin'], true)) {
            return false;
        }
        $query = $this->pdo->prepare('UPDATE NGUOIDUNG SET vai_tro = ? WHERE id = ?');
        return $query->execute([$role, $id]);
    }

    public function hasAdmin()
    {
        $query = $this->pdo->query("SELECT COUNT(*) FROM NGUOIDUNG WHERE vai_tro = 'admin'");
        return ((int) $query->fetchColumn()) > 0;
    }

    public function ensureAdminAccount()
    {
        if (!$this->hasAdmin()) {
            $existing = $this->findByEmail('admin@anlame.vn');
            if ($existing) {
                $this->updateRole($existing['id'], 'admin');
                return $existing['id'];
            }
            return $this->create('Quản Trị Viên AnLaMe', 'admin@anlame.vn', 'admin123', 'admin');
        }
        return true;
    }
}

