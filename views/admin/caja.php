<?php global $viewData; $d = $viewData ?? []; $cajaAbierta = $d['caja_abierta'] ?? null; $cajaCierre = $d['caja_cierre'] ?? null; ?>

<?php if ($cajaCierre): ?>
<!-- Resumen de cierre de caja -->
<div class="card" style="max-width:500px;margin:40px auto;">
    <h2 style="margin-bottom:16px;">Resumen de Cierre de Caja</h2>
    <table class="data-table">
        <tr><td><strong>Monto Inicial</strong></td><td>Bs <?=number_format($cajaCierre['monto_inicial'],2)?></td></tr>
        <?php foreach($cajaCierre['desglose'] as $d_item):?>
        <tr><td><strong><?=htmlspecialchars($d_item['metodo'])?></strong></td><td>Bs <?=number_format($d_item['total'],2)?></td></tr>
        <?php endforeach;?>
        <tr style="border-top:2px solid #1A3B8B;"><td><strong>Total Ventas</strong></td><td><strong>Bs <?=number_format($cajaCierre['total_ventas'],2)?></strong></td></tr>
        <tr><td><strong>Monto Final (Inicial + Ventas)</strong></td><td><strong style="color:#27ae60;">Bs <?=number_format($cajaCierre['monto_final'],2)?></strong></td></tr>
    </table>
    <p style="margin-top:16px;text-align:center;"><a href="index.php?page=admin&module=caja" class="btn btn-primary">Aceptar</a></p>
</div>

<?php elseif (!$cajaAbierta): ?>
<!-- Formulario para abrir caja -->
<div class="card" style="max-width:450px;margin:60px auto;">
    <h2 style="margin-bottom:16px;">Abrir Caja</h2>
    <p class="text-muted">Ingresa el monto inicial con el que comienzas tu turno.</p>
    <form method="POST">
        <?=csrf_field()?><input type="hidden" name="form_action" value="abrir_caja">
        <div class="form-group">
            <label>Monto Inicial (Bs)</label>
            <input type="number" step="0.01" name="monto_inicial" class="form-control" required min="0" placeholder="0.00">
        </div>
        <button type="submit" class="btn btn-primary" style="width:100%;margin-top:12px;">Abrir Caja</button>
    </form>
</div>

<?php else: ?>
<!-- POS Interface - Caja abierta -->
<div class="card" style="margin-bottom:0;padding:10px 16px;">
    <div style="display:flex;justify-content:space-between;align-items:center;">
        <h2 style="margin:0;">Caja</h2>
        <div style="display:flex;align-items:center;gap:12px;">
            <span class="badge badge-success">Abierta desde <?=$cajaAbierta['hora_apertura']?></span>
            <span class="text-muted">Monto inicial: Bs <?=number_format($cajaAbierta['monto_inicial'],2)?></span>
            <form method="POST" style="display:inline;" onsubmit="return confirm('Cerrar la caja? Se generara el resumen.')">
                <?=csrf_field()?><input type="hidden" name="form_action" value="cerrar_caja"><input type="hidden" name="caja_id" value="<?=$cajaAbierta['id']?>">
                <button type="submit" class="btn btn-sm btn-danger">Cerrar Caja</button>
            </form>
        </div>
    </div>
</div>

<div class="pos-wrapper">
    <div class="pos-left">
        <div class="pos-search">
            <input type="text" id="posSearchInput" class="form-control" autofocus
                   placeholder="Buscar producto o escanear codigo de barras...">
        </div>

        <!-- Promotions in POS -->
        <?php if (!empty($d['promociones'])): ?>
        <div style="padding:8px 0;">
            <h4 style="margin:0 0 8px 4px;color:#9E1B22;">Promociones</h4>
            <div class="pos-results" style="margin-bottom:12px;">
                <?php foreach ($d['promociones'] as $pr): ?>
                <div class="pos-product-card pos-promo-card" onclick="addPromoToCart(<?=$pr['id']?>, '<?=addslashes(htmlspecialchars($pr['nombre']))?>', <?=$pr['precio']?>)" style="border:2px solid #9E1B22;">
                    <?php if ($pr['ruta_imagen']): ?>
                        <img src="<?=htmlspecialchars($pr['ruta_imagen'])?>" class="ppc-img" alt="">
                    <?php else: ?>
                        <div class="ppc-img" style="background:#fdf2f2;display:flex;align-items:center;justify-content:center;color:#9E1B22;font-weight:bold;">PROMO</div>
                    <?php endif; ?>
                    <span class="ppc-name"><?=htmlspecialchars($pr['nombre'])?></span>
                    <?php if (!empty($pr['descripcion'])): ?>
                        <span style="font-size:.75rem;color:#555;display:block;margin-top:2px;"><?=htmlspecialchars($pr['descripcion'])?></span>
                    <?php endif; ?>
                    <span class="ppc-price" style="color:#9E1B22;">Bs <?=number_format($pr['precio'],2)?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <h4 style="margin:0 0 8px 4px;">Productos</h4>
        <div class="pos-results" id="posResults">
            <?php if (!empty($d['productos'])): ?>
                <?php foreach ($d['productos'] as $p): ?>
                <div class="pos-product-card" data-name="<?=strtolower($p['nombre'])?>" data-barcode="<?=strtolower($p['codigo_barras']??'')?>" onclick="addToCart(<?= $p['id'] ?>, '<?= addslashes(htmlspecialchars($p['nombre'])) ?>', <?= $p['precio_venta'] ?>, <?= $p['cantidad'] ?>)">
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
            <button class="btn btn-sm btn-secondary" onclick="clearPosCart()">Limpiar</button>
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
<?php endif; ?>
