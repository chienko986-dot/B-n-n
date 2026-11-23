-- =====================================================================
-- FILE: database_sach_dia_chi.sql
-- Bo sung cau truc cho SO DIA CHI (chon nhanh dia chi khi dat hang)
--
-- Bang DIACHI_NHANHANG da co: id, nguoidung_id, ten_nguoi_nhan,
-- so_dien_thoai, dia_chi_chi_tiet, ghi_chu
-- Chi THEM cac cot moi (khong tao bang moi, khong sua du lieu cu):
--   + tinh_id / huyen_id / xa_id : ma dia chinh de sua dia chi bang dropdown
--   + so_nha                     : so nha, duong
--   + mac_dinh                   : dia chi mac dinh (chon san khi dat hang)
-- =====================================================================

ALTER TABLE diachi_nhanhang
    ADD COLUMN tinh_id   VARCHAR(20) NULL AFTER ghi_chu,
    ADD COLUMN huyen_id  VARCHAR(20) NULL AFTER tinh_id,
    ADD COLUMN xa_id     VARCHAR(20) NULL AFTER huyen_id,
    ADD COLUMN so_nha    VARCHAR(255) NULL AFTER xa_id,
    ADD COLUMN mac_dinh  TINYINT(1) NOT NULL DEFAULT 0 AFTER so_nha;
