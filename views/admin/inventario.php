<?php global $viewData; $d = $viewData ?? []; ?>
<div class="card">
    <div class="card-header-custom">
        <h2>Inventario de Productos</h2>
        <div class="d-flex gap-10 flex-wrap">
            <a href="index.php?page=admin&module=inventario&stock_bajo=1" class="btn <?= !empty($d['stockBajo']) ? 'btn-danger' : 'btn-warning' ?> btn-sm">
                Stock Bajo (<?= $d['lowCount'] ?? 0 ?>)
            </a>
            <?php if (!empty($d['stockBajo'])): ?><a href="index.php?page=admin&module=inventario" class="btn btn-secondary btn-sm">Ver Todos</a><?php endif; ?>
            <button class="btn btn-primary" onclick="openModal('modalProducto')">+ Nuevo Producto</button>
        </div>
    </div>

    <form class="search-bar" method="GET">
        <input type="hidden" name="page" value="admin"><input type="hidden" name="module" value="inventario">
        <input type="text" name="buscar" class="form-control" id="inventorySearch" placeholder="Buscar por nombre, marca o codigo..." value="<?= htmlspecialchars($d['buscar'] ?? '') ?>">
        <button type="submit" class="btn btn-primary">Buscar</button>
        <?php if (!empty($d['buscar'])): ?><a href="index.php?page=admin&module=inventario" class="btn btn-secondary">Limpiar</a><?php endif; ?>
    </form>

    <div class="table-responsive">
        <table class="data-table">
            <thead><tr><th>ID</th><th>Img</th><th>Nombre</th><th>Categoria</th><th>Marca</th><th>Stock</th><th>P. Compra</th><th>P. Venta</th><th>Codigo</th><th>Estado</th><th>Acciones</th></tr></thead>
            <tbody>
            <?php foreach (($d['productos'] ?? []) as $p): ?>
            <tr class="<?= ($p['cantidad'] <= $p['umbral_alerta']) ? 'row-low-stock' : '' ?>">
                <td><?= $p['id'] ?></td>
                <td><?php if ($p['ruta_image']): ?><img src="<?= htmlspecialchars($p['ruta_image']) ?>" class="thumb"><?php else: ?>-<?php endif; ?></td>
                <td><strong><?= htmlspecialchars($p['nombre']) ?></strong></td>
                <td><?= htmlspecialchars($p['categoria'] ?? '-') ?></td>
                <td><?= htmlspecialchars($p['compania'] ?? '-') ?></td>
                <td><?= $p['cantidad'] ?></td>
                <td>Bs <?= number_format($p['precio_proveedor'],2) ?></td>
                <td><strong>Bs <?= number_format($p['precio_venta'],2) ?></strong></td>
                <td><code><?= htmlspecialchars($p['codigo_barras'] ?? '') ?></code></td>
                <td><span class="badge <?= $p['status']==='activo'?'badge-success':'badge-secondary' ?>"><?= $p['status'] ?></span></td>
                <td class="actions">
                    <a href="index.php?page=admin&module=inventario&editar=<?= $p['id'] ?>" class="btn btn-sm btn-primary">Editar</a>
                    <form method="POST" style="display:inline"><?= csrf_field() ?><input type="hidden" name="form_action" value="toggle"><input type="hidden" name="id" value="<?= $p['id'] ?>"><button type="submit" class="btn btn-sm btn-warning">Visib.</button></form>
                    <form method="POST" style="display:inline" onsubmit="return confirm('Eliminar este producto?')"><?= csrf_field() ?><input type="hidden" name="form_action" value="eliminar"><input type="hidden" name="id" value="<?= $p['id'] ?>"><button type="submit" class="btn btn-sm btn-danger">Elim.</button></form>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($d['productos'])): ?><tr><td colspan="11" class="text-center text-muted" style="padding:30px;">No hay productos</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if (($d['totalPages'] ?? 1) > 1): ?>
    <div class="pagination">
        <?php for ($i = 1; $i <= $d['totalPages']; $i++): ?>
            <a href="index.php?page=admin&module=inventario&p=<?= $i ?>&buscar=<?= urlencode($d['buscar']??'') ?><?= !empty($d['stockBajo'])?'&stock_bajo=1':'' ?>" class="<?= $i===$d['page']?'active':'' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>

