-- ============================================================
-- NANG CAP TRANG CHI TIET MON AN (mon an co size / topping /
-- anh phu, gia khuyen mai, chi tiet don luu lua chon)
-- ============================================================

-- 1) Them cot tren MONAN (chua co trong bang)
--    gia_goc            : gia goc de hien gia giam (NULL = khong khuyen mai)
--    thanh_phan          : thanh phan / nguyen lieu mon an
--    khau_phan           : khau phan, vd "Phần cho 1 - 2 người"
--    thoi_gian_chuan_bi  : vd "15 - 20 phút"
ALTER TABLE monan
    ADD COLUMN gia_goc DECIMAL(12,2) NULL AFTER gia,
    ADD COLUMN thanh_phan TEXT NULL AFTER mo_ta,
    ADD COLUMN khau_phan VARCHAR(100) NULL AFTER thanh_phan,
    ADD COLUMN thoi_gian_chuan_bi VARCHAR(50) NULL AFTER khau_phan;

-- 2) Anh phu cua mon an (hien tai chua co anh phu, bang dung khi them anh sau)
CREATE TABLE monan_anh (
    id INT AUTO_INCREMENT PRIMARY KEY,
    monan_id INT NOT NULL,
    hinh_anh VARCHAR(500) NOT NULL,
    thu_tu TINYINT NOT NULL DEFAULT 0,
    INDEX idx_monan_anh_monan (monan_id),
    CONSTRAINT fk_monan_anh_monan FOREIGN KEY (monan_id) REFERENCES monan (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3) Kich thuoc (size) cua tung mon
CREATE TABLE monan_size (
    id INT AUTO_INCREMENT PRIMARY KEY,
    monan_id INT NOT NULL,
    ten_size VARCHAR(50) NOT NULL,
    gia_tang DECIMAL(12,2) NOT NULL DEFAULT 0,
    mac_dinh TINYINT(1) NOT NULL DEFAULT 0,
    thu_tu TINYINT NOT NULL DEFAULT 0,
    trang_thai TINYINT(1) NOT NULL DEFAULT 1,
    INDEX idx_monan_size_monan (monan_id),
    CONSTRAINT fk_monan_size_monan FOREIGN KEY (monan_id) REFERENCES monan (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4) Topping cua tung mon
CREATE TABLE monan_topping (
    id INT AUTO_INCREMENT PRIMARY KEY,
    monan_id INT NOT NULL,
    ten_topping VARCHAR(100) NOT NULL,
    gia DECIMAL(12,2) NOT NULL DEFAULT 0,
    thu_tu TINYINT NOT NULL DEFAULT 0,
    trang_thai TINYINT(1) NOT NULL DEFAULT 1,
    INDEX idx_monan_topping_monan (monan_id),
    CONSTRAINT fk_monan_topping_monan FOREIGN KEY (monan_id) REFERENCES monan (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5) Chi tiet don hang: luu lua chon cua khach
--    size_ten     : ten size da chon, vd "Lớn"
--    topping_list : topping da chon, vd "Trứng, Phô mai"
--    ghi_chu      : ghi chu rieng cho dong nay
ALTER TABLE chitiet_donhang
    ADD COLUMN size_ten VARCHAR(50) NULL,
    ADD COLUMN topping_list VARCHAR(500) NULL,
    ADD COLUMN ghi_chu VARCHAR(255) NULL;

-- 6) Bo rang buoc "1 don chi co 1 dong cua 1 mon" de cho phep
--    cung mon nhung size / topping khac (dong nay chung len cho FK donhang_id)
ALTER TABLE chitiet_donhang ADD INDEX idx_ctdh_donhang (donhang_id);
ALTER TABLE chitiet_donhang DROP INDEX uq_ctdh_donhang_monan;
