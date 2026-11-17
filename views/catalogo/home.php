<?php /** Catalogo — Home */ global $viewData; $d = $viewData ?? []; ?>
<div class="catalog-container">
    <!-- Hero -->
    <section class="catalog-hero">
        <h1>Bienvenido a nuestra Tienda</h1>
        <p>Explora nuestro catalogo y haz tu pedido en linea</p>
        <form class="hero-search" method="GET">
            <input type="hidden" name="page" value="catalogo"><input type="hidden" name="vista" value="home">
            <input type="text" name="buscar" placeholder="Buscar productos..." value="<?= htmlspecialchars($d['buscar'] ?? '') ?>">
            <button type="submit">Buscar</button>
        </form>
    </section>

    <!-- Category pills -->
    <div class="category-pills">
        <a href="index.php?page=catalogo" class="pill <?= empty($d['cat_actual']) ? 'active' : '' ?>">Todos</a>
        <?php foreach (($d['categorias'] ?? []) as $c): ?>
            <a href="index.php?page=catalogo&cat=<?= $c['id'] ?>" class="pill <?= ($d['cat_actual'] ?? 0) == $c['id'] ? 'active' : '' ?>"><?= htmlspecialchars($c['nombre']) ?></a>
        <?php endforeach; ?>
    </div>

    <!-- Products grid -->
    <section class="product-grid">
        <?php foreach (($d['productos'] ?? []) as $p): ?>
        <div class="product-card">
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
                    <button class="btn-add-cart" onclick="catalogAddToCart(<?= $p['id'] ?>)">Agregar</button>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        <?php if (empty($d['productos'])): ?>
            <p class="text-center text-muted" style="grid-column:1/-1;padding:60px 0;">No hay productos disponibles</p>
        <?php endif; ?>
    </section>

    <!-- Promotions -->
    <?php if (!empty($d['promociones'])): ?>
    <section class="promo-section">
        <h2>Promociones</h2>
        <div class="promo-grid">
            <?php foreach ($d['promociones'] as $pr): ?>
            <div class="promo-card">
                <span class="promo-badge">PROMO</span>
                <?php if ($pr['ruta_imagen']): ?><img src="<?= htmlspecialchars($pr['ruta_imagen']) ?>" class="promo-img"><?php endif; ?>
                <h3><?= htmlspecialchars($pr['nombre']) ?></h3>
                <p><?= htmlspecialchars($pr['descripcion'] ?? '') ?></p>
                <span class="promo-price">Bs <?= number_format($pr['precio'], 2) ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>
</div>