<!-- Modal Producto -->
<div class="modal-overlay" id="modalProducto" <?php if(!empty($d['editando'])):?>style="display:flex"<?php endif;?>>
    <div class="modal">
        <div class="modal-header"><h3><?= !empty($d['editando'])?'Editar Producto':'Nuevo Producto' ?></h3><button class="modal-close" onclick="closeModal('modalProducto');<?php if(!empty($d['editando'])):?>location.href='index.php?page=admin&module=inventario'<?php endif;?>">&times;</button></div>
        <form method="POST" enctype="multipart/form-data" class="modal-body">
            <?= csrf_field() ?>
            <?php $e = $d['editando'] ?? null; ?>
            <input type="hidden" name="form_action" value="<?= $e?'editar':'crear' ?>">
            <?php if($e):?><input type="hidden" name="id" value="<?=$e['id']?>"><?php endif;?>
            <div class="form-row">
                <div class="form-group"><label>Nombre *</label><input type="text" name="nombre" class="form-control" required value="<?=htmlspecialchars($e['nombre']??'')?>"></div>
                <div class="form-group"><label>Codigo de Barras</label><input type="text" name="codigo_barras" class="form-control" value="<?=htmlspecialchars($e['codigo_barras']??'')?>"></div>
            </div>
            <div class="form-group"><label>Descripcion</label><textarea name="descripcion" class="form-control"><?=htmlspecialchars($e['descripcion']??'')?></textarea></div>
            <div class="form-row">
                <div class="form-group"><label>Cantidad *</label><input type="number" name="cantidad" class="form-control" required value="<?=$e['cantidad']??0?>"></div>
                <div class="form-group"><label>Umbral Alerta</label><input type="number" name="umbral_alerta" class="form-control" value="<?=$e['umbral_alerta']??5?>"></div>
            </div>
            <div class="form-row">
                <div class="form-group"><label>Precio Compra (Bs) *</label><input type="number" step="0.01" name="precio_proveedor" class="form-control" required value="<?=$e['precio_proveedor']??''?>"></div>
                <div class="form-group"><label>Precio Venta (Bs) *</label><input type="number" step="0.01" name="precio_venta" class="form-control" required value="<?=$e['precio_venta']??''?>"></div>
            </div>
            <div class="form-row">
                <div class="form-group"><label>Categoria</label><select name="id_categoria" class="form-control"><option value="">- Sin categoria -</option><?php foreach(($d['categorias']??[]) as $c):?><option value="<?=$c['id']?>" <?=($e['id_categoria']??'')==$c['id']?'selected':''?>><?=htmlspecialchars($c['nombre'])?></option><?php endforeach;?></select></div>
                <div class="form-group"><label>Marca/Compania</label><select name="id_compania" class="form-control"><option value="">- Sin marca -</option><?php foreach(($d['companias']??[]) as $c):?><option value="<?=$c['id']?>" <?=($e['id_compania']??'')==$c['id']?'selected':''?>><?=htmlspecialchars($c['nombre'])?></option><?php endforeach;?></select></div>
            </div>
            <div class="form-group"><label>Proveedor</label><select name="id_proveedor" class="form-control"><option value="">- Sin proveedor -</option><?php foreach(($d['proveedores']??[]) as $p):?><option value="<?=$p['id']?>" <?=($e['id_proveedor']??'')==$p['id']?'selected':''?>><?=htmlspecialchars($p['nombre'])?> <?=$p['compania']?"({$p['compania']})":''?></option><?php endforeach;?></select></div>
            <div class="form-group"><label>Imagen</label><input type="file" name="imagen" class="form-control" accept="image/*" data-preview="imgPreview">
                <?php if(!empty($e['ruta_image'])):?><img id="imgPreview" src="<?=htmlspecialchars($e['ruta_image'])?>" style="width:80px;margin-top:8px;border-radius:4px;"><?php else:?><img id="imgPreview" style="width:80px;margin-top:8px;display:none;"><?php endif;?>
            </div>
            <div class="modal-footer" style="padding:0;border:0;margin-top:16px;"><button type="button" class="btn btn-secondary" onclick="closeModal('modalProducto')">Cancelar</button><button type="submit" class="btn btn-primary">Guardar</button></div>
        </form>
    </div>
</div>
