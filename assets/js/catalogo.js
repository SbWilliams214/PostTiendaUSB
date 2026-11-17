/**
 * Catalogo Publico — JavaScript
 */
document.addEventListener('DOMContentLoaded', function () {

    /* -- Mobile menu toggle -- */
    const menuBtn = document.getElementById('mobileMenuBtn');
    const navLinks = document.getElementById('catalogNavLinks');
    if (menuBtn && navLinks) {
        menuBtn.addEventListener('click', function () {
            navLinks.classList.toggle('show');
        });
    }

    /* -- Update cart badge on load -- */
    updateCartBadge();

    /* -- Close alerts after 5s -- */
    document.querySelectorAll('.alert').forEach(function(el) {
        setTimeout(function() { el.style.display = 'none'; }, 5000);
    });
});

/* -- Add to cart -- */
function catalogAddToCart(productId, qty) {
    qty = parseInt(qty) || 1;
    const fd = new FormData();
    fd.append('action', 'add');
    fd.append('id', productId);
    fd.append('qty', qty);

    fetch('api/carrito.php', { method: 'POST', body: fd })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                showToast('Producto agregado al carrito');
                updateCartBadge();
            } else {
                showToast('Error: ' + (data.message || 'No se pudo agregar'));
            }
        })
        .catch(function() { showToast('Error de conexion'); });
}

/* -- Update badge -- */
function updateCartBadge() {
    fetch('api/carrito.php')
        .then(function(r) { return r.json(); })
        .then(function(data) {
            const badge = document.getElementById('cartBadge');
            if (badge) badge.textContent = data.data ? data.data.count : 0;
        })
        .catch(function() {});
}

/* -- Toast notification -- */
function showToast(message) {
    const toast = document.createElement('div');
    toast.className = 'toast';
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(function() { toast.remove(); }, 3000);
}
