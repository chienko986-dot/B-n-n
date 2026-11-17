<?php

class AuthController
{
    public function register()
    {
        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['ho_ten'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['mat_khau'] ?? '';

            if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
                $error = 'Vui lòng nhập đầy đủ thông tin hợp lệ.';
            } else {
                $userModel = new UserModel();
                if ($userModel->findByEmail($email)) {
                    $error = 'Email này đã được đăng ký.';
                } else {
                    $id = $userModel->create($name, $email, $password);
                    $_SESSION['user'] = ['id' => $id, 'ho_ten' => $name, 'email' => $email];
                    header('Location: ' . BASE_URL);
                    exit;
                }
            }
        }

        $title = 'Đăng ký tài khoản';
        $view = 'auth/register';
        require PATH_VIEW_MAIN;
    }

    public function login()
    {
        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['mat_khau'] ?? '';
            $user = (new UserModel())->findByEmail($email);

            if ($user && password_verify($password, $user['mat_khau'])) {
                $_SESSION['user'] = ['id' => (int) $user['id'], 'ho_ten' => $user['ho_ten'], 'email' => $user['email']];
                header('Location: ' . BASE_URL);
                exit;
            }

            $error = 'Email hoặc mật khẩu không đúng.';
        }

        $title = 'Đăng nhập';
        $view = 'auth/login';
        require PATH_VIEW_MAIN;
    }

    public function logout()
    {
        unset($_SESSION['user']);
        header('Location: ' . BASE_URL);
        exit;
    }
}
