-- ============================================================
-- DU LIEU THUC DON CHO TRANG CHI TIET MON AN
-- (thanh phan / khau phan / thoi gian, size, topping, gia khuyen mai)
-- ============================================================

-- ---------- MONAN: thanh phan, khau phan, thoi gian, gia khuyen mai ----------
UPDATE monan SET
    gia_goc = 199000,
    thanh_phan = 'Đế pizza mỏng, phô mai mozzarella, tôm, mực, cua thịt, ớt chuông, sốt cà chua Ý',
    khau_phan = 'Đường kính 30 cm - 2 đến 3 người',
    thoi_gian_chuan_bi = '20 - 25 phút'
WHERE id = 4;

UPDATE monan SET
    gia_goc = 99000,
    thanh_phan = 'Ức và đùi gà ướp gia vị 24 giờ, lớp bột chiên giòn, bột ớt cay Hàn Quốc',
    khau_phan = '6 miếng',
    thoi_gian_chuan_bi = '15 - 20 phút'
WHERE id = 6;

UPDATE monan SET
    thanh_phan = 'Cơm tấm, sườn nướng heo than hoa, chả trứng, mỡ hành, nước mắm pha',
    khau_phan = 'Phần cho 1 người',
    thoi_gian_chuan_bi = '15 - 20 phút'
WHERE id = 1;

UPDATE monan SET
    thanh_phan = 'Nước dùng ninh xương bò 12 giờ, bánh phở, thịt bò tái, nạm, gân, hành lá, rau mùi',
    khau_phan = 'Phần cho 1 người',
    thoi_gian_chuan_bi = '10 - 15 phút'
WHERE id = 2;

UPDATE monan SET
    thanh_phan = 'Thịt bò xay 100%, phô mai cheddar, rau xà lách, cà chua, sốt đặc biệt, bánh mì brioche',
    khau_phan = 'Phần cho 1 người',
    thoi_gian_chuan_bi = '15 - 20 phút'
WHERE id = 3;

UPDATE monan SET
    thanh_phan = 'Bún, chả viên và chả miếng nướng than hoa, nước mắm chua ngọt, rau sống, nộm đu đủ',
    khau_phan = 'Phần cho 1 người',
    thoi_gian_chuan_bi = '10 - 15 phút'
WHERE id = 5;

UPDATE monan SET
    thanh_phan = 'Trứng gà, sữa đặc, đường caramel, nước cốt dừa',
    khau_phan = '1 phần (2 cái)',
    thoi_gian_chuan_bi = '5 phút'
WHERE id = 7;

UPDATE monan SET
    thanh_phan = 'Trà đào pha sơ chế, đào miếng, cam sả, mật ong, đá viên',
    khau_phan = '500 ml',
    thoi_gian_chuan_bi = '5 phút'
WHERE id = 8;

-- ---------- SIZE ----------
INSERT INTO monan_size (monan_id, ten_size, gia_tang, mac_dinh, thu_tu, trang_thai) VALUES
(1, 'Tiêu chuẩn',      0,      1, 1, 1),
(1, 'Phần lớn',        12000,  0, 2, 1),
(2, 'Vừa',             0,      1, 1, 1),
(2, 'Lớn',             10000,  0, 2, 1),
(4, 'Vừa 26 cm',       0,      1, 1, 1),
(4, 'Lớn 32 cm',       40000,  0, 2, 1),
(5, 'Tiêu chuẩn',      0,      1, 1, 1),
(5, 'Phần lớn',        10000,  0, 2, 1),
(6, '6 miếng',         0,      1, 1, 1),
(6, '10 miếng',        35000,  0, 2, 1),
(8, 'Vừa (500 ml)',    0,      1, 1, 1),
(8, 'Lớn (700 ml)',    8000,   0, 2, 1);

-- ---------- TOPPING ----------
INSERT INTO monan_topping (monan_id, ten_topping, gia, thu_tu, trang_thai) VALUES
(1, 'Trứng ốp la',      8000,  1, 1),
(1, 'Chả trứng thêm',    10000, 2, 1),
(1, 'Sườn thêm',         25000, 3, 1),
(2, 'Thêm thịt bò',      15000, 1, 1),
(2, 'Thêm gân',          12000, 2, 1),
(2, 'Quẩy',              5000,  3, 1),
(3, 'Thêm thịt patty',   20000, 1, 1),
(3, 'Phô mai thêm',      8000,  2, 1),
(3, 'Bacon',             15000, 3, 1),
(4, 'Thêm phô mai',      15000, 1, 1),
(4, 'Thêm hải sản',      30000, 2, 1),
(4, 'Xúc xích',          20000, 3, 1),
(5, 'Chả thêm',          12000, 1, 1),
(5, 'Nem cua bể',        15000, 2, 1),
(6, 'Sốt phô mai',       10000, 1, 1),
(6, 'Sốt cay',           5000,  2, 1),
(6, 'Khoai tây chiên',   20000, 3, 1),
(7, 'Nước cốt dừa',      5000,  1, 1),
(7, 'Đậu phộng rang',    5000,  2, 1),
(8, 'Trân châu',         7000,  1, 1),
(8, 'Thạch đào',         7000,  2, 1);
