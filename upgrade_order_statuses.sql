-- Run once on an existing database before using the new order progress stages.
ALTER TABLE DONHANG
    MODIFY trang_thai ENUM(
        'cho_xac_nhan',
        'dang_chuan_bi',
        'cho_shipper_lay',
        'dang_giao',
        'hoan_thanh',
        'huy'
    ) NOT NULL DEFAULT 'cho_xac_nhan';
