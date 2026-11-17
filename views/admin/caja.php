<?php global $viewData; $d = $viewData ?? []; ?>
<div class="card">
    <h2 style="margin-bottom:16px;">Caja</h2>
</div>

<div class="pos-wrapper">
    <div class="pos-left">
        <div class="pos-search">
            <input type="text" id="posSearchInput" class="form-control" autofocus
                   placeholder="Buscar producto o escanear codigo de barras...">
        </div>
        <div class="pos-results" id="posResults">
            <?php if (!empty($d['productos'])): ?>
                <?php foreach ($d['productos'] as $p): ?>
                <div class="pos-product-card" onclick="addToCart(<?= $p['id'] ?>, '<?= addslashes(htmlspecialchars($p['nombre'])) ?>', <?= $p['precio_venta'] ?>, <?= $p['cantidad'] ?>)">
                    <?php if ($p['ruta_image']): ?>
                        <img src="<?= htmlspecialchars($p['ruta_image']) ?>" class="ppc-img" alt="">
                    <?php else: ?>
                        <div class="ppc-img" style="background:#eef1f8;display:flex;align-items:center;justify-content:center;color:#aaa;">Sin imagen</div>
                    <?php endif; ?>
                    <span class="ppc-name"><?= htmlspecialchars($p['nombre']) ?></span>
                    <span class="ppc-price">Bs <?= number_format($p['precio_venta'], 2) ?></span>
                    <span class="ppc-stock">Stock: <?= $p['cantidad'] ?></span>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-muted text-center" style="grid-column:1/-1;padding:40px 0;">No hay productos registrados</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="pos-right">
        <div class="pos-cart-header">
            <span>Venta Actual</span>
            <button class="btn btn-sm btn-secondary" onclick="posCart=[];renderCart();">Limpiar</button>
        </div>
        <div class="pos-cart-items" id="posCartItems">
            <p class="text-muted text-center" style="padding:30px 0;">Carrito vacio</p>
        </div>
        <div class="pos-cart-footer">
            <div class="pos-total">
                <span>TOTAL</span>
                <span id="posTotal">Bs 0.00</span>
            </div>
            <div class="pos-payment-row">
                <select id="posPaymentMethod">
                    <option value="1">Efectivo</option>
                    <option value="2">QR</option>
                </select>
            </div>
            <?= csrf_field() ?>
            <button class="pos-btn-cobrar" id="btnCobrar" disabled>COBRAR</button>
        </div>
    </div>
</div>
