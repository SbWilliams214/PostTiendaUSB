<?php /** Catalogo — Checkout */ global $viewData; $d = $viewData ?? []; ?>
<div class="catalog-container">
    <h2 style="margin-bottom:20px;">Confirmar Pedido</h2>
    <div id="checkoutContent">
        <p class="text-center text-muted" style="padding:40px;">Cargando resumen...</p>
    </div>

    <!-- Success modal -->
    <div class="modal-overlay" id="orderSuccessModal">
        <div class="modal">
            <div class="modal-body text-center" style="padding:40px;">
                <div style="font-size:2rem;color:#27ae60;font-weight:bold;">Pedido Creado</div>
                <h2 style="margin:16px 0;">Tu pedido ha sido registrado</h2>
                <p>Tu codigo de pedido es: <strong id="orderCode"></strong></p>
                <p>Total: <strong id="orderTotal"></strong></p>
                <p class="text-muted">Presenta este codigo al recoger tu pedido en tienda.</p>
                <a href="index.php?page=catalogo&vista=mis_pedidos" class="btn-primary-cat" style="display:inline-block;margin-top:16px;">Ver Mis Pedidos</a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    fetch('api/carrito.php').then(r=>r.json()).then(data => {
        const container = document.getElementById('checkoutContent');
        if (!data.success || !data.data.items.length) {
            container.innerHTML = '<p class="text-center">Tu carrito esta vacio. <a href="index.php">Volver al catalogo</a></p>';
            return;
        }
        const items = data.data.items;
        let html = '<div class="checkout-grid"><div class="checkout-items"><h3>Resumen del Pedido</h3><ul>';
        items.forEach(item => {
            html += '<li>' + item.nombre + ' x ' + item.cantidad + ' — <strong>Bs ' + parseFloat(item.subtotal).toFixed(2) + '</strong></li>';
        });
        html += '</ul><div class="cart-total">Total: Bs ' + parseFloat(data.data.total).toFixed(2) + '</div></div>';
        html += '<div class="checkout-payment"><h3>Metodo de Pago</h3>';
        html += '<div class="payment-options">';
        html += '<label class="payment-option"><input type="radio" name="metodo" value="2" checked> Pago con QR</label>';
        html += '<label class="payment-option"><input type="radio" name="metodo" value="1"> Efectivo (pagar en tienda)</label>';
        html += '</div>';
        <?php if(!empty($d['qr_path'])): ?>
        html += '<div id="qrSection" class="qr-display"><p>Escanea el QR para pagar:</p><img src="<?= htmlspecialchars($d['qr_path']) ?>" alt="QR Pago" style="max-width:200px;"></div>';
        <?php else: ?>
        html += '<div id="qrSection" class="qr-display"><p class="text-muted">QR no configurado. Paga en tienda al recoger.</p></div>';
        <?php endif; ?>
        html += '<button onclick="confirmarPedido()" class="btn-primary-cat" style="width:100%;margin-top:20px;padding:14px;font-size:1.1rem;">Confirmar Pedido</button>';
        html += '</div></div>';
        container.innerHTML = html;

        // Toggle QR visibility
        document.querySelectorAll('input[name="metodo"]').forEach(r => {
            r.addEventListener('change', function() {
                document.getElementById('qrSection').style.display = this.value === '2' ? 'block' : 'none';
            });
        });
    });
});

function confirmarPedido() {
    const metodo = document.querySelector('input[name="metodo"]:checked')?.value || '1';
    const fd = new FormData();
    fd.append('action','crear');
    fd.append('metodo_pago', metodo);
    fetch('api/pedidos.php', { method:'POST', body:fd })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                document.getElementById('orderCode').textContent = data.codigo;
                document.getElementById('orderTotal').textContent = 'Bs ' + parseFloat(data.total).toFixed(2);
                document.getElementById('orderSuccessModal').classList.add('show');
            } else {
                alert('Error: ' + (data.message || 'No se pudo crear el pedido'));
            }
        });
}
</script>
