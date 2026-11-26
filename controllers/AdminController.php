<?php

class AdminController
{
    private DishModel $dishModel;
    private CategoryModel $categoryModel;
    private OrderModel $orderModel;
    private UserModel $userModel;
    private ReviewModel $reviewModel;

    public function __construct()
    {
        $this->dishModel = new DishModel();
        $this->categoryModel = new CategoryModel();
        $this->orderModel = new OrderModel();
        $this->userModel = new UserModel();
        $this->reviewModel = new ReviewModel();
    }

    private function checkAuth()
    {
        if (empty($_SESSION['user'])) {
            set_flash('error', 'Vui lòng đăng nhập với tài khoản Quản trị viên để truy cập trang quản trị.');
            header('Location: ' . BASE_URL . '?action=login&redirect=admin');
            exit;
        }

        if (($_SESSION['user']['vai_tro'] ?? '') !== 'admin') {
            set_flash('error', 'Tài khoản của bạn không có quyền truy cập khu vực Quản trị.');
            header('Location: ' . BASE_URL);
            exit;
        }
    }

    private function render($view, $data = [])
    {
        extract($data);
        $adminView = $view;
        require PATH_VIEW . 'admin/layout.php';
        exit;
    }

    // ==========================================
    // 1. DASHBOARD
    // ==========================================
    public function dashboard()
    {
        $this->checkAuth();

        $stats = $this->orderModel->getStats();
        $stats['total_dishes'] = $this->dishModel->count();
        $stats['total_users'] = $this->userModel->count();
        $stats['total_categories'] = $this->categoryModel->count();

        $statusCounts = $this->orderModel->countByStatus();
        $recentOrders = $this->orderModel->getRecentOrders(6);
        $topDishes = $this->dishModel->getTopSelling(5);

        $this->render('dashboard', [
            'title'        => 'Bảng điều khiển quản trị - AnLaMe',
            'currentPage'  => 'dashboard',
            'stats'        => $stats,
            'statusCounts' => $statusCounts,
            'recentOrders' => $recentOrders,
            'topDishes'    => $topDishes,
        ]);
    }

    // ==========================================
    // 2. MÓN ĂN (DISHES)
    // ==========================================
    public function dishes()
    {
        $this->checkAuth();

        $filters = [
            'search'     => $_GET['search'] ?? '',
            'danhmuc_id' => $_GET['category'] ?? '',
            'trang_thai' => $_GET['status'] ?? '',
        ];

        $dishes = $this->dishModel->getAll($filters);
        $categories = $this->categoryModel->getAll();

        $this->render('dishes/index', [
            'title'       => 'Quản lý Món ăn - AnLaMe Admin',
            'currentPage' => 'dishes',
            'dishes'      => $dishes,
            'categories'  => $categories,
            'filters'     => $filters,
        ]);
    }

    public function dishCreate()
    {
        $this->checkAuth();

        $categories = $this->categoryModel->getActive();
        $error = '';
        $dish = [
            'ten_mon'      => '',
            'danhmuc_id'   => '',
            'gia'          => '',
            'so_luong_ton' => 50,
            'trang_thai'   => 1,
            'mo_ta'        => '',
            'hinh_anh'     => '',
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!is_valid_csrf_token($_POST['_csrf_token'] ?? null)) {
                $error = 'Phiên làm việc đã hết hạn. Vui lòng thử lại.';
            } else {
                $tenMon = trim($_POST['ten_mon'] ?? '');
                $danhmucId = (int) ($_POST['danhmuc_id'] ?? 0);
                $gia = (float) ($_POST['gia'] ?? 0);
                $soLuongTon = (int) ($_POST['so_luong_ton'] ?? 0);
                $trangThai = isset($_POST['trang_thai']) ? 1 : 0;
                $moTa = trim($_POST['mo_ta'] ?? '');
                $imagePath = trim($_POST['hinh_anh_url'] ?? '');

                if ($tenMon === '') {
                    $error = 'Vui lòng nhập tên món ăn.';
                } elseif ($danhmucId <= 0) {
                    $error = 'Vui lòng chọn danh mục món ăn.';
                } elseif ($gia < 0) {
                    $error = 'Giá bán không được âm.';
                } else {
                    // Xử lý upload ảnh nếu có
                    if (!empty($_FILES['hinh_anh']['name'])) {
                        try {
                            $imagePath = upload_file('dishes', $_FILES['hinh_anh']);
                        } catch (Exception $e) {
                            $error = 'Lỗi upload ảnh: ' . $e->getMessage();
                        }
                    }

                    if ($error === '') {
                        $this->dishModel->create([
                            'danhmuc_id'   => $danhmucId,
                            'ten_mon'      => $tenMon,
                            'gia'          => $gia,
                            'mo_ta'        => $moTa,
                            'hinh_anh'     => $imagePath,
                            'so_luong_ton' => $soLuongTon,
                            'trang_thai'   => $trangThai,
                        ]);

                        set_flash('success', "Đã thêm món ăn '$tenMon' thành công!");
                        header('Location: ' . BASE_URL . '?action=admin/dishes');
                        exit;
                    }
                }

                $dish = [
                    'ten_mon'      => $tenMon,
                    'danhmuc_id'   => $danhmucId,
                    'gia'          => $gia,
                    'so_luong_ton' => $soLuongTon,
                    'trang_thai'   => $trangThai,
                    'mo_ta'        => $moTa,
                    'hinh_anh'     => $imagePath,
                ];
            }
        }

