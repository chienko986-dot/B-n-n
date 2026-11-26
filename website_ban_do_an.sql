-- CSDL website ban do an truc tuyen
-- He quan tri: MySQL 8.0+
-- Chay file nay trong MySQL Workbench/phpMyAdmin.

CREATE DATABASE IF NOT EXISTS website_ban_do_an
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE website_ban_do_an;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS DANHGIA;
DROP TABLE IF EXISTS THANHTOAN;
DROP TABLE IF EXISTS CHITIET_DONHANG;
DROP TABLE IF EXISTS CHITIET_GIOHANG;
DROP TABLE IF EXISTS DONHANG;
DROP TABLE IF EXISTS DIACHI_NHANHANG;
DROP TABLE IF EXISTS GIOHANG;
DROP TABLE IF EXISTS MONAN;
DROP TABLE IF EXISTS DANHMUC;
DROP TABLE IF EXISTS NGUOIDUNG;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE NGUOIDUNG (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    ho_ten          VARCHAR(150) NOT NULL,
    email           VARCHAR(255) NOT NULL,
    mat_khau        VARCHAR(255) NOT NULL,
    so_dien_thoai   VARCHAR(20),
    dia_chi         VARCHAR(500),
    vai_tro         ENUM('khach', 'admin') NOT NULL DEFAULT 'khach',
    ngay_tao        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uq_nguoidung_email UNIQUE (email)
) ENGINE=InnoDB;

