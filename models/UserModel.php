<?php

class UserModel extends BaseModel
{
    public function findByEmail($email)
    {
        $query = $this->pdo->prepare(
            'SELECT id, ho_ten, email, mat_khau FROM NGUOIDUNG WHERE email = ? LIMIT 1'
        );
        $query->execute([$email]);
        return $query->fetch();
    }

    public function create($name, $email, $password)
    {
        $query = $this->pdo->prepare(
            'INSERT INTO NGUOIDUNG (ho_ten, email, mat_khau) VALUES (?, ?, ?)'
        );
        $query->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT)]);
        return (int) $this->pdo->lastInsertId();
    }
}
