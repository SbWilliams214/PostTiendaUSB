<?php /** Catalogo — Mis Pedidos */ global $viewData; $d = $viewData ?? []; ?>
<div class="catalog-container">
    <h2 style="margin-bottom:20px;">Mis Pedidos</h2>
    <?php if (empty($d['pedidos'])): ?>
        <p class="text-center text-muted" style="padding:40px;">Aun no has realizado ningun pedido.</p>
        <a href="index.php" class="btn-primary-cat" style="display:inline-block;">Ir al catalogo</a>
    <?php else: ?>
    <div class="orders-list">
        <?php foreach ($d['pedidos'] as $p): ?>
        <div class="order-card-cat">
            <div class="order-header-cat">
                <strong><?= htmlspecialchars($p['codigo_pedido']) ?></strong>
                <span class="badge <?= $p['status']==='pendiente'?'badge-warning':($p['status']==='entregado'?'badge-success':'badge-danger') ?>"><?= ucfirst($p['status']) ?></span>
            </div>
            <p class="text-muted"><?= $p['fecha'] ?> <?= $p['hora'] ?></p>
            <ul>
                <?php foreach (($p['items'] ?? []) as $item): ?>
                    <li><?= htmlspecialchars($item['producto_nombre'] ?? 'Producto') ?> x <?= $item['cantidad'] ?> — Bs <?= number_format($item['subtotal'],2) ?></li>
                <?php endforeach; ?>
            </ul>
            <p class="order-total-cat"><strong>Total: Bs <?= number_format(array_sum(array_column($p['items'] ?? [], 'subtotal')),2) ?></strong></p>
            <?php if ($p['status'] === 'pendiente'): ?>
                <p class="text-muted">Recoge tu pedido en tienda presentando el codigo.</p>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