CREATE TABLE DANHMUC (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    ten_danh_muc    VARCHAR(150) NOT NULL,
    mo_ta           TEXT,
    trang_thai      TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE MONAN (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    danhmuc_id      INT NOT NULL,
    ten_mon         VARCHAR(200) NOT NULL,
    gia             DECIMAL(12,2) NOT NULL,
    mo_ta           TEXT,
    hinh_anh        VARCHAR(500),
    so_luong_ton    INT NOT NULL DEFAULT 0,
    trang_thai      TINYINT(1) NOT NULL DEFAULT 1,
    CONSTRAINT fk_monan_danhmuc
        FOREIGN KEY (danhmuc_id) REFERENCES DANHMUC(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT chk_monan_gia CHECK (gia >= 0),
    CONSTRAINT chk_monan_ton CHECK (so_luong_ton >= 0),
    INDEX idx_monan_danhmuc (danhmuc_id)
) ENGINE=InnoDB;

CREATE TABLE GIOHANG (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    nguoidung_id    INT NOT NULL,
    ngay_tao        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uq_giohang_nguoidung UNIQUE (nguoidung_id),
    CONSTRAINT fk_giohang_nguoidung
        FOREIGN KEY (nguoidung_id) REFERENCES NGUOIDUNG(id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE DIACHI_NHANHANG (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    nguoidung_id    INT NOT NULL,
    ten_nguoi_nhan  VARCHAR(150) NOT NULL,
    so_dien_thoai   VARCHAR(20) NOT NULL,
    dia_chi_chi_tiet VARCHAR(500) NOT NULL,
    ghi_chu         TEXT,
    CONSTRAINT fk_diachi_nguoidung
        FOREIGN KEY (nguoidung_id) REFERENCES NGUOIDUNG(id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    INDEX idx_diachi_nguoidung (nguoidung_id)
) ENGINE=InnoDB;

CREATE TABLE DONHANG (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    nguoidung_id    INT NOT NULL,
    diachi_id       INT NOT NULL,
    ngay_dat        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    tong_tien       DECIMAL(12,2) NOT NULL DEFAULT 0,
    phi_giao_hang   DECIMAL(12,2) NOT NULL DEFAULT 0,
    trang_thai      ENUM('cho_xac_nhan', 'dang_chuan_bi', 'cho_shipper_lay', 'dang_giao', 'hoan_thanh', 'huy')
                    NOT NULL DEFAULT 'cho_xac_nhan',
    ghi_chu         TEXT,
    CONSTRAINT fk_donhang_nguoidung
        FOREIGN KEY (nguoidung_id) REFERENCES NGUOIDUNG(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_donhang_diachi
        FOREIGN KEY (diachi_id) REFERENCES DIACHI_NHANHANG(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT chk_donhang_tong_tien CHECK (tong_tien >= 0),
    CONSTRAINT chk_donhang_phi_giao CHECK (phi_giao_hang >= 0),
    INDEX idx_donhang_nguoidung (nguoidung_id),
    INDEX idx_donhang_diachi (diachi_id),
    INDEX idx_donhang_ngay_dat (ngay_dat),
    INDEX idx_donhang_trang_thai (trang_thai)
) ENGINE=InnoDB;

CREATE TABLE CHITIET_DONHANG (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    donhang_id      INT NOT NULL,
    monan_id        INT NOT NULL,
    so_luong        INT NOT NULL,
    don_gia         DECIMAL(12,2) NOT NULL,
    thanh_tien      DECIMAL(12,2) NOT NULL,
    CONSTRAINT fk_ctdh_donhang
        FOREIGN KEY (donhang_id) REFERENCES DONHANG(id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_ctdh_monan
        FOREIGN KEY (monan_id) REFERENCES MONAN(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT chk_ctdh_so_luong CHECK (so_luong > 0),
    CONSTRAINT chk_ctdh_don_gia CHECK (don_gia >= 0),
    CONSTRAINT chk_ctdh_thanh_tien CHECK (thanh_tien >= 0),
    CONSTRAINT uq_ctdh_donhang_monan UNIQUE (donhang_id, monan_id),
    INDEX idx_ctdh_monan (monan_id)
) ENGINE=InnoDB;

CREATE TABLE CHITIET_GIOHANG (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    giohang_id      INT NOT NULL,
    monan_id        INT NOT NULL,
    so_luong        INT NOT NULL DEFAULT 1,
    CONSTRAINT fk_ctgh_giohang
        FOREIGN KEY (giohang_id) REFERENCES GIOHANG(id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_ctgh_monan
        FOREIGN KEY (monan_id) REFERENCES MONAN(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT chk_ctgh_so_luong CHECK (so_luong > 0),
    CONSTRAINT uq_ctgh_giohang_monan UNIQUE (giohang_id, monan_id),
    INDEX idx_ctgh_monan (monan_id)
) ENGINE=InnoDB;

CREATE TABLE THANHTOAN (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    donhang_id      INT NOT NULL,
    phuong_thuc     ENUM('tien_mat', 'chuyen_khoan') NOT NULL,
    so_tien         DECIMAL(12,2) NOT NULL,
    trang_thai      ENUM('chua_tt', 'da_tt') NOT NULL DEFAULT 'chua_tt',
    ngay_thanh_toan DATETIME NULL,
    CONSTRAINT uq_thanhtoan_donhang UNIQUE (donhang_id),
    CONSTRAINT fk_thanhtoan_donhang
        FOREIGN KEY (donhang_id) REFERENCES DONHANG(id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT chk_thanhtoan_so_tien CHECK (so_tien >= 0)
) ENGINE=InnoDB;

CREATE TABLE DANHGIA (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    nguoidung_id    INT NOT NULL,
    monan_id        INT NOT NULL,
    so_sao          TINYINT UNSIGNED NOT NULL,
    binh_luan       TEXT,
    ngay_danh_gia   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_danhgia_nguoidung
        FOREIGN KEY (nguoidung_id) REFERENCES NGUOIDUNG(id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_danhgia_monan
        FOREIGN KEY (monan_id) REFERENCES MONAN(id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT chk_danhgia_so_sao CHECK (so_sao BETWEEN 1 AND 5),
    INDEX idx_danhgia_nguoidung (nguoidung_id),
    INDEX idx_danhgia_monan (monan_id)
) ENGINE=InnoDB;

-- Ghi chu:
-- 1. Email nguoi dung la duy nhat.
-- 2. Moi nguoi dung co toi da mot gio hang (UNIQUE nguoidung_id).
-- 3. Moi don hang co toi da mot ban ghi thanh toan (UNIQUE donhang_id).
-- 4. Chi tiet don hang/gio hang khong lap lai cung mot mon an trong cung don/gio.
-- 5. Khi them don hang, ung dung can kiem tra diachi_id thuoc dung nguoidung_id.
-- 6. thanh_tien va tong_tien nen duoc tinh/cap nhat tu ung dung hoac trigger.
