-- =====================================================================
-- FILE: database_mua_hang.sql
-- Chuc nang: Cap nhat CSDL cho tinh nang MUA HANG (gio hang + dat hang)
-- Cach dung: Import VAO SAU khi da import website_ban_do_an.sql
--            (phpMyAdmin -> chon database website_ban_do_an -> Import)
-- Luu y: Phan ALTER chi chay mot lan. Neu import lai website_ban_do_an.sql
--        (no DROP bang) thi phai import lai file nay.
-- =====================================================================

USE website_ban_do_an;

SET NAMES utf8mb4;

-- ---------------------------------------------------------------------
-- 1. CAP NHAT BANG DONHANG
--    - Them ma_don (ma don hang rieng biet)
--    - Them thong tin khach hang (vi khach khong can dang nhap)
--    - Cho phep nguoidung_id / diachi_id la NULL (don cua khach le)
-- ---------------------------------------------------------------------
ALTER TABLE DONHANG
    ADD COLUMN ma_don        VARCHAR(30)  NOT NULL AFTER id,
    ADD COLUMN ho_ten        VARCHAR(150) NULL AFTER ma_don,
    ADD COLUMN so_dien_thoai VARCHAR(20)  NULL AFTER ho_ten,
    ADD COLUMN email         VARCHAR(255) NULL AFTER so_dien_thoai,
    ADD COLUMN dia_chi_giao  VARCHAR(500) NULL AFTER email;

ALTER TABLE DONHANG MODIFY nguoidung_id INT NULL;
ALTER TABLE DONHANG MODIFY diachi_id    INT NULL;

ALTER TABLE DONHANG ADD CONSTRAINT uq_donhang_ma_don UNIQUE (ma_don);

-- ---------------------------------------------------------------------
-- 2. DU LIEU MAU: DANH MUC (khop voi phan "Danh muc" o trang chu)
-- ---------------------------------------------------------------------
INSERT INTO DANHMUC (ten_danh_muc, mo_ta, trang_thai)
SELECT 'Món Việt', 'Các món ăn truyền thống Việt Nam', 1
WHERE NOT EXISTS (SELECT 1 FROM DANHMUC WHERE ten_danh_muc = 'Món Việt');

INSERT INTO DANHMUC (ten_danh_muc, mo_ta, trang_thai)
SELECT 'Phở & Bún', 'Phở, bún và các món nước', 1
WHERE NOT EXISTS (SELECT 1 FROM DANHMUC WHERE ten_danh_muc = 'Phở & Bún');

INSERT INTO DANHMUC (ten_danh_muc, mo_ta, trang_thai)
SELECT 'Fastfood', 'Burger, gà rán và đồ ăn nhanh', 1
WHERE NOT EXISTS (SELECT 1 FROM DANHMUC WHERE ten_danh_muc = 'Fastfood');

INSERT INTO DANHMUC (ten_danh_muc, mo_ta, trang_thai)
SELECT 'Pizza & Pasta', 'Pizza và mì Ý', 1
WHERE NOT EXISTS (SELECT 1 FROM DANHMUC WHERE ten_danh_muc = 'Pizza & Pasta');

INSERT INTO DANHMUC (ten_danh_muc, mo_ta, trang_thai)
SELECT 'Tráng miệng', 'Bánh và món ngọt', 1
WHERE NOT EXISTS (SELECT 1 FROM DANHMUC WHERE ten_danh_muc = 'Tráng miệng');

INSERT INTO DANHMUC (ten_danh_muc, mo_ta, trang_thai)
SELECT 'Đồ uống', 'Trà, nước ép và đồ uống', 1
WHERE NOT EXISTS (SELECT 1 FROM DANHMUC WHERE ten_danh_muc = 'Đồ uống');

-- ---------------------------------------------------------------------
-- 3. DU LIEU MAU: MON AN (khop voi 8 mon o "Thuc don" trang chu)
-- ---------------------------------------------------------------------
INSERT INTO MONAN (danhmuc_id, ten_mon, gia, mo_ta, hinh_anh, so_luong_ton, trang_thai)
SELECT d.id, 'Cơm tấm sườn nướng', 45000,
       'Sườn nướng mật ong, cơm tấm mềm, ăn kèm bì và chả trứng.',
       'assets/images/dishes/com-tam.jpg', 100, 1
FROM DANHMUC d
WHERE d.ten_danh_muc = 'Món Việt'
  AND NOT EXISTS (SELECT 1 FROM MONAN m WHERE m.ten_mon = 'Cơm tấm sườn nướng');