        $this->render('dishes/form', [
            'title'       => 'Thêm Món ăn mới - AnLaMe Admin',
            'currentPage' => 'dishes',
            'categories'  => $categories,
            'dish'        => $dish,
            'isEdit'      => false,
            'error'       => $error,
        ]);
    }

    public function dishEdit()
    {
        $this->checkAuth();

        $id = (int) ($_GET['id'] ?? 0);
        $dish = $this->dishModel->getById($id);

        if (!$dish) {
            set_flash('error', 'Không tìm thấy món ăn yêu cầu!');
            header('Location: ' . BASE_URL . '?action=admin/dishes');
            exit;
        }

        $categories = $this->categoryModel->getActive();
        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!is_valid_csrf_token($_POST['_csrf_token'] ?? null)) {
                $error = 'Phiên làm việc đã hết hạn. Vui lòng thử lại.';
            } else {
                $tenMon = trim($_POST['ten_mon'] ?? '');
                $danhmucId = (int) ($_POST['danhmuc_id'] ?? 0);
                $gia = (float) ($_POST['gia'] ?? 0);
                $soLuongTon = (int) ($_POST['so_luong_ton'] ?? 0);
                $trangThai = isset($_POST['trang_thai']) ? 1 : 0;
                $moTa = trim($_POST['mo_ta'] ?? '');
                $imagePath = $dish['hinh_anh'];

                if (!empty($_POST['hinh_anh_url'])) {
                    $imagePath = trim($_POST['hinh_anh_url']);
                }

                if ($tenMon === '') {
                    $error = 'Vui lòng nhập tên món ăn.';
                } elseif ($danhmucId <= 0) {
                    $error = 'Vui lòng chọn danh mục món ăn.';
                } elseif ($gia < 0) {
                    $error = 'Giá bán không được âm.';
                } else {
                    if (!empty($_FILES['hinh_anh']['name'])) {
                        try {
                            $imagePath = upload_file('dishes', $_FILES['hinh_anh']);
                        } catch (Exception $e) {
                            $error = 'Lỗi upload ảnh: ' . $e->getMessage();
                        }
                    }

                    if ($error === '') {
                        $this->dishModel->update($id, [
                            'danhmuc_id'   => $danhmucId,
                            'ten_mon'      => $tenMon,
                            'gia'          => $gia,
                            'mo_ta'        => $moTa,
                            'hinh_anh'     => $imagePath,
                            'so_luong_ton' => $soLuongTon,
                            'trang_thai'   => $trangThai,
                        ]);

                        set_flash('success', "Đã cập nhật món ăn '$tenMon' thành công!");
                        header('Location: ' . BASE_URL . '?action=admin/dishes');
                        exit;
                    }
                }

                $dish = array_merge($dish, [
                    'ten_mon'      => $tenMon,
                    'danhmuc_id'   => $danhmucId,
                    'gia'          => $gia,
                    'so_luong_ton' => $soLuongTon,
                    'trang_thai'   => $trangThai,
                    'mo_ta'        => $moTa,
                    'hinh_anh'     => $imagePath,
                ]);
            }
        }

        $this->render('dishes/form', [
            'title'       => 'Chỉnh sửa Món ăn - AnLaMe Admin',
            'currentPage' => 'dishes',
            'categories'  => $categories,
            'dish'        => $dish,
            'isEdit'      => true,
            'error'       => $error,
        ]);
    }

    public function dishDelete()
    {
        $this->checkAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !is_valid_csrf_token($_POST['_csrf_token'] ?? null)) {
            set_flash('error', 'Yêu cầu xóa không hợp lệ.');
            header('Location: ' . BASE_URL . '?action=admin/dishes');
            exit;
        }

        $id = (int) ($_POST['id'] ?? 0);
        $result = $this->dishModel->delete($id);

        if ($result === 'deleted') {
            set_flash('success', 'Đã xóa món ăn thành công.');
        } else {
            set_flash('info', 'Món ăn đã có lịch sử đơn hàng nên đã chuyển trạng thái sang [Tạm ngừng] thay vì xóa hẳn để bảo toàn dữ liệu.');
        }

        header('Location: ' . BASE_URL . '?action=admin/dishes');
        exit;
    }

    // ==========================================
    // 3. DANH MỤC (CATEGORIES)
    // ==========================================
    public function categories()
    {
        $this->checkAuth();

        $categories = $this->categoryModel->getAll();

        $this->render('categories/index', [
            'title'       => 'Quản lý Danh mục - AnLaMe Admin',
            'currentPage' => 'categories',
            'categories'  => $categories,
        ]);
    }

    public function categoryCreate()
    {
        $this->checkAuth();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!is_valid_csrf_token($_POST['_csrf_token'] ?? null)) {
                set_flash('error', 'Phiên làm việc đã hết hạn.');
            } else {
                $name = trim($_POST['ten_danh_muc'] ?? '');
                $desc = trim($_POST['mo_ta'] ?? '');
                $status = isset($_POST['trang_thai']) ? 1 : 0;

                if ($name === '') {
                    set_flash('error', 'Tên danh mục không được để trống.');
                } else {
                    $this->categoryModel->create($name, $desc, $status);
                    set_flash('success', "Đã thêm danh mục '$name' thành công!");
                }
            }
        }

        header('Location: ' . BASE_URL . '?action=admin/categories');
        exit;
    }

    public function categoryEdit()
    {
        $this->checkAuth();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!is_valid_csrf_token($_POST['_csrf_token'] ?? null)) {
                set_flash('error', 'Phiên làm việc đã hết hạn.');
            } else {
                $id = (int) ($_POST['id'] ?? 0);
                $name = trim($_POST['ten_danh_muc'] ?? '');
                $desc = trim($_POST['mo_ta'] ?? '');
                $status = isset($_POST['trang_thai']) ? 1 : 0;

                if ($name === '' || $id <= 0) {
                    set_flash('error', 'Dữ liệu danh mục không hợp lệ.');
                } else {
                    $this->categoryModel->update($id, $name, $desc, $status);
                    set_flash('success', "Đã cập nhật danh mục '$name' thành công!");
                }
            }
        }

        header('Location: ' . BASE_URL . '?action=admin/categories');
        exit;
    }

    public function categoryDelete()
    {
        $this->checkAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !is_valid_csrf_token($_POST['_csrf_token'] ?? null)) {
            set_flash('error', 'Yêu cầu không hợp lệ.');
            header('Location: ' . BASE_URL . '?action=admin/categories');
            exit;
        }

        $id = (int) ($_POST['id'] ?? 0);
        try {
            $this->categoryModel->delete($id);
            set_flash('success', 'Đã xóa danh mục thành công.');
        } catch (Exception $e) {
            set_flash('error', $e->getMessage());
        }

        header('Location: ' . BASE_URL . '?action=admin/categories');
        exit;
    }

    // ==========================================
    // 4. ĐƠN HÀNG (ORDERS)
    // ==========================================
    public function orders()
    {
        $this->checkAuth();

        $status = $_GET['status'] ?? 'all';
        $search = $_GET['search'] ?? '';

        $filters = [
            'status' => $status,
            'search' => $search,
        ];

        $orders = $this->orderModel->getAll($filters);
        foreach ($orders as &$order) {
            $order['next_statuses'] = $this->orderModel->getAllowedNextStatuses($order['trang_thai']);
        }
        unset($order);
        $statusCounts = $this->orderModel->countByStatus();

        $this->render('orders/index', [
            'title'        => 'Quản lý Đơn hàng - AnLaMe Admin',
            'currentPage'  => 'orders',
            'orders'       => $orders,
            'statusCounts' => $statusCounts,
            'currentStatus'=> $status,
            'search'       => $search,
        ]);
    }

    public function orderDetail()
    {
        $this->checkAuth();

        $id = (int) ($_GET['id'] ?? 0);
        $order = $this->orderModel->getById($id);

        if (!$order) {
            set_flash('error', 'Không tìm thấy đơn hàng yêu cầu.');
            header('Location: ' . BASE_URL . '?action=admin/orders');
            exit;
        }

        $items = $this->orderModel->getItems($id);

        $this->render('orders/detail', [
            'title'       => "Chi tiết Đơn hàng #$id - AnLaMe Admin",
            'currentPage' => 'orders',
            'order'       => $order,
            'items'       => $items,
            'nextStatuses' => $this->orderModel->getAllowedNextStatuses($order['trang_thai']),
        ]);
    }

    public function orderUpdateStatus()
    {
        $this->checkAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !is_valid_csrf_token($_POST['_csrf_token'] ?? null)) {
            set_flash('error', 'Yêu cầu không hợp lệ.');
            header('Location: ' . BASE_URL . '?action=admin/orders');
            exit;
        }

        $id = (int) ($_POST['id'] ?? 0);
        $status = $_POST['trang_thai'] ?? '';

        if ($this->orderModel->updateStatus($id, $status)) {
            set_flash('success', "Đã cập nhật trạng thái đơn hàng #$id sang [" . order_status_label($status) . "]!");
        } else {
            set_flash('error', 'Cập nhật trạng thái đơn hàng không thành công.');
        }

        $redirect = !empty($_POST['redirect_detail'])
            ? (BASE_URL . '?action=admin/orders/detail&id=' . $id)
            : (BASE_URL . '?action=admin/orders');

        header('Location: ' . $redirect);
        exit;
    }

    // ==========================================
    // 5. NGƯỜI DÙNG / KHÁCH HÀNG (USERS)
    // ==========================================
    public function users()
    {
        $this->checkAuth();

        $search = $_GET['search'] ?? '';
        $users = $this->userModel->getAll($search);

        $this->render('users/index', [
            'title'       => 'Quản lý Tài khoản Khách hàng - AnLaMe Admin',
            'currentPage' => 'users',
            'users'       => $users,
            'search'      => $search,
        ]);
    }

    public function userToggleRole()
    {
        $this->checkAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !is_valid_csrf_token($_POST['_csrf_token'] ?? null)) {
            set_flash('error', 'Yêu cầu không hợp lệ.');
            header('Location: ' . BASE_URL . '?action=admin/users');
            exit;
        }

        $id = (int) ($_POST['id'] ?? 0);
        $role = $_POST['vai_tro'] ?? 'khach';

        // Không cho phép tự hạ quyền của chính mình
        if ($id === (int) ($_SESSION['user']['id'] ?? 0) && $role !== 'admin') {
            set_flash('error', 'Bạn không thể tự hạ quyền Admin của chính tài khoản đang đăng nhập!');
            header('Location: ' . BASE_URL . '?action=admin/users');
            exit;
        }

        $this->userModel->updateRole($id, $role);
        set_flash('success', "Đã cập nhật vai trò người dùng #$id thành [$role].");
        header('Location: ' . BASE_URL . '?action=admin/users');
        exit;
    }

    // ==========================================
    // 6. ĐÁNH GIÁ (REVIEWS)
    // ==========================================
    public function reviews()
    {
        $this->checkAuth();

        $reviews = $this->reviewModel->getAll();

        $this->render('reviews/index', [
            'title'       => 'Quản lý Đánh giá - AnLaMe Admin',
            'currentPage' => 'reviews',
            'reviews'     => $reviews,
        ]);
    }

    public function reviewDelete()
    {
        $this->checkAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !is_valid_csrf_token($_POST['_csrf_token'] ?? null)) {
            set_flash('error', 'Yêu cầu không hợp lệ.');
            header('Location: ' . BASE_URL . '?action=admin/reviews');
            exit;
        }

        $id = (int) ($_POST['id'] ?? 0);
        $this->reviewModel->delete($id);
        set_flash('success', 'Đã xóa đánh giá thành công.');
        header('Location: ' . BASE_URL . '?action=admin/reviews');
        exit;
    }

    // ==========================================
    // 7. SEED DỮ LIỆU MẪU (DEMO DATA GENERATOR)
    // ==========================================
    public function seedData()
    {
        $userModel = new UserModel();
        $adminId = $userModel->ensureAdminAccount();

        // Tự động đăng nhập admin nếu chưa đăng nhập
        if (empty($_SESSION['user'])) {
            $adminUser = $userModel->findByEmail('admin@anlame.vn');
            if ($adminUser) {
                $_SESSION['user'] = [
                    'id'      => (int) $adminUser['id'],
                    'ho_ten'  => $adminUser['ho_ten'],
                    'email'   => $adminUser['email'],
                    'vai_tro' => 'admin',
                ];
            }
        }

        // Tạo khách hàng mẫu nếu chưa có
        $sampleCustomer = $userModel->findByEmail('khachhang@gmail.com');
        if (!$sampleCustomer) {
            $customerId = $userModel->create(
                'Nguyễn Văn Nam',
                'khachhang@gmail.com',
                '12345678',
                'khach',
                '0912345678',
                'Số 18, Phố Huế, Q. Hoàn Kiếm, Hà Nội'
            );
        } else {
            $customerId = $sampleCustomer['id'];
        }

        // Tạo danh mục mẫu nếu chưa có
        $categoriesList = [
            'Món Việt'      => 'Các món ăn truyền thống đậm đà bản sắc Việt Nam.',
            'Phở & Bún'     => 'Nước dùng ninh xương gia truyền 12 tiếng thơm lừng.',
            'Fastfood'      => 'Thức ăn nhanh giòn tan, tiện lợi và ngon miệng.',
            'Pizza & Pasta' => 'Hương vị Ý chuẩn điệu với đế bánh thơm giòn và phô mai kéo sợi.',
            'Tráng miệng'   => 'Bánh ngọt, flan và các món tráng miệng thanh mát.',
            'Đồ uống'       => 'Trà đào, nước ép và đồ uống giải nhiệt tươi mới.',
        ];

        $catMap = [];
        $existingCats = $this->categoryModel->getAll();
        foreach ($existingCats as $c) {
            $catMap[$c['ten_danh_muc']] = $c['id'];
        }

        foreach ($categoriesList as $catName => $catDesc) {
            if (!isset($catMap[$catName])) {
                $newId = $this->categoryModel->create($catName, $catDesc, 1);
                $catMap[$catName] = $newId;
            }
        }

        // Tạo món ăn mẫu nếu bảng món ăn đang trống
        if ($this->dishModel->count() === 0) {
            $sampleDishes = [
                [
                    'ten_mon'      => 'Cơm tấm sườn nướng mật ong',
                    'cat'          => 'Món Việt',
                    'gia'          => 45000,
                    'so_luong_ton' => 45,
                    'hinh_anh'     => 'assets/images/dishes/com-tam.jpg',
                    'mo_ta'        => 'Sườn cốt lết ướp mật ong nướng than hoa, cơm tấm dẻo bùi, bì heo và chả trứng hấp béo ngậy.',
                ],
                [
                    'ten_mon'      => 'Phở bò tái nạm đặc biệt',
                    'cat'          => 'Phở & Bún',
                    'gia'          => 59000,
                    'so_luong_ton' => 60,
                    'hinh_anh'     => 'assets/images/dishes/pho-bo.jpg',
                    'mo_ta'        => 'Bánh phở tươi mềm mướt, thịt bò tái mềm ngọt, nạm gầu giòn thơm cùng nước dùng ninh 12h.',
                ],
                [
                    'ten_mon'      => 'Burger bò phô mai 2 tầng',
                    'cat'          => 'Fastfood',
                    'gia'          => 69000,
                    'so_luong_ton' => 30,
                    'hinh_anh'     => 'assets/images/dishes/burger.jpg',
                    'mo_ta'        => 'Thịt bò Úc xay nướng than kèm 2 lát phô mai cheddar tan chảy, sốt mayonnaise cay và xà lách tươi.',
                ],
                [
                    'ten_mon'      => 'Pizza hải sản sốt Pesto',
                    'cat'          => 'Pizza & Pasta',
                    'gia'          => 159000,
                    'so_luong_ton' => 25,
                    'hinh_anh'     => 'assets/images/dishes/pizza.jpg',
                    'mo_ta'        => 'Đế bánh mỏng nướng củi, tôm sú tươi, mực lá giòn sần sật phủ đẫm sốt pesto thơm ngát và phô mai mozzarella.',
                ],
                [
                    'ten_mon'      => 'Bún chả Hà Nội nướng que tre',
                    'cat'          => 'Phở & Bún',
                    'gia'          => 49000,
                    'so_luong_ton' => 40,
                    'hinh_anh'     => 'assets/images/dishes/bun-cha.jpg',
                    'mo_ta'        => 'Chả miếng ba chỉ và chả băm nướng than hoa vàng rộm ngập trong chén nước mắm chua ngọt ấm nóng.',
                ],
                [
                    'ten_mon'      => 'Gà rán giòn sốt cay Hàn Quốc',
                    'cat'          => 'Fastfood',
                    'gia'          => 75000,
                    'so_luong_ton' => 35,
                    'hinh_anh'     => 'assets/images/dishes/ga-ran.jpg',
                    'mo_ta'        => 'Đùi gà chiên vàng rộm siêu giòn tan, phủ sốt cay ngọt Gochujang đặc trưng và rắc mè rang thơm lừng.',
                ],
                [
                    'ten_mon'      => 'Bánh Flan caramen trứng sữa',
                    'cat'          => 'Tráng miệng',
                    'gia'          => 22000,
                    'so_luong_ton' => 80,
                    'hinh_anh'     => 'assets/images/dishes/flan.jpg',
                    'mo_ta'        => 'Bánh flan mềm mịn tan ngay đầu lưỡi, caramen ngọt thanh đắng nhẹ cùng chút cà phê đen đậm vị.',
                ],
                [
                    'ten_mon'      => 'Trà đào sả cam tươi',
                    'cat'          => 'Đồ uống',
                    'gia'          => 35000,
                    'so_luong_ton' => 100,
                    'hinh_anh'     => 'assets/images/dishes/tra-dao.jpg',
                    'mo_ta'        => 'Trà đen thơm nồng phối cùng cam vàng tươi mọng, vị sả thơm thoảng và đào giòn miếng thanh nhiệt.',
                ],
            ];

            foreach ($sampleDishes as $sd) {
                $danhmucId = $catMap[$sd['cat']] ?? reset($catMap);
                $this->dishModel->create([
                    'danhmuc_id'   => $danhmucId,
                    'ten_mon'      => $sd['ten_mon'],
                    'gia'          => $sd['gia'],
                    'mo_ta'        => $sd['mo_ta'],
                    'hinh_anh'     => $sd['hinh_anh'],
                    'so_luong_ton' => $sd['so_luong_ton'],
                    'trang_thai'   => 1,
                ]);
            }
        }

        // Tạo địa chỉ nhận hàng & đơn hàng mẫu nếu bảng đơn hàng đang trống
        if ($this->orderModel->countByStatus()['all'] === 0) {
            $pdo = (new BaseModel())->pdo ?? null;
            // Dùng reflection hoặc query qua pdo
            $stmt = (new class extends BaseModel {
                public function getPdo() { return $this->pdo; }
            })->getPdo();

            // Địa chỉ nhận hàng mẫu
            $stmt->prepare(
                'INSERT INTO DIACHI_NHANHANG (nguoidung_id, ten_nguoi_nhan, so_dien_thoai, dia_chi_chi_tiet, ghi_chu) 
                 VALUES (?, ?, ?, ?, ?)'
            )->execute([$customerId, 'Nguyễn Văn Nam', '0912345678', 'Tòa Vincom Center, 191 Bà Triệu, Hà Nội', 'Giao giờ hành chính, gọi trước khi đến']);
            $diachiId = (int) $stmt->lastInsertId();

            $dishes = $this->dishModel->getAll();
            if (!empty($dishes)) {
                $dish1 = $dishes[0];
                $dish2 = $dishes[1] ?? $dishes[0];

                // Đơn 1: Đang giao
                $tongTien1 = $dish1['gia'] * 2;
                $stmt->prepare(
                    "INSERT INTO DONHANG (nguoidung_id, diachi_id, ngay_dat, tong_tien, phi_giao_hang, trang_thai, ghi_chu)
                     VALUES (?, ?, NOW() - INTERVAL 1 HOUR, ?, 15000, 'dang_giao', 'Cho thêm ớt và rau thơm')"
                )->execute([$customerId, $diachiId, $tongTien1]);
                $dhId1 = (int) $stmt->lastInsertId();

                $stmt->prepare(
                    'INSERT INTO CHITIET_DONHANG (donhang_id, monan_id, so_luong, don_gia, thanh_tien) VALUES (?, ?, 2, ?, ?)'
                )->execute([$dhId1, $dish1['id'], $dish1['gia'], $tongTien1]);

                $stmt->prepare(
                    "INSERT INTO THANHTOAN (donhang_id, phuong_thuc, so_tien, trang_thai) VALUES (?, 'chuyen_khoan', ?, 'da_tt')"
                )->execute([$dhId1, $tongTien1 + 15000]);

                // Đơn 2: Chờ xác nhận
                $tongTien2 = $dish2['gia'] * 1;
                $stmt->prepare(
                    "INSERT INTO DONHANG (nguoidung_id, diachi_id, ngay_dat, tong_tien, phi_giao_hang, trang_thai, ghi_chu)
                     VALUES (?, ?, NOW() - INTERVAL 15 MINUTE, ?, 0, 'cho_xac_nhan', 'Giao nhanh giúp mình')"
                )->execute([$customerId, $diachiId, $tongTien2]);
                $dhId2 = (int) $stmt->lastInsertId();

                $stmt->prepare(
                    'INSERT INTO CHITIET_DONHANG (donhang_id, monan_id, so_luong, don_gia, thanh_tien) VALUES (?, ?, 1, ?, ?)'
                )->execute([$dhId2, $dish2['id'], $dish2['gia'], $tongTien2]);

                $stmt->prepare(
                    "INSERT INTO THANHTOAN (donhang_id, phuong_thuc, so_tien, trang_thai) VALUES (?, 'tien_mat', ?, 'chua_tt')"
                )->execute([$dhId2, $tongTien2]);

                // Đơn 3: Hoàn thành
                $tongTien3 = $dish1['gia'] + $dish2['gia'];
                $stmt->prepare(
                    "INSERT INTO DONHANG (nguoidung_id, diachi_id, ngay_dat, tong_tien, phi_giao_hang, trang_thai, ghi_chu)
                     VALUES (?, ?, NOW() - INTERVAL 1 DAY, ?, 0, 'hoan_thanh', 'Đã giao thành công')"
                )->execute([$customerId, $diachiId, $tongTien3]);
                $dhId3 = (int) $stmt->lastInsertId();

                $stmt->prepare(
                    'INSERT INTO CHITIET_DONHANG (donhang_id, monan_id, so_luong, don_gia, thanh_tien) VALUES (?, ?, 1, ?, ?)'
                )->execute([$dhId3, $dish1['id'], $dish1['gia'], $dish1['gia']]);
                $stmt->prepare(
                    'INSERT INTO CHITIET_DONHANG (donhang_id, monan_id, so_luong, don_gia, thanh_tien) VALUES (?, ?, 1, ?, ?)'
                )->execute([$dhId3, $dish2['id'], $dish2['gia'], $dish2['gia']]);

                $stmt->prepare(
                    "INSERT INTO THANHTOAN (donhang_id, phuong_thuc, so_tien, trang_thai, ngay_thanh_toan) VALUES (?, 'tien_mat', ?, 'da_tt', NOW())"
                )->execute([$dhId3, $tongTien3]);

                // Thêm đánh giá mẫu
                $stmt->prepare(
                    "INSERT INTO DANHGIA (nguoidung_id, monan_id, so_sao, binh_luan) VALUES (?, ?, 5, 'Món ăn rất tươi ngon, giao hàng siêu nhanh, đóng gói cẩn thận 10 điểm!')"
                )->execute([$customerId, $dish1['id']]);
            }
        }

        set_flash('success', 'Đã nạp dữ liệu mẫu hoàn tất! Bạn có thể trải nghiệm toàn bộ giao diện quản trị.');
        header('Location: ' . BASE_URL . '?action=admin');
        exit;
    }
}
