<?php /** Reportes */ global $viewData; $d = $viewData ?? []; ?>
<div class="card">
    <h2 style="margin-bottom:20px;">Reportes</h2>

    <form class="report-controls" method="GET">
        <input type="hidden" name="page" value="admin"><input type="hidden" name="module" value="reportes">
        <div class="form-group"><label>Tipo de Reporte</label>
            <select name="generar" class="form-control">
                <option value="ventas_diarias" <?=($d['tipo']??'')==='ventas_diarias'?'selected':''?>>Ventas Diarias</option>
                <option value="inventario" <?=($d['tipo']??'')==='inventario'?'selected':''?>>Inventario</option>
                <option value="ganancias" <?=($d['tipo']??'')==='ganancias'?'selected':''?>>Ganancias</option>
            </select>
        </div>
        <div class="form-group"><label>Empleado</label>
            <select name="empleado" class="form-control">
                <option value="0">Todos los empleados (Global)</option>
                <?php foreach (($d['empleados'] ?? []) as $emp): ?>
                    <option value="<?= $emp['id'] ?>" <?= ($d['empleado_filtro'] ?? 0) == $emp['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($emp['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group"><label>Desde</label><input type="date" name="desde" class="form-control" value="<?=htmlspecialchars($d['desde']??date('Y-m-d',strtotime('-30 days')))?>"></div>
        <div class="form-group"><label>Hasta</label><input type="date" name="hasta" class="form-control" value="<?=htmlspecialchars($d['hasta']??date('Y-m-d'))?>"></div>
        <div class="form-group"><label>&nbsp;</label><button type="submit" class="btn btn-primary">Generar</button></div>
    </form>

    <?php if ($d['reportData'] !== null): ?>
    <div class="report-results">
        <?php if ($d['tipo'] === 'ventas_diarias'): ?>
            <h3>Ventas Diarias (<?=htmlspecialchars($d['desde'])?> - <?=htmlspecialchars($d['hasta'])?>)</h3>
            <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Hora</th>
                        <th>Codigo Pedido</th>
                        <th>Tipo</th>
                        <th>Empleado</th>
                        <th>Estado</th>
                        <th>Producto</th>
                        <th>Marca</th>
                        <th>Cantidad</th>
                        <th>Precio Unit.</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                <?php if(!empty($d['reportData'])): ?>
                    <?php foreach($d['reportData'] as $r): ?>
                    <tr>
                        <td><?=htmlspecialchars($r['fecha'])?></td>
                        <td><?=htmlspecialchars($r['hora'])?></td>
                        <td><strong><?=htmlspecialchars($r['codigo_pedido'])?></strong></td>
                        <td><?=htmlspecialchars($r['tipo_pedido']??'—')?></td>
                        <td><?=htmlspecialchars($r['empleado_nombre']??'—')?></td>
                        <td><span class="badge badge-success"><?=htmlspecialchars($r['status'])?></span></td>
                        <td><?=htmlspecialchars($r['producto_nombre']??'—')?></td>
                        <td><?=htmlspecialchars($r['marca']??'—')?></td>
                        <td><?=$r['cantidad']?></td>
                        <td>Bs <?=number_format($r['precio_unitario'],2)?></td>
                        <td><strong>Bs <?=number_format($r['subtotal'],2)?></strong></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="11" class="text-center text-muted">Sin datos para este periodo</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
            </div>

        <?php elseif ($d['tipo'] === 'inventario'): ?>
            <h3>Reporte de Inventario</h3>
            <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr><th>Producto</th><th>Categoria</th><th>Marca</th><th>Stock</th><th>P. Compra</th><th>P. Venta</th><th>Estado</th></tr>
                </thead>
                <tbody>
                <?php foreach($d['reportData'] as $r): ?>
                <tr class="<?=$r['cantidad']<=$r['umbral_alerta']?'row-low-stock':''?>">
                    <td><?=htmlspecialchars($r['nombre'])?></td>
                    <td><?=htmlspecialchars($r['categoria']??'--')?></td>
                    <td><?=htmlspecialchars($r['compania']??'--')?></td>
                    <td><?=$r['cantidad']?></td>
                    <td>Bs <?=number_format($r['precio_proveedor'],2)?></td>
                    <td>Bs <?=number_format($r['precio_venta'],2)?></td>
                    <td><span class="badge <?=$r['status']==='activo'?'badge-success':'badge-secondary'?>"><?=$r['status']?></span></td>
                </tr>
                <?php endforeach; ?>
                <?php if(empty($d['reportData'])): ?>
                    <tr><td colspan="7" class="text-center text-muted">Sin datos</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
            </div>

        <?php elseif ($d['tipo'] === 'ganancias'): ?>
            <h3>Reporte de Ganancias (<?=htmlspecialchars($d['desde'])?> - <?=htmlspecialchars($d['hasta'])?>)</h3>
            <div class="summary-cards" style="margin-bottom:20px;">
                <div class="summary-card accent-green">
                    <div class="sc-body">
                        <h3>Bs <?=number_format($d['totalGanancia']??0,2)?></h3>
                        <p>Ganancia total del periodo</p>
                    </div>
                </div>
            </div>
            <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Hora</th>
                        <th>Codigo Pedido</th>
                        <th>Tipo</th>
                        <th>Empleado</th>
                        <th>Producto</th>
                        <th>Cant.</th>
                        <th>P. Compra</th>
                        <th>P. Venta</th>
                        <th>Ganancia</th>
                    </tr>
                </thead>
                <tbody>
                <?php if(!empty($d['reportData'])): ?>
                    <?php foreach($d['reportData'] as $r): ?>
                    <tr>
                        <td><?=htmlspecialchars($r['fecha'])?></td>
                        <td><?=htmlspecialchars($r['hora'])?></td>
                        <td><strong><?=htmlspecialchars($r['codigo_pedido'])?></strong></td>
                        <td><?=htmlspecialchars($r['tipo_pedido']??'--')?></td>
                        <td><?=htmlspecialchars($r['empleado_nombre']??'--')?></td>
                        <td><?=htmlspecialchars($r['producto_nombre']??'--')?></td>
                        <td><?=$r['cantidad']?></td>
                        <td>Bs <?=number_format($r['precio_proveedor']??0,2)?></td>
                        <td>Bs <?=number_format($r['precio_unitario'],2)?></td>
                        <td><strong style="color:<?=($r['ganancia_item']??0)>=0?'#27ae60':'#e74c3c'?>">Bs <?=number_format($r['ganancia_item']??0,2)?></strong></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="10" class="text-center text-muted">Sin datos para este periodo</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
            </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>
