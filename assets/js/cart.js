/* =====================================================================
   FILE: assets/js/cart.js
   Xu ly trang GIO HANG:
   - Tang / giam so luong, nhap so luong
   - Tu dong cap nhat thanh tien tung mon + tong gio hang
   - Xoa mon, cap nhat badge so luong o header
   - Gui du lieu len PHP bang fetch (co the hoat dong khong can JS)
   ===================================================================== */
(function () {
    'use strict';

    var cartPage = document.getElementById('cartPage');
    if (!cartPage) {
        return;
    }

    var shipFee = parseInt(cartPage.getAttribute('data-ship-fee'), 10) || 0;
    var freeFrom = parseInt(cartPage.getAttribute('data-free-from'), 10) || 0;
    var coFetch = typeof window.fetch === 'function';
    var toastTimer = null;

    /* -------------------- Cac ham tro giup -------------------- */

    // Dinh dang tien: 45000 -> 45.000đ
    function dinhDangTien(soTien) {
        return Math.round(soTien).toLocaleString('vi-VN') + 'đ';
    }

    // Hien thi thong bao (dung chung element .cart-toast cua trang chu)
    function thongBao(message, loi) {
        var toast = document.querySelector('.cart-toast');

        if (!toast) {
            toast = document.createElement('div');
            toast.className = 'cart-toast';
            document.body.appendChild(toast);
        }

        toast.innerHTML = '<i class="bi ' + (loi ? 'bi-exclamation-circle-fill' : 'bi-check-circle-fill') + '"></i><span></span>';
        toast.querySelector('span').textContent = message;
        toast.classList.add('is-visible');

        clearTimeout(toastTimer);
        toastTimer = setTimeout(function () {
            toast.classList.remove('is-visible');
        }, 2400);
    }

    // Cap nhat so luong tren bieu tuong gio hang o header
    function capNhatBadge(soLuong) {
        document.querySelectorAll('.cart-count').forEach(function (el) {
            el.textContent = soLuong;
        });
    }

    // Doc 1 dong gio hang
    function layDong(cartItem) {
        var input = cartItem.querySelector('.js-qty-input');

        return {
            el: cartItem,
            id: parseInt(cartItem.getAttribute('data-id'), 10),
            gia: parseFloat(cartItem.getAttribute('data-price')) || 0,
            input: input,
            soLuong: input ? (parseInt(input.value, 10) || 1) : 1,
            tonKho: input ? (parseInt(input.getAttribute('max'), 10) || 999) : 999
        };
    }

    // Doc toan bo gio hang
    function layGioHang() {
        return Array.prototype.slice.call(cartPage.querySelectorAll('.cart-item')).map(layDong);
    }

    // Tu dong tinh lai toan bo so tien tren trang
    function capNhatTong() {
        var tongTienHang = 0;
        var tongSoLuong = 0;
        var soMon = 0;

        layGioHang().forEach(function (dong) {
            var thanhTien = dong.gia * dong.soLuong;
            var elTien = dong.el.querySelector('.js-line-total');

            if (elTien) {
                elTien.textContent = dinhDangTien(thanhTien);
            }

            tongTienHang += thanhTien;
            tongSoLuong += dong.soLuong;
            soMon += 1;
        });

        // Phi giao hang: mien phi tu muc qui dinh
        var phiGiaoHang = (tongTienHang > 0 && tongTienHang < freeFrom) ? shipFee : 0;
        var tongThanhToan = tongTienHang + phiGiaoHang;

        setText('.js-summary-subtotal', dinhDangTien(tongTienHang));
        setText('.js-summary-ship', phiGiaoHang > 0 ? dinhDangTien(phiGiaoHang) : 'Miễn phí');
        setText('.js-summary-total', dinhDangTien(tongThanhToan));
        setText('.js-summary-qty', tongSoLuong + ' phần');

        return {
            soMon: soMon,
            tongSoLuong: tongSoLuong,
            tongTienHang: tongTienHang,
            phiGiaoHang: phiGiaoHang,
            tongThanhToan: tongThanhToan
        };
    }

    function setText(selector, noiDung) {
        var el = document.querySelector(selector);
        if (el) {
            el.textContent = noiDung;
        }
    }

    // Gui form len PHP, tra ve Promise JSON
    function guiForm(form, duLieu) {
        return fetch(form.getAttribute('action'), {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
            },
            body: new URLSearchParams(duLieu).toString()
        }).then(function (res) {
            return res.json();
        });
    }

    // Xu ly ket qua sau moi lan cap nhat so luong
    function xuLyKetQua(res, dong) {
        if (!res) {
            return;
        }

        capNhatBadge(typeof res.cart_count === 'number' ? res.cart_count : capNhatTong().tongSoLuong);

        // Server co the giam so luong (vuot ton kho)
        if (res.so_luong && dong && res.so_luong !== dong.soLuong) {
            dong.input.value = res.so_luong;
            capNhatTong();
        }

        if (res.message) {
            thongBao(res.message, !res.success);
        }
    }

    /* -------------------- Tang / giam / nhap so luong -------------------- */

    cartPage.addEventListener('click', function (event) {
        var nutTang = event.target.closest('.js-qty-plus');
        var nutGiam = event.target.closest('.js-qty-minus');

        if (!nutTang && !nutGiam) {
            return;
        }

        // Khong co JavaScript thi cho form submit binh thuong
        if (!coFetch) {
            return;
        }

        event.preventDefault();

        var form = (nutTang || nutGiam).closest('form');
        if (!form) {
            return;
        }

        var dong = layDong(form.closest('.cart-item'));
        var moi = nutTang ? dong.soLuong + 1 : dong.soLuong - 1;

        // Khong cho phep so luong nho hon 1
        if (moi < 1) {
            moi = 1;
        }

        // Khong vuot ton kho
        if (moi > dong.tonKho) {
            moi = dong.tonKho;
            thongBao('Chỉ còn ' + dong.tonKho + ' phần món này', true);
        }

        if (moi === dong.soLuong) {
            return;
        }

        dong.input.value = moi;
        dong.soLuong = moi;
        capNhatTong();

        guiForm(form, {
            id: dong.id,
            so_luong: moi,
            huong: ''
        }).then(function (res) {
            xuLyKetQua(res, dong);
        }).catch(function () {
            // Co loi thi tai lai trang cho du lieu dung
            window.location.reload();
        });
    });

    // Nhap truc tiep so luong
    cartPage.addEventListener('change', function (event) {
        var input = event.target.closest('.js-qty-input');
        if (!input) {
            return;
        }

        var form = input.closest('form');
        var dong = layDong(form.closest('.cart-item'));

        var moi = parseInt(input.value, 10);
        if (isNaN(moi) || moi < 1) {
            moi = 1;
        }
        if (moi > dong.tonKho) {
            moi = dong.tonKho;
        }

        input.value = moi;
        dong.soLuong = moi;
        capNhatTong();

        if (!coFetch) {
            return;
        }

        guiForm(form, {
            id: dong.id,
            so_luong: moi,
            huong: ''
        }).then(function (res) {
            xuLyKetQua(res, dong);
        }).catch(function () {
            window.location.reload();
        });
    });

    // Nut "Cap nhat" (truong hop khong dung nut + / -)
    cartPage.addEventListener('submit', function (event) {
        var form = event.target.closest('.js-qty-form');
        if (!form || !coFetch) {
            return;
        }

        // Nut + / - da duoc xu ly o su kien click
        if (event.submitter && (event.submitter.classList.contains('js-qty-plus')
            || event.submitter.classList.contains('js-qty-minus'))) {
            event.preventDefault();
            return;
        }

        event.preventDefault();

        var dong = layDong(form.closest('.cart-item'));

        guiForm(form, {
            id: dong.id,
            so_luong: dong.soLuong,
            huong: ''
        }).then(function (res) {
            xuLyKetQua(res, dong);
        }).catch(function () {
            form.submit();
        });
    });

    /* -------------------- Xoa mon khoi gio hang -------------------- */

    cartPage.addEventListener('submit', function (event) {
        var form = event.target.closest('.js-remove-form');
        if (!form || !coFetch) {
            return;
        }

        event.preventDefault();

        var id = form.querySelector('input[name="id"]').value;
        var cartItem = form.closest('.cart-item');

        guiForm(form, { id: id }).then(function (res) {
            if (!res || !res.success) {
                thongBao((res && res.message) || 'Không xóa được sản phẩm', true);
                return;
            }

            cartItem.classList.add('is-removing');

            setTimeout(function () {
                cartItem.remove();

                var tong = capNhatTong();

                if (typeof res.cart_count === 'number') {
                    capNhatBadge(res.cart_count);
                } else {
                    capNhatBadge(tong.tongSoLuong);
                }

                thongBao(res.message || 'Đã xóa sản phẩm khỏi giỏ hàng');

                // Gio hang rong thi tai lai de hien thi man hinh gio hang trong
                if (tong.soMon === 0) {
                    window.location.reload();
                }
            }, 250);
        }).catch(function () {
            form.submit();
        });
    });

    // Tinh tien ngay khi vao trang
    capNhatTong();
})();
