/**
 * FILE: assets/js/checkout.js
 * - Chon dia chi Tinh / Huyen / Xa (cascading) khi dat hang
 * - Doc du lieu tu assets/data/vn-address.json (tai 1 lan, cache trong bo nho)
 * - Chuyen doi giua 2 che do: "dia chi moi" (chon) va "dia chi cu" (go tay)
 * - Hien thi truc quan dia chi day du truoc khi dat hang
 */
(function () {
    'use strict';

    var form = document.querySelector('.checkout-form');

    if (!form) {
        return;
    }

    var panelMoi = document.getElementById('addrPanelMoi');
    var panelCu  = document.getElementById('addrPanelCu');
    var selectTinh  = document.getElementById('tinh_id');
    var selectHuyen = document.getElementById('huyen_id');
    var selectXa    = document.getElementById('xa_id');
    var inputSoNha  = document.getElementById('so_nha');
    var preview     = document.getElementById('addrPreview');

    var urlDuLieu = form.getAttribute('data-dia-chinh') || 'assets/data/vn-address.json';
    var duLieu    = null;
    var dangTai   = null;

    // ------------------------------------------------------------------
    // 1. TAI DU LIEU DIA CHINH (chi tai 1 lan)
    // ------------------------------------------------------------------
    function taiDuLieu() {
        if (duLieu) {
            return Promise.resolve(duLieu);
        }

        if (dangTai) {
            return dangTai;
        }

        dangTai = fetch(urlDuLieu, { credentials: 'same-origin' })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('HTTP ' + response.status);
                }
                return response.json();
            })
            .then(function (data) {
                duLieu = Array.isArray(data) ? data : [];
                return duLieu;
            })
            .catch(function (error) {
                dangTai = null;
                console.error('Không tải được dữ liệu địa chính.', error);
                throw error;
            });

        return dangTai;
    }

    // ------------------------------------------------------------------
    // 2. TIM KIEM
    // ------------------------------------------------------------------
    function timTinh(id) {
        for (var i = 0; i < duLieu.length; i++) {
            if (String(duLieu[i].level1_id) === String(id)) {
                return duLieu[i];
            }
        }
        return null;
    }

    function timHuyen(tinh, id) {
        var ds = (tinh && tinh.level2s) || [];

        for (var i = 0; i < ds.length; i++) {
            if (String(ds[i].level2_id) === String(id)) {
                return ds[i];
            }
        }
        return null;
    }

    // ------------------------------------------------------------------
    // 3. DUA DU LIEU VAO SELECT
    // ------------------------------------------------------------------
    function datSelect(select, ds, giaTriChon, tieuDe) {
        if (!select) {
            return;
        }

        select.innerHTML = '';

        var optionDau = document.createElement('option');
        optionDau.value = '';
        optionDau.textContent = tieuDe;
        select.appendChild(optionDau);

        (ds || []).forEach(function (item) {
            var option = document.createElement('option');
            option.value = String(item.id);
            option.textContent = item.name;

            if (giaTriChon && String(giaTriChon) === String(item.id)) {
                option.selected = true;
            }

            select.appendChild(option);
        });

        select.disabled = !ds || ds.length === 0;
    }

    function sangTinh() {
        datSelect(selectHuyen, [], '', '-- Chọn quận / huyện --');
        datSelect(selectXa, [], '', '-- Chọn phường / xã --');
        capNhatDiaChiDayDu();
    }

    function sangHuyen() {
        datSelect(selectXa, [], '', '-- Chọn phường / xã --');
        capNhatDiaChiDayDu();
    }

    // ------------------------------------------------------------------
    // 4. HIEN THI DIA CHI DAY DU
    // ------------------------------------------------------------------
    function layTen(select) {
        if (!select || select.selectedIndex < 0) {
            return '';
        }
        return (select.options[select.selectedIndex].textContent || '').trim();
    }

    function capNhatDiaChiDayDu() {
        if (!preview) {
            return;
        }

        var span = preview.querySelector('span') || preview;
        var soNha  = inputSoNha ? inputSoNha.value.trim() : '';
        var tenXa  = layTen(selectXa);
        var tenHuyen = layTen(selectHuyen);
        var tenTinh  = layTen(selectTinh);

        var cacPhan = [soNha, tenXa, tenHuyen, tenTinh].filter(function (phan) {
            return phan !== '' && phan.indexOf('-- Chọn') !== 0;
        });

        if (cacPhan.length === 0) {
            span.textContent = 'Chọn tỉnh / thành, quận / huyện, phường / xã để xem địa chỉ.';
            return;
        }

        span.textContent = cacPhan.join(', ');
    }

    // ------------------------------------------------------------------
    // 5. CHUYEN DOI CHE DO "MOI" / "CU"
    // ------------------------------------------------------------------
    function chuyenCheDo(cheDo) {
        var laMoi = cheDo !== 'cu';

        if (panelMoi) {
            panelMoi.hidden = !laMoi;
        }
        if (panelCu) {
            panelCu.hidden = laMoi;
        }

        // Khi go dia chi cu thi bo required o so nha de khong chan submit
        if (inputSoNha) {
            inputSoNha.required = false;
        }

        if (laMoi) {
            capNhatDiaChiDayDu();
        }
    }

    Array.prototype.forEach.call(
        form.querySelectorAll('input[name="che_do_dia_chi"]'),
        function (radio) {
            radio.addEventListener('change', function () {
                if (radio.checked) {
                    chuyenCheDo(radio.value);
                }
            });
        }
    );

    // ------------------------------------------------------------------
    // 6. SU KIEN CASCADING
    // ------------------------------------------------------------------
    if (selectTinh) {
        selectTinh.addEventListener('change', function () {
            var idTinh = selectTinh.value;

            if (!idTinh) {
                sangTinh();
                return;
            }

            // Dua select huyen ve trang thai dang tai
            datSelect(selectHuyen, [], '', 'Đang tải...');
            selectHuyen.disabled = true;
            sangHuyen();

            taiDuLieu().then(function () {
                var tinh = timTinh(idTinh);

                if (!tinh) {
                    sangTinh();
                    return;
                }

                var dsHuyen = (tinh.level2s || []).map(function (huyen) {
                    return { id: huyen.level2_id, name: huyen.name };
                });

                datSelect(selectHuyen, dsHuyen, '', '-- Chọn quận / huyện --');
                capNhatDiaChiDayDu();
            }).catch(function () {
                sangTinh();
            });
        });
    }

    if (selectHuyen) {
        selectHuyen.addEventListener('change', function () {
            var idTinh  = selectTinh ? selectTinh.value : '';
            var idHuyen = selectHuyen.value;

            if (!idHuyen) {
                sangHuyen();
                return;
            }

            datSelect(selectXa, [], '', 'Đang tải...');
            selectXa.disabled = true;

            taiDuLieu().then(function () {
                var tinh  = timTinh(idTinh);
                var huyen = timHuyen(tinh, idHuyen);

                if (!huyen) {
                    sangHuyen();
                    return;
                }

                var dsXa = (huyen.level3s || []).map(function (xa) {
                    return { id: xa.level3_id, name: xa.name };
                });

                datSelect(selectXa, dsXa, '', '-- Chọn phường / xã --');
                capNhatDiaChiDayDu();
            }).catch(function () {
                sangHuyen();
            });
        });
    }

    if (selectXa) {
        selectXa.addEventListener('change', capNhatDiaChiDayDu);
    }

    if (inputSoNha) {
        inputSoNha.addEventListener('input', capNhatDiaChiDayDu);
    }

    // ------------------------------------------------------------------
    // 7. KHONG CHO SUBMIT KHI DIA CHINH CHUA DU (chon che do "moi")
    // ------------------------------------------------------------------
    form.addEventListener('submit', function (event) {
        var radioMoi = form.querySelector('input[name="che_do_dia_chi"][value="moi"]');

        if (!radioMoi || !radioMoi.checked) {
            return; // che do "cu" -> PHP se kiem tra
        }

        if (!selectTinh || !selectHuyen || !selectXa) {
            return;
        }

        if (!selectTinh.value || !selectHuyen.value || !selectXa.value) {
            event.preventDefault();

            var thieu = !selectTinh.value
                ? 'vui lòng chọn Tỉnh / Thành phố'
                : (!selectHuyen.value
                    ? 'vui lòng chọn Quận / Huyện'
                    : 'vui lòng chọn Phường / Xã');

            var oLoi = !selectTinh.value ? selectTinh : (!selectHuyen.value ? selectHuyen : selectXa);

            oLoi.focus();
            oLoi.classList.add('is-invalid');

            if (typeof window.showCheckoutError === 'function') {
                window.showCheckoutError('Địa chỉ giao hàng ' + thieu + '.');
            } else {
                window.alert('Địa chỉ giao hàng ' + thieu + '.');
            }
        }
    });

    // ------------------------------------------------------------------
    // 8. KHOI TAO
    // ------------------------------------------------------------------
    chuyenCheDo(
        (form.querySelector('input[name="che_do_dia_chi"]:checked') || {}).value || 'moi'
    );
    capNhatDiaChiDayDu();
})();
