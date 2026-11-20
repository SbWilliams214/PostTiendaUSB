document.addEventListener('DOMContentLoaded', function () {

    /* -- SIDEBAR TOGGLE -- */
    var hamburger = document.getElementById('hamburger');
    var sidebar = document.getElementById('adminSidebar');
    var overlay = document.getElementById('sidebarOverlay');
    if (hamburger && sidebar && overlay) {
        hamburger.addEventListener('click', function () { sidebar.classList.toggle('open'); overlay.classList.toggle('show'); });
        overlay.addEventListener('click', function () { sidebar.classList.remove('open'); overlay.classList.remove('show'); });
    }

    /* -- MODAL HELPERS -- */
    window.openModal = function (id) { var el = document.getElementById(id); if (el) el.classList.add('show'); };
    window.closeModal = function (id) { var el = document.getElementById(id); if (el) el.classList.remove('show'); };
    document.querySelectorAll('.modal-overlay').forEach(function (ov) {
        ov.addEventListener('click', function (e) { if (e.target === ov) ov.classList.remove('show'); });
    });

    /* -- POS: SEARCH / FILTER -- */
    var posSearch = document.getElementById('posSearchInput');
    if (posSearch) {
        posSearch.focus();
        var allCards = document.querySelectorAll('#posResults .pos-product-card');

        posSearch.addEventListener('input', function () {
            var q = this.value.trim().toLowerCase();
            allCards.forEach(function (card) {
                var name = (card.getAttribute('data-name') || '').toLowerCase();
                var barcode = (card.getAttribute('data-barcode') || '').toLowerCase();
                card.style.display = (!q || name.indexOf(q) !== -1 || barcode.indexOf(q) !== -1) ? '' : 'none';
            });
        });

        posSearch.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                var q = this.value.trim().toLowerCase();
                // Try to find exact barcode match
                var found = false;
                allCards.forEach(function (card) {
                    var barcode = (card.getAttribute('data-barcode') || '').toLowerCase();
                    if (barcode && barcode === q) {
                        card.click();
                        found = true;
                    }
                });
                if (!found && q.length > 3) {
                    fetch('api/buscar_producto.php?q=' + encodeURIComponent(q))
                        .then(function (r) { return r.json(); })
                        .then(function (data) {
                            if (data.length === 1) {
                                var p = data[0];
                                addToCart(p.id, p.nombre, parseFloat(p.precio_venta), p.cantidad);
                            }
                        }).catch(function(){});
                }
                this.value = '';
                // Reset filter
                allCards.forEach(function(card){ card.style.display = ''; });
            }
        });
    }

    /* -- POS: CART -- */
    window.posCart = [];

    window.addToCart = function (id, name, price, maxStock) {
        var existing = posCart.find(function (i) { return i.id === id && !i.isPromo; });
        if (existing) {
            if (existing.qty < maxStock) existing.qty++;
        } else {
            posCart.push({ id: id, name: name, price: parseFloat(price), qty: 1, maxStock: maxStock, isPromo: false });
        }
        renderCart();
    };

    window.addPromoToCart = function (id, name, price) {
        var existing = posCart.find(function (i) { return i.id === id && i.isPromo; });
        if (existing) {
            existing.qty++;
        } else {
            posCart.push({ id: id, name: '[PROMO] ' + name, price: parseFloat(price), qty: 1, maxStock: 999, isPromo: true });
        }
        renderCart();
    };

    window.updateCartQty = function (idx, delta) {
        var item = posCart[idx];
        if (!item) return;
        item.qty += delta;
        if (item.qty <= 0) { posCart.splice(idx, 1); }
        else if (item.qty > item.maxStock) { item.qty = item.maxStock; }
        renderCart();
    };

    window.removeFromCart = function (idx) {
        posCart.splice(idx, 1);
        renderCart();
    };

    window.clearPosCart = function() {
        posCart = [];
        renderCart();
        if (posSearch) { posSearch.value = ''; posSearch.focus(); }
    };

    window.renderCart = renderCart;

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
        container.innerHTML = posCart.map(function (item, idx) {
            var sub = item.price * item.qty;
            total += sub;
            return '<div class="pos-cart-item' + (item.isPromo ? ' pos-cart-promo' : '') + '">'
                + '<span class="ci-name">' + escapeHtml(item.name) + '</span>'
                + '<span class="ci-qty">'
                + '  <button onclick="updateCartQty(' + idx + ',-1)">&minus;</button>'
                + '  <span>' + item.qty + '</span>'
                + '  <button onclick="updateCartQty(' + idx + ',1)">+</button>'
                + '</span>'
                + '<span class="ci-subtotal">Bs ' + sub.toFixed(2) + '</span>'
                + '<button class="ci-remove" onclick="removeFromCart(' + idx + ')" title="Eliminar">&times;</button>'
                + '</div>';
        }).join('');

        if (totalEl) totalEl.textContent = 'Bs ' + total.toFixed(2);
        if (cobrarBtn) cobrarBtn.disabled = false;
    }

    /* -- POS: COBRAR -- */
    var cobrarBtn = document.getElementById('btnCobrar');
    if (cobrarBtn) {
        cobrarBtn.addEventListener('click', function () {
            if (!posCart.length) return;
            var metodo = document.getElementById('posPaymentMethod').value;
            var csrf = document.querySelector('input[name="csrf_token"]');
            var payload = {
                items: posCart.filter(function(i){ return !i.isPromo; }).map(function (i) { return { id: i.id, qty: i.qty, price: i.price }; }),
                promos: posCart.filter(function(i){ return i.isPromo; }).map(function(i) { return { id: i.id, qty: i.qty, price: i.price }; }),
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
                    showPosToast('Venta registrada. Folio: ' + (data.folio || ''));
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

    /* -- POS Toast -- */
    function showPosToast(msg) {
        var t = document.createElement('div');
        t.className = 'alert alert-success flash-auto';
        t.style.cssText = 'position:fixed;top:20px;right:20px;z-index:9999;padding:14px 24px;border-radius:6px;box-shadow:0 4px 12px rgba(0,0,0,0.15);';
        t.textContent = msg;
        document.body.appendChild(t);
        setTimeout(function(){ t.style.transition='opacity 0.3s'; t.style.opacity='0'; setTimeout(function(){ t.remove(); },300); },1500);
    }

    /* -- FILE INPUT PREVIEW -- */
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

    /* -- UTILITY -- */
    function escapeHtml(str) { var div = document.createElement('div'); div.textContent = str; return div.innerHTML; }
    window.escapeHtml = escapeHtml;
});
