<?php /** Catalogo — Home */ global $viewData; $d = $viewData ?? []; ?>
<div class="catalog-container">
    <!-- Hero -->
    <section class="catalog-hero">
        <h1>Bienvenido a nuestra Tienda</h1>
        <p>Explora nuestro catalogo y haz tu pedido en linea</p>
        <div class="hero-search">
            <input type="text" id="catalogSearchInput" placeholder="Buscar productos..." value="<?= htmlspecialchars($d['buscar'] ?? '') ?>">
        </div>
    </section>

    <!-- Promotions at TOP -->
    <?php if (!empty($d['promociones'])): ?>
    <section class="promo-section" style="margin-top:0;">
        <h2>Promociones</h2>
        <div class="product-grid">
            <?php foreach ($d['promociones'] as $pr): ?>
            <div class="product-card" style="border:2px solid #9E1B22;position:relative;cursor:pointer;" onclick="alert('Esta promocion solo esta disponible en caja. Visitanos en tienda para adquirirla.')">
                <span class="promo-badge">PROMO</span>
                <?php if (!empty($pr['ruta_imagen'])): ?>
                    <img src="<?= htmlspecialchars($pr['ruta_imagen']) ?>" alt="<?= htmlspecialchars($pr['nombre']) ?>" class="product-card-img">
                <?php else: ?>
                    <div class="product-card-img placeholder-img">Sin imagen</div>
                <?php endif; ?>
                <div class="product-card-body">
                    <h3 class="product-name"><?= htmlspecialchars($pr['nombre']) ?></h3>
                    <p style="font-size:.88rem;color:#555;margin-bottom:6px;"><?= htmlspecialchars($pr['descripcion'] ?? '') ?></p>
                    <p class="product-price" style="color:#9E1B22;">Bs <?= number_format($pr['precio'], 2) ?></p>
                    <span style="display:inline-block;margin-top:6px;font-size:.82rem;color:#1A3B8B;font-weight:600;">Disponible solo en caja</span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- Category pills -->
    <div class="category-pills">
        <a href="index.php?page=catalogo" class="pill <?= empty($d['cat_actual']) ? 'active' : '' ?>">Todos</a>
        <?php foreach (($d['categorias'] ?? []) as $c): ?>
            <a href="index.php?page=catalogo&cat=<?= $c['id'] ?>" class="pill <?= ($d['cat_actual'] ?? 0) == $c['id'] ? 'active' : '' ?>"><?= htmlspecialchars($c['nombre']) ?></a>
        <?php endforeach; ?>
    </div>

    <!-- Products grid -->
    <section class="product-grid" id="productGrid">
        <?php foreach (($d['productos'] ?? []) as $p): ?>
        <div class="product-card" data-name="<?= strtolower(htmlspecialchars($p['nombre'])) ?>" data-brand="<?= strtolower(htmlspecialchars($p['compania'] ?? '')) ?>">
            <?php if ($p['ruta_image']): ?>
                <img src="<?= htmlspecialchars($p['ruta_image']) ?>" alt="<?= htmlspecialchars($p['nombre']) ?>" class="product-card-img">
            <?php else: ?>
                <div class="product-card-img placeholder-img">Sin imagen</div>
            <?php endif; ?>
            <div class="product-card-body">
                <span class="product-brand"><?= htmlspecialchars($p['compania'] ?? '') ?></span>
                <h3 class="product-name"><?= htmlspecialchars($p['nombre']) ?></h3>
                <p class="product-price">Bs <?= number_format($p['precio_venta'], 2) ?></p>
                <div class="product-card-actions">
                    <a href="index.php?page=catalogo&vista=producto&id=<?= $p['id'] ?>" class="btn-ver">Ver detalle</a>
                    <button type="button" class="btn-add-cart" onclick="catalogAddToCart(<?= $p['id'] ?>)">Agregar</button>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        <?php if (empty($d['productos'])): ?>
            <p class="text-center text-muted" style="grid-column:1/-1;padding:60px 0;">No hay productos disponibles</p>
        <?php endif; ?>
    </section>
</div>
