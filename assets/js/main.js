(function () {
    'use strict';

    var header = document.getElementById('siteHeader');
    var backToTop = document.getElementById('backToTop');
    var cartCountEl = document.querySelector('.cart-count');
    var cartCount = cartCountEl ? parseInt(cartCountEl.textContent, 10) || 0 : 0;

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

    document.querySelectorAll('.btn-add').forEach(function (button) {
        button.addEventListener('click', function () {
            cartCount += 1;

            if (cartCountEl) {
                cartCountEl.textContent = cartCount;
            }

            var dishName = button.getAttribute('data-dish') || 'Món ăn';
            showToast('Đã thêm "' + dishName + '" vào giỏ hàng');
        });
    });

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
