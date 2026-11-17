<?php /** Catalogo — Detalle Producto */ global $viewData; $p = ($viewData['producto'] ?? null); ?>
<div class="catalog-container">
    <?php if (!$p): ?>
        <p class="text-center" style="padding:60px;">Producto no encontrado.</p>
    <?php else: ?>
    <a href="index.php" class="back-link">Volver al catalogo</a>
    <div class="product-detail">
        <div class="product-detail-img">
            <?php if ($p['ruta_image']): ?>
                <img src="<?= htmlspecialchars($p['ruta_image']) ?>" alt="<?= htmlspecialchars($p['nombre']) ?>">
            <?php else: ?>
                <div class="placeholder-img" style="height:300px;font-size:1.2rem;display:flex;align-items:center;justify-content:center;">Sin imagen</div>
            <?php endif; ?>
        </div>
        <div class="product-detail-info">
            <span class="product-brand"><?= htmlspecialchars($p['compania'] ?? '') ?></span>
            <h1><?= htmlspecialchars($p['nombre']) ?></h1>
            <p class="product-category"><?= htmlspecialchars($p['categoria'] ?? '') ?></p>
            <p class="product-desc"><?= nl2br(htmlspecialchars($p['descripcion'] ?? '')) ?></p>
            <p class="product-price-lg">Bs <?= number_format($p['precio_venta'], 2) ?></p>
            <p class="stock-indicator <?= $p['cantidad'] > 0 ? 'in-stock' : 'out-stock' ?>">
                <?= $p['cantidad'] > 0 ? "En stock ({$p['cantidad']} disponibles)" : 'Agotado' ?>
            </p>
            <?php if ($p['cantidad'] > 0): ?>
            <div class="add-to-cart-row">
                <label>Cantidad:</label>
                <input type="number" id="detailQty" value="1" min="1" max="<?= $p['cantidad'] ?>" class="qty-input">
                <button class="btn-add-cart-lg" onclick="catalogAddToCart(<?= $p['id'] ?>, document.getElementById('detailQty').value)">Agregar al Carrito</button>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>