INSERT INTO MONAN (danhmuc_id, ten_mon, gia, mo_ta, hinh_anh, so_luong_ton, trang_thai)
SELECT d.id, 'Phở bò đặc biệt', 59000,
       'Nước dùng hầm xương 12 tiếng, bò tái, bò viên và gầu giòn.',
       'assets/images/dishes/pho-bo.jpg', 100, 1
FROM DANHMUC d
WHERE d.ten_danh_muc = 'Phở & Bún'
  AND NOT EXISTS (SELECT 1 FROM MONAN m WHERE m.ten_mon = 'Phở bò đặc biệt');

INSERT INTO MONAN (danhmuc_id, ten_mon, gia, mo_ta, hinh_anh, so_luong_ton, trang_thai)
SELECT d.id, 'Burger bò phô mai', 69000,
       'Bò Úc nướng than, phô mai cheddar tan chảy, sốt đặc biệt.',
       'assets/images/dishes/burger.jpg', 100, 1
FROM DANHMUC d
WHERE d.ten_danh_muc = 'Fastfood'
  AND NOT EXISTS (SELECT 1 FROM MONAN m WHERE m.ten_mon = 'Burger bò phô mai');

INSERT INTO MONAN (danhmuc_id, ten_mon, gia, mo_ta, hinh_anh, so_luong_ton, trang_thai)
SELECT d.id, 'Pizza hải sản', 159000,
       'Đế mỏng giòn, tôm, mực, thanh cua và phô mai mozzarella.',
       'assets/images/dishes/pizza.jpg', 100, 1
FROM DANHMUC d
WHERE d.ten_danh_muc = 'Pizza & Pasta'
  AND NOT EXISTS (SELECT 1 FROM MONAN m WHERE m.ten_mon = 'Pizza hải sản');

INSERT INTO MONAN (danhmuc_id, ten_mon, gia, mo_ta, hinh_anh, so_luong_ton, trang_thai)
SELECT d.id, 'Bún chả Hà Nội', 49000,
       'Chả viên nướng thơm lừng, nước mắm chua ngọt chuẩn vị Hà thành.',
       'assets/images/dishes/bun-cha.jpg', 100, 1
FROM DANHMUC d
WHERE d.ten_danh_muc = 'Phở & Bún'
  AND NOT EXISTS (SELECT 1 FROM MONAN m WHERE m.ten_mon = 'Bún chả Hà Nội');

INSERT INTO MONAN (danhmuc_id, ten_mon, gia, mo_ta, hinh_anh, so_luong_ton, trang_thai)
SELECT d.id, 'Gà rán giòn cay', 79000,
       'Gà ướp sốt cay Hàn Quốc, lớp vỏ giòn rụm, ăn là mê.',
       'assets/images/dishes/ga-ran.jpg', 100, 1
FROM DANHMUC d
WHERE d.ten_danh_muc = 'Fastfood'
  AND NOT EXISTS (SELECT 1 FROM MONAN m WHERE m.ten_mon = 'Gà rán giòn cay');

INSERT INTO MONAN (danhmuc_id, ten_mon, gia, mo_ta, hinh_anh, so_luong_ton, trang_thai)
SELECT d.id, 'Bánh flan trứng', 25000,
       'Flan mềm mịn, caramel đắng nhẹ, thêm cà phê đá là hết sảy.',
       'assets/images/dishes/flan.jpg', 100, 1
FROM DANHMUC d
WHERE d.ten_danh_muc = 'Tráng miệng'
  AND NOT EXISTS (SELECT 1 FROM MONAN m WHERE m.ten_mon = 'Bánh flan trứng');

INSERT INTO MONAN (danhmuc_id, ten_mon, gia, mo_ta, hinh_anh, so_luong_ton, trang_thai)
SELECT d.id, 'Trà đào cam sả', 35000,
       'Trà đào thơm, cam tươi và sả, ly lớn 700ml giải nhiệt.',
       'assets/images/dishes/tra-dao.jpg', 100, 1
FROM DANHMUC d
WHERE d.ten_danh_muc = 'Đồ uống'
  AND NOT EXISTS (SELECT 1 FROM MONAN m WHERE m.ten_mon = 'Trà đào cam sả');

-- ---------------------------------------------------------------------
-- GHI CHU QUY TAC TIEN TE
--   DONHANG.tong_tien      = tong tien hang (chua phi giao hang)
--   DONHANG.phi_giao_hang  = phi giao hang
--   Tong thanh toan         = tong_tien + phi_giao_hang
--   Phi giao hang: mien phi voi don tu 99.000d tro len (xem configs/env.php)
-- ---------------------------------------------------------------------
