/**
 * FILE: assets/js/checkout.js
 * - So dia chi: chon dia chi da luu, mo modal them / sua dia chi
 * - Chon dia chi Tinh / Huyen / Xa (cascading) khi dat hang + trong modal
 * - Doc du lieu tu assets/data/vn-address.json (tai 1 lan, cache trong bo nho)
 * - Chuyen doi giua cac che do: "dia chi da luu" / "dia chi moi" / "dia chi cu"
 * - Hien thi truc quan dia chi day du truoc khi dat hang
 */
(function () {
    'use strict';

    var form = document.querySelector('.checkout-form');

    if (!form) {
        return;
    }

    var urlDiaChinh = form.getAttribute('data-dia-chinh') || 'assets/data/vn-address.json';

    /* ===================================================================
       1. TAI DU LIEU DIA CHINH (chi tai 1 lan, dung chung cho form + modal)
       =================================================================== */
    var duLieu  = null;
    var dangTai = null;

    function taiDuLieu() {
        if (duLieu) {
            return Promise.resolve(duLieu);
        }

        if (dangTai) {
            return dangTai;
        }

        dangTai = fetch(urlDiaChinh, { credentials: 'same-origin' })
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

    /* ===================================================================
       2. NHOM CASCADING TINH / HUYEN / XA (dung chung form dat hang + modal)
       =================================================================== */
    function taoCascading(cfg) {
        var selectTinh  = cfg.tinh;
        var selectHuyen = cfg.huyen;
        var selectXa    = cfg.xa;
        var inputSoNha  = cfg.soNha || null;
        var preview     = cfg.preview || null;

        function layTen(select) {
            if (!select || select.selectedIndex < 0) {
                return '';
            }
            return (select.options[select.selectedIndex].textContent || '').trim();
        }

        function capNhatPreview() {
            if (!preview) {
                return;
            }

            var span = preview.querySelector('span') || preview;
            var cacPhan = [
                inputSoNha ? inputSoNha.value.trim() : '',
                layTen(selectXa),
                layTen(selectHuyen),
                layTen(selectTinh)
            ].filter(function (phan) {
                return phan !== '' && phan.indexOf('-- Chọn') !== 0;
            });

            if (cacPhan.length === 0) {
                span.textContent = 'Chọn tỉnh / thành, quận / huyện, phường / xã để xem địa chỉ.';
                return;
            }

            span.textContent = cacPhan.join(', ');
        }

        function sangTinh() {
            datSelect(selectHuyen, [], '', '-- Chọn quận / huyện --');
            datSelect(selectXa, [], '', '-- Chọn phường / xã --');
            capNhatPreview();
        }

        function sangHuyen() {
            datSelect(selectXa, [], '', '-- Chọn phường / xã --');
            capNhatPreview();
        }

        if (selectTinh) {
            selectTinh.addEventListener('change', function () {
                var idTinh = selectTinh.value;

                if (!idTinh) {
                    sangTinh();
                    return;
                }

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
                    capNhatPreview();
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
                    capNhatPreview();
                }).catch(function () {
                    sangHuyen();
                });
            });
        }

        if (selectXa) {
            selectXa.addEventListener('change', capNhatPreview);
        }

        if (inputSoNha) {
            inputSoNha.addEventListener('input', capNhatPreview);
        }

        /**
         * Nap truc tiep 1 dia chi co san (dung khi sua dia chi trong modal).
         */
        function nap(diaChi, khiXong) {
            datSelect(selectHuyen, [], '', '-- Chọn quận / huyện --');
            datSelect(selectXa, [], '', '-- Chọn phường / xã --');

            if (!diaChi || !diaChi.tinh_id) {
                if (inputSoNha) {
                    inputSoNha.value = diaChi && diaChi.so_nha ? diaChi.so_nha : '';
                }
                capNhatPreview();
                if (khiXong) { khiXong(); }
                return;
            }

            datSelect(selectTinh, [], '', '-- Chọn tỉnh / thành --');
            taiDuLieu().then(function () {
                datSelect(selectTinh, duLieu.map(function (t) {
                    return { id: t.level1_id, name: t.name };
                }), diaChi.tinh_id, '-- Chọn tỉnh / thành --');
                selectTinh.disabled = false;

                var tinh = timTinh(diaChi.tinh_id);
                var dsHuyen = tinh ? (tinh.level2s || []).map(function (h) {
                    return { id: h.level2_id, name: h.name };
                }) : [];

                datSelect(selectHuyen, dsHuyen, diaChi.huyen_id, '-- Chọn quận / huyện --');

                var huyen = timHuyen(tinh, diaChi.huyen_id);
                var dsXa = huyen ? (huyen.level3s || []).map(function (x) {
                    return { id: x.level3_id, name: x.name };
                }) : [];

                datSelect(selectXa, dsXa, diaChi.xa_id, '-- Chọn phường / xã --');

                if (inputSoNha) {
                    inputSoNha.value = diaChi.so_nha || '';
                }

                capNhatPreview();
                if (khiXong) { khiXong(); }
            }).catch(function () {
                if (khiXong) { khiXong(); }
            });
        }

        capNhatPreview();

        return { nap: nap, capNhatPreview: capNhatPreview };
    }

    /* ===================================================================
       3. CASCADING CUA FORM DAT HANG
       =================================================================== */
    var panelMoi    = document.getElementById('addrPanelMoi');
    var panelCu     = document.getElementById('addrPanelCu');
    var inputSoNha  = document.getElementById('so_nha');
    var preview     = document.getElementById('addrPreview');

    var cascadingForm = taoCascading({
        tinh:   document.getElementById('tinh_id'),
        huyen:  document.getElementById('huyen_id'),
        xa:     document.getElementById('xa_id'),
        soNha:  inputSoNha,
        preview: preview
    });

    /* ===================================================================
       4. SO DIA CHI: chon dia chi / chuyen che do
       =================================================================== */
    var book         = document.getElementById('addrBook');
    var modal        = document.getElementById('addrModal');
    var modalTitle   = document.getElementById('addrModalTitle');
    var addrForm     = document.getElementById('addrForm');
    var addrFormId   = document.getElementById('addrFormId');
    var panelChon    = document.getElementById('addrPanelChon');
    var panelText    = document.getElementById('addrPanelText');

    var cascadingModal = taoCascading({
        tinh:   document.getElementById('m_tinh_id'),
        huyen:  document.getElementById('m_huyen_id'),
        xa:     document.getElementById('m_xa_id'),
        soNha:  document.getElementById('m_so_nha'),
        preview: document.getElementById('m_addrPreview')
    });

    var dsDiaChi = [];

    if (book) {
        try {
            dsDiaChi = JSON.parse(book.getAttribute('data-addresses') || '[]');
        } catch (e) {
            dsDiaChi = [];
        }
    }

    function radioDangChon() {
        return form.querySelector('input[name="che_do_dia_chi"]:checked');
    }

    function laDiaChiDaLuu(radio) {
        return !!radio && radio.value.indexOf('da_luu') === 0;
    }

    function nhomO(idO) {
        var o = document.getElementById(idO);
        return o ? o.closest('.form-group') : null;
    }

    // Chuyen doi trang thai khi khach chon dia chi da luu / nhap dia chi moi
    function capNhatCheDoDiaChi() {
        var radio  = radioDangChon();
        var daLuu  = laDiaChiDaLuu(radio);

        // O ten / so dien thoai: lay tu so dia chi khi dang chon dia chi da luu
        var nhomTen = nhomO('ho_ten');
        var nhomSdt = nhomO('so_dien_thoai');

        if (nhomTen) { nhomTen.hidden = daLuu; }
        if (nhomSdt) { nhomSdt.hidden = daLuu; }

        // Checkbox "Luu vao so dia chi" (chi co tren form dat hang)
        var saveBox = document.getElementById('addrSaveBox');
        if (saveBox) { saveBox.hidden = daLuu; }

        if (daLuu) {
            if (panelMoi) { panelMoi.hidden = true; }
            if (panelCu)  { panelCu.hidden = true; }
        } else {
            chuyenCheDo(radio ? radio.value : 'moi');
        }

        // The doi the nao dia chi duoc chon
        if (book) {
            book.querySelectorAll('.addr-card').forEach(function (card) {
                card.classList.toggle('is-active', daLuu && card.contains(radio));
            });
        }
    }

    // Chuyen doi giua 2 che do nhap: "dia chi moi" (chon) / "dia chi cu" (go tay)
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
            cascadingForm.capNhatPreview();
        }
    }

    form.addEventListener('change', function (event) {
        if (event.target && event.target.name === 'che_do_dia_chi') {
            capNhatCheDoDiaChi();
        }
    });

    /* ===================================================================
       5. MODAL THEM / SUA DIA CHI
       =================================================================== */
    function dongModal() {
        if (!modal) { return; }
        modal.hidden = true;
        document.body.classList.remove('addr-modal-open');
    }

    function moModal() {
        if (!modal) { return; }
        modal.hidden = false;
        document.body.classList.add('addr-modal-open');
        var first = document.getElementById('m_ten');
        if (first) { first.focus(); }
    }

    function chuyenCheDoModal(cheDo) {
        if (panelChon) { panelChon.hidden = cheDo !== 'chon'; }
        if (panelText) { panelText.hidden = cheDo !== 'text'; }
    }

    if (modal) {
        modal.addEventListener('change', function (event) {
            if (event.target && event.target.name === 'dia_chi_che_do') {
                chuyenCheDoModal(event.target.value);
            }
        });

        modal.addEventListener('click', function (event) {
            if (event.target.closest('[data-addr-close]')) {
                dongModal();
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && !modal.hidden) {
                dongModal();
            }
        });
    }

    // Nut "Them dia chi moi"
    var nutThem = document.getElementById('addrAddBtn');

    if (nutThem && addrForm) {
        nutThem.addEventListener('click', function () {
            addrForm.reset();
            if (addrFormId) { addrFormId.value = ''; }
            if (modalTitle) { modalTitle.textContent = 'Thêm địa chỉ mới'; }

            var radioChon = addrForm.querySelector('input[name="dia_chi_che_do"][value="chon"]');
            if (radioChon) { radioChon.checked = true; }
            chuyenCheDoModal('chon');

            // Xoa ket qua cu tren cascading modal
            var tinhM  = document.getElementById('m_tinh_id');
            var huyenM = document.getElementById('m_huyen_id');
            var xaM    = document.getElementById('m_xa_id');
            var soNhaM = document.getElementById('m_so_nha');

            if (tinhM)  { tinhM.value = ''; }
            if (huyenM) { datSelect(huyenM, [], '', '-- Chọn quận / huyện --'); }
            if (xaM)    { datSelect(xaM, [], '', '-- Chọn phường / xã --'); }
            if (soNhaM) { soNhaM.value = ''; }
            cascadingModal.capNhatPreview();

            moModal();
        });
    }

    // Nut "Sua" tren tung dia chi
    document.querySelectorAll('.addr-btn-edit').forEach(function (button) {
        button.addEventListener('click', function () {
            if (!addrForm) { return; }

            var id    = parseInt(button.getAttribute('data-addr-id'), 10);
            var diaChi = null;

            dsDiaChi.forEach(function (dc) {
                if (parseInt(dc.id, 10) === id) {
                    diaChi = dc;
                }
            });

            if (!diaChi) { return; }

            addrForm.reset();
            if (addrFormId) { addrFormId.value = String(diaChi.id); }
            if (modalTitle) { modalTitle.textContent = 'Sửa địa chỉ'; }

            document.getElementById('m_ten').value = diaChi.ten_nguoi_nhan || '';
            document.getElementById('m_sdt').value = diaChi.so_dien_thoai || '';
            document.getElementById('m_ghi_chu').value = diaChi.ghi_chu || '';

            var chkMacDinh = addrForm.querySelector('input[name="mac_dinh"]');
            if (chkMacDinh) { chkMacDinh.checked = String(diaChi.mac_dinh) === '1'; }

            if (diaChi.tinh_id) {
                // Co ma dia chinh -> chon lai Tinh / Huyen / Xa
                var radioChon = addrForm.querySelector('input[name="dia_chi_che_do"][value="chon"]');
                if (radioChon) { radioChon.checked = true; }
                chuyenCheDoModal('chon');
                cascadingModal.nap(diaChi);
            } else {
                // Dia chi cu (chi co chuoi day du) -> che do go tay
                var radioText = addrForm.querySelector('input[name="dia_chi_che_do"][value="text"]');
                if (radioText) { radioText.checked = true; }
                chuyenCheDoModal('text');
                document.getElementById('m_dia_chi_text').value = diaChi.dia_chi_chi_tiet || '';
            }

            moModal();
        });
    });

    // Kiem tra truoc khi gui form dia chi (neu dang o che do chon T/H/X)
    if (addrForm) {
        addrForm.addEventListener('submit', function (event) {
            var radioChon = addrForm.querySelector('input[name="dia_chi_che_do"][value="chon"]');

            if (radioChon && radioChon.checked) {
                var tinh  = document.getElementById('m_tinh_id');
                var huyen = document.getElementById('m_huyen_id');
                var xa    = document.getElementById('m_xa_id');
                var soNha = document.getElementById('m_so_nha');
                var loi   = !tinh || !tinh.value
                    ? 'vui lòng chọn Tỉnh / Thành phố'
                    : (!huyen || !huyen.value
                        ? 'vui lòng chọn Quận / Huyện'
                        : (!xa || !xa.value
                            ? 'vui lòng chọn Phường / Xã'
                            : (!soNha || !soNha.value.trim() ? 'vui lòng nhập số nhà, đường' : '')));

                if (loi) {
                    event.preventDefault();
                    window.alert('Địa chỉ giao hàng ' + loi + '.');
                }
            }
        });
    }

    /* ===================================================================
       6. NGAN KHONG CHO SUBMIT KHI DIA CHINH CHUA DU (che do "moi")
       =================================================================== */
    form.addEventListener('submit', function (event) {
        var radioMoi = form.querySelector('input[name="che_do_dia_chi"][value="moi"]');

        if (!radioMoi || !radioMoi.checked) {
            return; // che do "da luu" / "cu" -> PHP se kiem tra
        }

        var selectTinh  = document.getElementById('tinh_id');
        var selectHuyen = document.getElementById('huyen_id');
        var selectXa    = document.getElementById('xa_id');

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

    /* ===================================================================
       7. KHOI TAO
       =================================================================== */
    chuyenCheDo(
        (form.querySelector('input[name="che_do_dia_chi"]:checked') || {}).value || 'moi'
    );
    capNhatCheDoDiaChi();
    cascadingForm.capNhatPreview();
})();
