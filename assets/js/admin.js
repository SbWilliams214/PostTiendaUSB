document.addEventListener('DOMContentLoaded', function () {

    /* ── SIDEBAR TOGGLE ── */
    var hamburger = document.getElementById('hamburger');
    var sidebar = document.getElementById('adminSidebar');
    var overlay = document.getElementById('sidebarOverlay');
    if (hamburger && sidebar && overlay) {
        hamburger.addEventListener('click', function () { sidebar.classList.toggle('open'); overlay.classList.toggle('show'); });
        overlay.addEventListener('click', function () { sidebar.classList.remove('open'); overlay.classList.remove('show'); });
    }

    /* ── CLOSE ALERTS ── */
    document.querySelectorAll('.close-alert').forEach(function (btn) {
        btn.addEventListener('click', function () { this.closest('.alert').style.display = 'none'; });
    });

    /* ── MODAL HELPERS ── */
    window.openModal = function (id) { var el = document.getElementById(id); if (el) el.classList.add('show'); };
    window.closeModal = function (id) { var el = document.getElementById(id); if (el) el.classList.remove('show'); };
    document.querySelectorAll('.modal-overlay').forEach(function (ov) {
        ov.addEventListener('click', function (e) { if (e.target === ov) ov.classList.remove('show'); });
    });

    /* ── POS: SEARCH / FILTER ── */
    var posSearch = document.getElementById('posSearchInput');
    if (posSearch) {
        posSearch.focus();
        var allCards = document.querySelectorAll('.pos-product-card');

        posSearch.addEventListener('input', function () {
            var q = this.value.trim().toLowerCase();
            allCards.forEach(function (card) {
                var name = card.querySelector('.ppc-name').textContent.toLowerCase();
                card.style.display = (!q || name.indexOf(q) !== -1) ? '' : 'none';
            });
        });

        posSearch.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                var q = this.value.trim().toLowerCase();
                // If barcode match (exact), auto-add
                allCards.forEach(function (card) {
                    var onclick = card.getAttribute('onclick');
                    if (onclick && onclick.toLowerCase().indexOf(q) !== -1) {
                        card.click();
                    }
                });
                // Also try API search for barcode
                if (q.length > 3) {
                    fetch('api/buscar_producto.php?q=' + encodeURIComponent(q))
                        .then(function (r) { return r.json(); })
                        .then(function (data) {
                            if (data.length === 1) {
                                var p = data[0];
                                addToCart(p.id, p.nombre, parseFloat(p.precio_venta), p.cantidad);
                            }
                        }).catch(function(){});
                }
            }
        });
    }

    /* ── POS: CART ── */
    window.posCart = [];

    window.addToCart = function (id, name, price, maxStock) {
        var existing = posCart.find(function (i) { return i.id === id; });
        if (existing) {
            if (existing.qty < maxStock) existing.qty++;
        } else {
            posCart.push({ id: id, name: name, price: parseFloat(price), qty: 1, maxStock: maxStock });
        }
        renderCart();
    };

    window.updateCartQty = function (id, delta) {
        var item = posCart.find(function (i) { return i.id === id; });
        if (!item) return;
        item.qty += delta;
        if (item.qty <= 0) { posCart = posCart.filter(function (i) { return i.id !== id; }); }
        else if (item.qty > item.maxStock) { item.qty = item.maxStock; }
        renderCart();
    };

    window.removeFromCart = function (id) {
        posCart = posCart.filter(function (i) { return i.id !== id; });
        renderCart();
    };

    function renderCart() {
        var container = document.getElementById('posCartItems');
        var totalEl = document.getElementById('posTotal');
        var cobrarBtn = document.getElementById('btnCobrar');
        if (!container) return;

        if (!posCart.length) {
            container.innerHTML = '<p class="text-muted text-center" style="padding:30px 0;">Carrito vacio</p>';
            if (totalEl) totalEl.textContent = 'Bs 0.00';
            if (cobrarBtn) cobrarBtn.disabled = true;
            return;
        }

        var total = 0;
        container.innerHTML = posCart.map(function (item) {
            var sub = item.price * item.qty;
            total += sub;
            return '<div class="pos-cart-item">'
                + '<span class="ci-name">' + escapeHtml(item.name) + '</span>'
                + '<span class="ci-qty">'
                + '  <button onclick="updateCartQty(' + item.id + ',-1)">&minus;</button>'
                + '  <span>' + item.qty + '</span>'
                + '  <button onclick="updateCartQty(' + item.id + ',1)">+</button>'
                + '</span>'
                + '<span class="ci-subtotal">Bs ' + sub.toFixed(2) + '</span>'
                + '<button class="ci-remove" onclick="removeFromCart(' + item.id + ')" title="Eliminar">&times;</button>'
                + '</div>';
        }).join('');

        if (totalEl) totalEl.textContent = 'Bs ' + total.toFixed(2);
        if (cobrarBtn) cobrarBtn.disabled = false;
    }

    /* ── POS: COBRAR ── */
    var cobrarBtn = document.getElementById('btnCobrar');
    if (cobrarBtn) {
        cobrarBtn.addEventListener('click', function () {
            if (!posCart.length) return;
            var metodo = document.getElementById('posPaymentMethod').value;
            var csrf = document.querySelector('input[name="csrf_token"]');
            var payload = {
                items: posCart.map(function (i) { return { id: i.id, qty: i.qty, price: i.price }; }),
                metodo_pago: metodo,
                csrf_token: csrf ? csrf.value : ''
            };
            fetch('api/registrar_venta.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.success) {
                    alert('Venta registrada. Folio: ' + (data.folio || ''));
                    posCart = [];
                    renderCart();
                    if (posSearch) { posSearch.value = ''; posSearch.focus(); }
                } else {
                    alert('Error: ' + (data.error || 'No se pudo registrar'));
                }
            })
            .catch(function () { alert('Error de conexion.'); });
        });
    }

    /* ── CONFIRM ACTIONS ── */
    document.querySelectorAll('[data-confirm]').forEach(function (el) {
        el.addEventListener('click', function (e) { if (!confirm(this.dataset.confirm)) e.preventDefault(); });
    });

    /* ── FILE INPUT PREVIEW ── */
    document.querySelectorAll('input[type="file"][data-preview]').forEach(function (input) {
        input.addEventListener('change', function () {
            var preview = document.getElementById(this.dataset.preview);
            if (preview && this.files && this.files[0]) {
                var reader = new FileReader();
                reader.onload = function (e) { preview.src = e.target.result; preview.style.display = 'block'; };
                reader.readAsDataURL(this.files[0]);
            }
        });
    });

    /* ── UTILITY ── */
    function escapeHtml(str) { var div = document.createElement('div'); div.textContent = str; return div.innerHTML; }
    window.escapeHtml = escapeHtml;
});
