/**
 * Catalogo Publico — JavaScript
 */

/* -- Base path for API calls -- */
var _basePath = (function () {
    var s = document.querySelector('script[src*="catalogo.js"]');
    if (s) return s.src.replace(/assets\/js\/catalogo\.js.*$/, '');
    return '';
})();

document.addEventListener('DOMContentLoaded', function () {

    /* -- Mobile menu toggle -- */
    var menuBtn = document.getElementById('mobileMenuBtn');
    var navLinks = document.getElementById('catalogNavLinks');
    if (menuBtn && navLinks) {
        menuBtn.addEventListener('click', function () {
            navLinks.classList.toggle('show');
        });
    }

    /* -- Update cart badge on load -- */
    updateCartBadge();

    /* -- Close alerts after 5s -- */
    document.querySelectorAll('.alert').forEach(function (el) {
        setTimeout(function () { el.style.display = 'none'; }, 5000);
    });

    /* -- Live search filter (products + promos) -- */
    var searchInput = document.getElementById('catalogSearchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            var q = this.value.trim().toLowerCase();
            // Filter regular products
            document.querySelectorAll('#productGrid .product-card').forEach(function (card) {
                var name = card.getAttribute('data-name') || '';
                var brand = card.getAttribute('data-brand') || '';
                card.style.display = (!q || name.indexOf(q) !== -1 || brand.indexOf(q) !== -1) ? '' : 'none';
            });
            // Filter promo cards
            document.querySelectorAll('.promo-section .product-card').forEach(function (card) {
                var name = card.getAttribute('data-name') || '';
                card.style.display = (!q || name.indexOf(q) !== -1) ? '' : 'none';
            });
        });
    }
});

/* -- Add product to cart (GLOBAL) -- */
function catalogAddToCart(productId, qty) {
    qty = parseInt(qty) || 1;
    var fd = new FormData();
    fd.append('action', 'add');
    fd.append('id', productId);
    fd.append('qty', qty);
    fd.append('type', 'producto');

    fetch(_basePath + 'api/carrito.php', { method: 'POST', body: fd })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.success) {
                showToast('Producto agregado al carrito');
                updateCartBadge();
            } else {
                showToast('Error: ' + (data.message || 'No se pudo agregar'));
            }
        })
        .catch(function (err) {
            console.error('catalogAddToCart error:', err);
            showToast('Error de conexion');
        });
}

/* -- Add promo to cart (GLOBAL) -- */
function catalogAddPromo(promoId) {
    var fd = new FormData();
    fd.append('action', 'add');
    fd.append('id', promoId);
    fd.append('qty', 1);
    fd.append('type', 'promo');

    fetch(_basePath + 'api/carrito.php', { method: 'POST', body: fd })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.success) {
                showToast('Promocion agregada al carrito');
                updateCartBadge();
            } else {
                showToast('Error: ' + (data.message || 'No se pudo agregar'));
            }
        })
        .catch(function (err) {
            console.error('catalogAddPromo error:', err);
            showToast('Error de conexion');
        });
}

/* -- Update badge (GLOBAL) -- */
function updateCartBadge() {
    fetch(_basePath + 'api/carrito.php')
        .then(function (r) { return r.json(); })
        .then(function (data) {
            var badge = document.getElementById('cartBadge');
            if (badge) badge.textContent = data.data ? data.data.count : 0;
        })
        .catch(function () { });
}

/* -- Toast notification (GLOBAL) -- */
function showToast(message) {
    var toast = document.createElement('div');
    toast.className = 'toast';
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(function () { toast.remove(); }, 3000);
}
