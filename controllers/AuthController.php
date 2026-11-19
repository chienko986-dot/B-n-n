<?php

class AuthController
{
    public function register()
    {
        if (isset($_SESSION['user'])) {
            header('Location: ' . BASE_URL);
            exit;
        }

        $error = '';
        $name = '';
        $email = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = is_string($_POST['ho_ten'] ?? null) ? trim($_POST['ho_ten']) : '';
            $email = is_string($_POST['email'] ?? null) ? strtolower(trim($_POST['email'])) : '';
            $password = is_string($_POST['mat_khau'] ?? null) ? $_POST['mat_khau'] : '';
            $passwordConfirmation = is_string($_POST['xac_nhan_mat_khau'] ?? null)
                ? $_POST['xac_nhan_mat_khau']
                : '';
            $nameLength = preg_match_all('/./us', $name);

            if (!is_valid_csrf_token($_POST['_csrf_token'] ?? null)) {
                $error = 'Phiên làm việc đã hết hạn. Vui lòng tải lại trang và thử lại.';
            } elseif ($name === '' || $nameLength === false || $nameLength > 150) {
                $error = 'Họ tên không được để trống và tối đa 150 ký tự.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 255) {
                $error = 'Vui lòng nhập địa chỉ email hợp lệ.';
            } elseif (strlen($password) < 8 || strlen($password) > 72) {
                $error = 'Mật khẩu cần có từ 8 đến 72 ký tự.';
            } elseif ($password !== $passwordConfirmation) {
                $error = 'Mật khẩu xác nhận không khớp.';
            } else {
                $userModel = new UserModel();
                if ($userModel->findByEmail($email)) {
                    $error = 'Email này đã được đăng ký.';
                } else {
                    try {
                        $id = $userModel->create($name, $email, $password);
                        session_regenerate_id(true);
                        unset($_SESSION['_csrf_token']);
                        $_SESSION['user'] = ['id' => $id, 'ho_ten' => $name, 'email' => $email, 'vai_tro' => 'khach'];
                        header('Location: ' . BASE_URL);
                        exit;
                    } catch (PDOException $exception) {
                        if ($exception->getCode() !== '23000') {
                            throw $exception;
                        }
                        $error = 'Email này đã được đăng ký.';
                    }
                }
            }
        }

        $title = 'Đăng ký tài khoản';
        $view = 'auth/register';
        require PATH_VIEW_MAIN;
    }

    public function login()
    {
        if (isset($_SESSION['user'])) {
            header('Location: ' . BASE_URL);
            exit;
        }

        $error = '';
        $email = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = is_string($_POST['email'] ?? null) ? strtolower(trim($_POST['email'])) : '';
            $password = is_string($_POST['mat_khau'] ?? null) ? $_POST['mat_khau'] : '';

            if (!is_valid_csrf_token($_POST['_csrf_token'] ?? null)) {
                $error = 'Phiên làm việc đã hết hạn. Vui lòng tải lại trang và thử lại.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
                $error = 'Email hoặc mật khẩu không đúng.';
            } else {
                $user = (new UserModel())->findByEmail($email);

                if ($user && password_verify($password, $user['mat_khau'])) {
                    session_regenerate_id(true);
                    unset($_SESSION['_csrf_token']);
                    $_SESSION['user'] = [
                        'id' => (int) $user['id'],
                        'ho_ten' => $user['ho_ten'],
                        'email' => $user['email'],
                        'vai_tro' => $user['vai_tro'] ?? 'khach',
                    ];
                    
                    if (!empty($_GET['redirect'])) {
                        header('Location: ' . BASE_URL . '?action=' . urlencode($_GET['redirect']));
                    } elseif (($user['vai_tro'] ?? '') === 'admin') {
                        header('Location: ' . BASE_URL . '?action=admin');
                    } else {
                        header('Location: ' . BASE_URL);
                    }
                    exit;
                }

                $error = 'Email hoặc mật khẩu không đúng.';
            }
        }

        $title = 'Đăng nhập';
        $view = 'auth/login';
        require PATH_VIEW_MAIN;
    }

    public function logout()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Allow: POST');
            http_response_code(405);
            exit('Phương thức không được hỗ trợ.');
        }

        if (!is_valid_csrf_token($_POST['_csrf_token'] ?? null)) {
            http_response_code(403);
            exit('Yêu cầu không hợp lệ. Vui lòng tải lại trang.');
        }

        $_SESSION = [];
        session_regenerate_id(true);
        header('Location: ' . BASE_URL);
        exit;
    }
}
