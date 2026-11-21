-- =====================================================================
-- FILE: database_dia_chinh.sql
-- Chuc nang: Dia chi giao hang co chon Tinh / Huyen / Xa
-- Cach dung: Import VAO SAU khi da import website_ban_do_an.sql
--            va database_mua_hang.sql
--            (phpMyAdmin -> chon database website_ban_do_an -> Import)
-- =====================================================================

USE website_ban_do_an;

SET NAMES utf8mb4;

-- ---------------------------------------------------------------------
-- 1. THEM COT DIA CHINH VAO DON HANG
--    - Chi luu ID (so) de lien ket voi du lieu tinh/huyen/xa
--    - so_nha: so nha, duong (phan con lai cua dia chi)
--    - tat ca deu NULL duoc -> don cu khong bi anh huong
--    - dia_chi_giao van luu day du chuoi de hien thi (khong can tra cuu lai)
-- ---------------------------------------------------------------------
ALTER TABLE DONHANG
    ADD COLUMN tinh_id  VARCHAR(10) NULL AFTER dia_chi_giao,
    ADD COLUMN huyen_id VARCHAR(10) NULL AFTER tinh_id,
    ADD COLUMN xa_id    VARCHAR(10) NULL AFTER huyen_id,
    ADD COLUMN so_nha   VARCHAR(255) NULL AFTER xa_id;

-- ---------------------------------------------------------------------
-- 2. INDEX (ho tro loc don hang theo tinh/tinh thanh)
-- ---------------------------------------------------------------------
ALTER TABLE DONHANG ADD INDEX idx_donhang_tinh (tinh_id);
