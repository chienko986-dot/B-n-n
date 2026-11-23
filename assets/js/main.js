(function () {
    'use strict';

    var header = document.getElementById('siteHeader');
    var backToTop = document.getElementById('backToTop');
    var cartCountEl = document.querySelector('.cart-count');
    var cartItemsEl = document.getElementById('cartItems');
    var cartEmptyEl = document.getElementById('cartEmpty');
    var cartSummaryEl = document.getElementById('cartSummary');
    var cartClearButton = document.getElementById('cartClear');
    var cartShippingMessage = document.getElementById('cartShippingMessage');
    var cartShippingProgress = document.getElementById('cartShippingProgress');
    var cartSubtotalEl = document.getElementById('cartSubtotal');
    var cartDeliveryEl = document.getElementById('cartDelivery');
    var cartTotalEl = document.getElementById('cartTotal');
    var freeShippingThreshold = 99000;
    var deliveryFee = 15000;
    var cart = [];
    var savedCart = localStorage.getItem('anlame-cart');

    if (savedCart) {
        try {
            var parsedCart = JSON.parse(savedCart);
            if (Array.isArray(parsedCart)) {
                cart = parsedCart.filter(function (item) {
                    return item && typeof item.name === 'string' &&
                        Number.isFinite(Number(item.price)) && Number(item.price) > 0 &&
                        Number.isInteger(Number(item.quantity)) && Number(item.quantity) > 0;
                }).map(function (item) {
                    return {
                        name: item.name,
                        price: Number(item.price),
                        image: typeof item.image === 'string' ? item.image : '',
                        quantity: Number(item.quantity)
                    };
                });
            }
        } catch (error) {
            console.warn('Không thể đọc giỏ hàng đã lưu; giỏ hàng sẽ được làm mới.', error);
        }
    }

    function formatPrice(price) {
        return new Intl.NumberFormat('vi-VN').format(price) + 'đ';
    }

    function saveCart() {
        localStorage.setItem('anlame-cart', JSON.stringify(cart));
    }

    function renderCart() {
        if (!cartItemsEl) {
            return;
        }

        var itemCount = cart.reduce(function (total, item) {
            return total + item.quantity;
        }, 0);
        var subtotal = cart.reduce(function (total, item) {
            return total + item.price * item.quantity;
        }, 0);
        var hasItems = cart.length > 0;

        cartCountEl.textContent = itemCount;
        cartEmptyEl.hidden = hasItems;
        cartSummaryEl.hidden = !hasItems;
        cartClearButton.hidden = !hasItems;
        cartItemsEl.replaceChildren();

        cart.forEach(function (item, index) {
            var row = document.createElement('article');
            row.className = 'cart-item';

            var image = document.createElement('img');
            image.className = 'cart-item-image';
            image.src = item.image;
            image.alt = '';
            image.loading = 'lazy';
            image.onerror = function () {
                image.classList.add('is-missing');
                image.removeAttribute('src');
            };

            var details = document.createElement('div');
            details.className = 'cart-item-details';
            var name = document.createElement('h3');
            name.className = 'cart-item-name';
            name.textContent = item.name;
            var price = document.createElement('strong');
            price.className = 'cart-item-price';
            price.textContent = formatPrice(item.price);

            var controls = document.createElement('div');
            controls.className = 'cart-quantity';
            var decrease = document.createElement('button');
            decrease.type = 'button';
            decrease.setAttribute('aria-label', 'Giảm số lượng ' + item.name);
            decrease.innerHTML = '<i class="bi bi-dash"></i>';
            decrease.addEventListener('click', function () {
                updateQuantity(index, -1);
            });
            var quantity = document.createElement('span');
            quantity.textContent = item.quantity;
            var increase = document.createElement('button');
            increase.type = 'button';
            increase.setAttribute('aria-label', 'Tăng số lượng ' + item.name);
            increase.innerHTML = '<i class="bi bi-plus"></i>';
            increase.addEventListener('click', function () {
                updateQuantity(index, 1);
            });
            controls.append(decrease, quantity, increase);

            var actions = document.createElement('div');
            actions.className = 'cart-item-actions';
            var remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'cart-item-remove';
            remove.setAttribute('aria-label', 'Xóa ' + item.name + ' khỏi giỏ hàng');
            remove.innerHTML = '<i class="bi bi-trash3"></i><span>Xóa</span>';
            remove.addEventListener('click', function () {
                removeItem(index);
            });
            actions.append(controls, remove);

            details.append(name, price, actions);
            row.append(image, details);
            cartItemsEl.appendChild(row);
        });

        var delivery = subtotal >= freeShippingThreshold || !hasItems ? 0 : deliveryFee;
        var remaining = Math.max(0, freeShippingThreshold - subtotal);
        cartSubtotalEl.textContent = formatPrice(subtotal);
        cartDeliveryEl.textContent = delivery ? formatPrice(delivery) : 'Miễn phí';
        cartTotalEl.textContent = formatPrice(subtotal + delivery);
        cartShippingMessage.textContent = remaining > 0
            ? 'Mua thêm ' + formatPrice(remaining) + ' để được miễn phí giao hàng'
            : 'Bạn được miễn phí giao hàng!';
        cartShippingProgress.style.width = Math.min(100, subtotal / freeShippingThreshold * 100) + '%';
        cartShippingProgress.parentElement.setAttribute('aria-valuenow',
            Math.round(Math.min(100, subtotal / freeShippingThreshold * 100)));
    }

    function updateQuantity(index, change) {
        cart[index].quantity += change;
        if (cart[index].quantity <= 0) {
            cart.splice(index, 1);
        }
        saveCart();
        renderCart();
    }

    function removeItem(index) {
        cart.splice(index, 1);
        saveCart();
        renderCart();
    }

    var cartDrawer = document.getElementById('cartDrawer');
    document.querySelectorAll('[data-cart-navigation]').forEach(function (link) {
        link.addEventListener('click', function (event) {
            var target = link.getAttribute('href');
            var targetSection = target && target.charAt(0) === '#'
                ? document.getElementById(target.slice(1))
                : null;

            if (!cartDrawer || !targetSection || !window.bootstrap || !window.bootstrap.Offcanvas) {
                return;
            }

            event.preventDefault();
            cartDrawer.addEventListener('hidden.bs.offcanvas', function () {
                window.history.pushState(null, '', target);
                targetSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, { once: true });
            window.bootstrap.Offcanvas.getOrCreateInstance(cartDrawer).hide();
        });
    });

    function addToCart(button) {
        var name = button.getAttribute('data-dish') || 'Món ăn';
        var price = Number(button.getAttribute('data-price'));
        var image = button.getAttribute('data-image') || '';
        var existingItem = cart.find(function (item) {
            return item.name === name;
        });

        if (!Number.isFinite(price) || price <= 0) {
            console.error('Không thể thêm món vào giỏ hàng: giá món không hợp lệ.');
            return;
        }

        if (existingItem) {
            existingItem.quantity += 1;
        } else {
            cart.push({ name: name, price: price, image: image, quantity: 1 });
        }

        saveCart();
        renderCart();
        showToast('Đã thêm "' + name + '" vào giỏ hàng');
    }

    function onScroll() {
        var y = window.scrollY || window.pageYOffset;

        if (header) {
            header.classList.toggle('is-scrolled', y > 8);
        }
        if (backToTop) {
            backToTop.classList.toggle('is-visible', y > 400);
        }
    }

    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();

    if (backToTop) {
        backToTop.addEventListener('click', function () {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    var toast = document.createElement('div');
    toast.className = 'cart-toast';
    document.body.appendChild(toast);
    var toastTimer = null;

    function showToast(message) {
        toast.innerHTML = '<i class="bi bi-check-circle-fill"></i><span></span>';
        toast.querySelector('span').textContent = message;
        toast.classList.add('is-visible');

        clearTimeout(toastTimer);
        toastTimer = setTimeout(function () {
            toast.classList.remove('is-visible');
        }, 2200);
    }

    // ---- Trang chi tiet: doc lua chon size / topping / ghi chu ----
    function layLuaChonTuyChinh(coTuyChinh) {
        var params = new URLSearchParams();

        if (!coTuyChinh) {
            return params;
        }

        var box = document.getElementById('dishBuyBox');
        if (!box) {
            return params;
        }

        var size = box.querySelector('input[name="size_id"]:checked');
        if (size) {
            params.set('size_id', size.value);
        }

        box.querySelectorAll('input[name="topping_ids[]"]:checked').forEach(function (top) {
            params.append('topping_ids[]', top.value);
        });

        var note = document.getElementById('dishNote');
        if (note && note.value.trim()) {
            params.set('ghi_chu', note.value.trim());
        }

        return params;
    }

    // Them mon vao gio hang (gui len PHP bang fetch)
    function guiThemVaoGio(button, soLuong) {
        var idMon = parseInt(button.getAttribute('data-id'), 10);

        if (!idMon || typeof window.fetch !== 'function') {
            return;
        }

        var qty = Math.max(1, parseInt(soLuong, 10) || 1);

        // Chi trang chi tiet (data-use-opts) gui them lua chon size / topping / ghi chu
        var params = layLuaChonTuyChinh(button.getAttribute('data-use-opts') === '1');
        params.set('id', idMon);
        params.set('so_luong', qty);

        button.disabled = true;
        button.classList.add('is-loading');

        fetch('?action=cart-add', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
            },
            body: params.toString()
        })
            .then(function (response) {
                return response.json();
            })
            .then(function (data) {
                if (data.success && typeof data.cart_count === 'number') {
                    if (cartCountEl) {
                        cartCountEl.textContent = data.cart_count;
                    }
                }

                showToast(data.message || 'Đã thêm vào giỏ hàng');
            })
            .catch(function () {
                showToast('Có lỗi xảy ra, vui lòng thử lại');
            })
            .then(function () {
                button.disabled = false;
                button.classList.remove('is-loading');
            });
    }

    document.querySelectorAll('.btn-add').forEach(function (button) {
        button.addEventListener('click', function () {
            var soLuong = 1;

            if (button.getAttribute('data-use-qty') === '1') {
                var qtyInput = document.getElementById('detailQty');
                soLuong = qtyInput ? parseInt(qtyInput.value, 10) : 1;
            }

            guiThemVaoGio(button, soLuong);
        });
    });

    // ---- Trang chi tiet mon an: chon so luong ----
    var detailQty = document.getElementById('detailQty');
    var qtyMinus = document.getElementById('qtyMinus');
    var qtyPlus = document.getElementById('qtyPlus');
    var qtyActions = document.querySelector('.dish-detail-actions');
    var qtyMax = qtyActions ? parseInt(qtyActions.getAttribute('data-max'), 10) || 99 : 99;

    // ---- Trang chi tiet: tinh tien theo size / topping / so luong ----
    function capNhatTien() {
        var box = document.getElementById('dishBuyBox');
        var unitEl = document.getElementById('dishUnitPrice');
        var totalEl = document.getElementById('dishTotalPrice');
        var qtyHintEl = document.getElementById('dishTotalQty');

        if (!box || !unitEl || !totalEl) {
            return;
        }

        var gia = parseInt(box.getAttribute('data-base'), 10) || 0;

        var size = box.querySelector('input[name="size_id"]:checked');
        if (size) {
            gia += parseInt(size.getAttribute('data-gia-tang'), 10) || 0;
        }

        box.querySelectorAll('input[name="topping_ids[]"]:checked').forEach(function (top) {
            gia += parseInt(top.getAttribute('data-gia'), 10) || 0;
        });

        var qty = detailQty ? (parseInt(detailQty.value, 10) || 1) : 1;

        unitEl.textContent = formatPrice(gia);
        totalEl.textContent = formatPrice(gia * qty);

        if (qtyHintEl) {
            qtyHintEl.textContent = '× ' + qty + ' phần';
        }
    }

    function capNhatSoLuong(value) {
        if (!detailQty) {
            return;
        }

        var qty = parseInt(value, 10);
        if (!Number.isFinite(qty)) {
            qty = 1;
        }
        qty = Math.min(Math.max(qty, 1), qtyMax);
        detailQty.value = qty;

        if (qtyMinus) {
            qtyMinus.disabled = qty <= 1;
        }
        if (qtyPlus) {
            qtyPlus.disabled = qty >= qtyMax;
        }

        capNhatTien();
    }

    if (detailQty) {
        capNhatSoLuong(detailQty.value);

        detailQty.addEventListener('change', function () {
            capNhatSoLuong(detailQty.value);
        });
        detailQty.addEventListener('input', function () {
            if (detailQty.value !== '') {
                capNhatSoLuong(detailQty.value);
            }
        });
    }

    if (qtyMinus) {
        qtyMinus.addEventListener('click', function () {
            capNhatSoLuong((parseInt(detailQty.value, 10) || 1) - 1);
        });
    }

    if (qtyPlus) {
        qtyPlus.addEventListener('click', function () {
            capNhatSoLuong((parseInt(detailQty.value, 10) || 1) + 1);
        });
    }

    // Chon size / topping -> tinh lai tien ngay tren trang
    var dishBuyBox = document.getElementById('dishBuyBox');
    if (dishBuyBox) {
        dishBuyBox.addEventListener('change', function (event) {
            if (event.target && event.target.matches('input[name="size_id"], input[name="topping_ids[]"]')) {
                capNhatTien();
            }
        });

        capNhatTien();
    }

    // ---- Trang chi tiet: chuyen anh phu -> anh lon ----
    document.querySelectorAll('.dish-thumb-item').forEach(function (thumb) {
        thumb.addEventListener('click', function () {
            var full = thumb.getAttribute('data-full');
            var mainImg = document.getElementById('dishMainImg');

            if (!full || !mainImg) {
                return;
            }

            mainImg.src = full;
            mainImg.style.display = '';

            document.querySelectorAll('.dish-thumb-item').forEach(function (item) {
                item.classList.remove('is-active');
            });
            thumb.classList.add('is-active');
        });
    });

    // ---- Trang chi tiet: to mau sao danh gia ----
    var starInput = document.getElementById('dishStarInput');
    if (starInput) {
        var starHints = ['', 'Rất tệ', 'Tệ', 'Bình thường', 'Tốt', 'Rất tuyệt vời'];

        var toMauSao = function () {
            var checked = starInput.querySelector('input[name="so_sao"]:checked');
            var soSao = checked ? parseInt(checked.value, 10) : 0;

            starInput.querySelectorAll('label').forEach(function (label, index) {
                label.classList.toggle('is-on', index + 1 <= soSao);
            });

            var hint = document.getElementById('dishStarHint');
            if (hint) {
                hint.textContent = starHints[soSao] || '';
            }
        };

        starInput.addEventListener('change', toMauSao);
        toMauSao();
    }

    // ---- Mua ngay: them vao gio (co lua chon) roi chuyen den trang dat hang ----
    var buyNowBtn = document.getElementById('buyNowBtn');
    if (buyNowBtn) {
        buyNowBtn.addEventListener('click', function () {
            var addBtn = document.getElementById('detailAddBtn');
            if (!addBtn || typeof window.fetch !== 'function') {
                return;
            }

            var qty = detailQty ? parseInt(detailQty.value, 10) : 1;
            var idMon = parseInt(addBtn.getAttribute('data-id'), 10);

            var params = layLuaChonTuyChinh(true);
            params.set('id', idMon);
            params.set('so_luong', Math.max(1, qty));

            buyNowBtn.disabled = true;

            fetch('?action=cart-add', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
                },
                body: params.toString()
            })
                .then(function (response) {
                    return response.json();
                })
                .then(function (data) {
                    if (data.success) {
                        window.location.href = '?action=checkout';
                    } else {
                        showToast(data.message || 'Không thể thêm món vào giỏ hàng');
                        buyNowBtn.disabled = false;
                    }
                })
                .catch(function () {
                    showToast('Có lỗi xảy ra, vui lòng thử lại');
                    buyNowBtn.disabled = false;
                });
        });
    }

    // ---- Card mon an: bam vao de xem chi tiet ----
    document.querySelectorAll('.dish-card[data-href]').forEach(function (card) {
        card.addEventListener('click', function (event) {
            if (event.target.closest('button, a, input, select, textarea')) {
                return;
            }

            window.location.href = card.getAttribute('data-href');
        });
    });

    if (cartClearButton) {
        cartClearButton.addEventListener('click', function () {
            cart = [];
            saveCart();
            renderCart();
        });
    }

    document.querySelectorAll('.dish-fav').forEach(function (button) {
        button.addEventListener('click', function () {
            button.classList.toggle('is-active');
            var icon = button.querySelector('i');

            if (icon) {
                icon.classList.toggle('bi-heart');
                icon.classList.toggle('bi-heart-fill');
            }
        });
    });

    var dishGrid = document.getElementById('dishGrid');

    if (dishGrid) {
        var dishCards = Array.prototype.slice.call(dishGrid.querySelectorAll('.dish-card'));
        var filterChips = Array.prototype.slice.call(document.querySelectorAll('.filter-chips .chip'));
        var categoryCards = Array.prototype.slice.call(document.querySelectorAll('.cat-card[data-filter]'));
        var menuSearch = document.getElementById('menuSearch');
        var menuSearchClear = document.getElementById('menuSearchClear');
        var menuSort = document.getElementById('menuSort');
        var menuResult = document.getElementById('menuResult');
        var menuEmpty = document.getElementById('menuEmpty');
        var menuReset = document.getElementById('menuReset');
        var headerSearchInput = document.querySelector('.search-form input[type="search"]');
        var originalOrder = dishCards.slice();
        var currentQuery = '';
        var currentCategory = 'all';

        function normalize(value) {
            return (value || '')
                .toString()
                .toLowerCase()
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .replace(/đ/g, 'd');
        }

        function toggleClearButton() {
            if (menuSearchClear) {
                menuSearchClear.classList.toggle('is-visible', currentQuery.length > 0);
            }
        }

        function applyFilters() {
            var query = normalize(currentQuery.trim());
            var visible = 0;

            dishCards.forEach(function (card) {
                var haystack = normalize(card.getAttribute('data-search') || card.textContent);
                var matchesQuery = !query || haystack.indexOf(query) !== -1;
                var matchesCategory = currentCategory === 'all' ||
                    card.getAttribute('data-category') === currentCategory;
                var isMatch = matchesQuery && matchesCategory;

                card.classList.toggle('is-hidden', !isMatch);

                if (isMatch) {
                    card.classList.add('is-visible');
                    visible += 1;
                }
            });

            if (menuEmpty) {
                menuEmpty.hidden = visible !== 0;
            }
            if (menuResult) {
                menuResult.textContent = visible + ' món ăn';
            }
        }

        function applySort() {
            var value = menuSort ? menuSort.value : 'default';
            var sorted = dishCards.slice();

            if (value === 'price-asc' || value === 'price-desc') {
                sorted.sort(function (a, b) {
                    var diff = parseFloat(a.getAttribute('data-price')) - parseFloat(b.getAttribute('data-price'));
                    return value === 'price-asc' ? diff : -diff;
                });
            } else if (value === 'sold') {
                sorted.sort(function (a, b) {
                    return parseFloat(b.getAttribute('data-sold')) - parseFloat(a.getAttribute('data-sold'));
                });
            } else if (value === 'rating') {
                sorted.sort(function (a, b) {
                    return parseFloat(b.getAttribute('data-rating')) - parseFloat(a.getAttribute('data-rating'));
                });
            } else {
                sorted = originalOrder.slice();
            }

            sorted.forEach(function (card) {
                dishGrid.appendChild(card);
            });
        }

        function setCategory(category) {
            currentCategory = category || 'all';

            filterChips.forEach(function (chip) {
                chip.classList.toggle('is-active', chip.getAttribute('data-filter') === currentCategory);
            });
        }

        function setQuery(value, source) {
            currentQuery = value || '';

            if (menuSearch && source !== menuSearch) {
                menuSearch.value = currentQuery;
            }
            if (headerSearchInput && source !== headerSearchInput) {
                headerSearchInput.value = currentQuery;
            }

            toggleClearButton();
            applyFilters();
        }

        filterChips.forEach(function (chip) {
            chip.addEventListener('click', function () {
                setCategory(chip.getAttribute('data-filter'));
                applyFilters();
            });
        });

        categoryCards.forEach(function (card) {
            card.addEventListener('click', function () {
                setCategory(card.getAttribute('data-filter'));
                if (menuSort) {
                    menuSort.value = 'default';
                }
                applySort();
                applyFilters();
            });
        });

        if (menuSearch) {
            menuSearch.addEventListener('input', function () {
                setQuery(menuSearch.value, menuSearch);
            });
        }

        if (headerSearchInput) {
            headerSearchInput.addEventListener('input', function () {
                setQuery(headerSearchInput.value, headerSearchInput);
            });

            var headerSearchForm = headerSearchInput.closest('form');
            if (headerSearchForm) {
                headerSearchForm.addEventListener('submit', function (event) {
                    event.preventDefault();
                    setQuery(headerSearchInput.value, headerSearchInput);

                    var menuSection = document.getElementById('thuc-don');
                    if (menuSection) {
                        menuSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                });
            }
        }

        if (menuSearchClear) {
            menuSearchClear.addEventListener('click', function () {
                setQuery('', menuSearch);
                if (menuSearch) {
                    menuSearch.focus();
                }
            });
        }

        if (menuSort) {
            menuSort.addEventListener('change', function () {
                applySort();
                applyFilters();
            });
        }

        if (menuReset) {
            menuReset.addEventListener('click', function () {
                setCategory('all');
                setQuery('', menuSearch);
                if (menuSort) {
                    menuSort.value = 'default';
                }
                applySort();
                applyFilters();
            });
        }

        applyFilters();
    }

    var revealItems = document.querySelectorAll('.reveal');

    if ('IntersectionObserver' in window && revealItems.length) {
        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.15 });

        revealItems.forEach(function (item) {
            observer.observe(item);
        });
    } else {
        revealItems.forEach(function (item) {
            item.classList.add('is-visible');
        });
    }

    var menu = document.getElementById('mainMenu');
    if (menu) {
        menu.querySelectorAll('.nav-link').forEach(function (link) {
            link.addEventListener('click', function () {
                if (menu.classList.contains('show') && window.bootstrap) {
                    window.bootstrap.Collapse.getOrCreateInstance(menu).hide();
                }
            });
        });
    }
})();
