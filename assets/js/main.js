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
