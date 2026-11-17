<?php /** Catalogo — Carrito */ ?>
<div class="catalog-container">
    <h2 style="margin-bottom:20px;">Mi Carrito</h2>
    <div id="cartPageContent">
        <p class="text-center text-muted" style="padding:40px;">Cargando carrito...</p>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() { loadCartPage(); });

function loadCartPage() {
    fetch('api/carrito.php')
        .then(r => r.json())
        .then(data => {
            const container = document.getElementById('cartPageContent');
            if (!data.success || !data.data.items.length) {
                container.innerHTML = '<p class="text-center text-muted" style="padding:40px;">Tu carrito esta vacio</p><a href="index.php" class="btn-add-cart-lg" style="display:inline-block;margin-top:10px;">Seguir Comprando</a>';
                return;
            }
            const items = data.data.items;
            let html = '<div class="cart-table"><table class="data-table"><thead><tr><th>Producto</th><th>Precio</th><th>Cantidad</th><th>Subtotal</th><th></th></tr></thead><tbody>';
            items.forEach(item => {
                html += '<tr><td>' + item.nombre + '</td><td>Bs ' + parseFloat(item.precio_venta).toFixed(2) + '</td>'
                    + '<td><div class="cart-qty-controls"><button onclick="updateCartItem(' + item.id + ',' + (item.cantidad-1) + ')">-</button><span>' + item.cantidad + '</span><button onclick="updateCartItem(' + item.id + ',' + (item.cantidad+1) + ')">+</button></div></td>'
                    + '<td><strong>Bs ' + parseFloat(item.subtotal).toFixed(2) + '</strong></td>'
                    + '<td><button class="btn-remove" onclick="removeCartItem(' + item.id + ')">X</button></td></tr>';
            });
            html += '</tbody></table></div>';
            html += '<div class="cart-summary"><div class="cart-total">Total: Bs ' + parseFloat(data.data.total).toFixed(2) + '</div>';
            html += '<div class="cart-actions"><a href="index.php" class="btn-secondary-cat">Seguir Comprando</a>';
            html += '<a href="index.php?page=catalogo&vista=checkout" class="btn-primary-cat">Confirmar Pedido</a></div></div>';
            container.innerHTML = html;
        });
}

function updateCartItem(id, qty) {
    if (qty <= 0) { removeCartItem(id); return; }
    const fd = new FormData(); fd.append('action','update'); fd.append('id',id); fd.append('qty',qty);
    fetch('api/carrito.php', {method:'POST', body:fd}).then(()=>{ loadCartPage(); updateBadge(); });
}
function removeCartItem(id) {
    const fd = new FormData(); fd.append('action','remove'); fd.append('id',id);
    fetch('api/carrito.php', {method:'POST', body:fd}).then(()=>{ loadCartPage(); updateBadge(); });
}
function updateBadge() {
    fetch('api/carrito.php').then(r=>r.json()).then(d=>{ const b=document.getElementById('cartBadge'); if(b) b.textContent=d.data?.count||0; });
}
</script>
