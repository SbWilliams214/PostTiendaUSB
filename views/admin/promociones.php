<?php /** Promociones */ global $viewData; $d = $viewData ?? []; $editando = $d['editando'] ?? null; ?>
<div class="card">
    <div class="card-header-custom">
        <h2>Promociones</h2>
        <button class="btn btn-primary" onclick="openModal('modalPromo')">+ Nueva Promocion</button>
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead><tr><th>ID</th><th>Imagen</th><th>Nombre</th><th>Productos</th><th>Precio</th><th>Estado</th><th>Plataforma Web</th><th>Acciones</th></tr></thead>
            <tbody>
            <?php foreach(($d['promociones']??[]) as $p): ?>
            <tr>
                <td><?=$p['id']?></td>
                <td><?php if($p['ruta_imagen']):?><img src="<?=htmlspecialchars($p['ruta_imagen'])?>" class="thumb"><?php else:?>--<?php endif;?></td>
                <td><strong><?=htmlspecialchars($p['nombre'])?></strong><br><small class="text-muted"><?=htmlspecialchars(substr($p['descripcion']??'',0,60))?></small></td>
                <td>
                    <?php if(!empty($p['detalle_productos'])):?>
                        <?php foreach($p['detalle_productos'] as $dp):?>
                            <span class="badge badge-info" style="margin:2px;"><?=htmlspecialchars($dp['producto_nombre']??'')?> (Bs <?=number_format($dp['precio_unitario_promo'],2)?>)</span>
                        <?php endforeach;?>
                    <?php else:?>
                        <span class="text-muted">Sin productos</span>
                    <?php endif;?>
                </td>
                <td>Bs <?=number_format($p['precio'],2)?></td>
                <td>
                    <form method="POST" style="display:inline"><?=csrf_field()?><input type="hidden" name="form_action" value="toggle_status"><input type="hidden" name="id" value="<?=$p['id']?>">
                    <button class="badge <?=$p['status']==='activo'?'badge-success':'badge-secondary'?>" style="cursor:pointer;border:0;" title="Click para cambiar"><?=$p['status']?></button></form>
                </td>
                <td>
                    <form method="POST" style="display:inline"><?=csrf_field()?><input type="hidden" name="form_action" value="toggle_plat"><input type="hidden" name="id" value="<?=$p['id']?>">
                    <button class="badge <?=$p['status_plataforma']==='activo'?'badge-success':'badge-secondary'?>" style="cursor:pointer;border:0;" title="Click para cambiar"><?=$p['status_plataforma']?></button></form>
                </td>
                <td style="white-space:nowrap;">
                    <a href="index.php?page=admin&module=promociones&editar=<?=$p['id']?>" class="btn btn-sm btn-primary">Editar</a>
                    <form method="POST" style="display:inline" onsubmit="return confirm('Eliminar esta promocion?')"><?=csrf_field()?><input type="hidden" name="form_action" value="eliminar"><input type="hidden" name="id" value="<?=$p['id']?>"><button class="btn btn-sm btn-danger">Eliminar</button></form>
                </td>
            </tr>
            <?php endforeach;?>
            <?php if(empty($d['promociones'])):?><tr><td colspan="8" class="text-center text-muted" style="padding:30px;">No hay promociones</td></tr><?php endif;?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Crear/Editar Promocion -->
<div class="modal-overlay <?=$editando?'show':''?>" id="modalPromo">
    <div class="modal" style="max-width:600px;">
        <div class="modal-header"><h3><?=$editando?'Editar':'Nueva'?> Promocion</h3><button class="modal-close" onclick="<?=$editando?"location='index.php?page=admin&module=promociones'":"closeModal('modalPromo')"?>">X</button></div>
        <form method="POST" enctype="multipart/form-data" class="modal-body">
            <?=csrf_field()?>
            <?php if($editando):?>
                <input type="hidden" name="form_action" value="editar">
                <input type="hidden" name="id" value="<?=$editando['id']?>">
            <?php else:?>
                <input type="hidden" name="form_action" value="crear">
            <?php endif;?>
            <div class="form-group"><label>Nombre *</label><input type="text" name="nombre" class="form-control" required value="<?=htmlspecialchars($editando['nombre']??'')?>"></div>
            <div class="form-group"><label>Descripcion</label><textarea name="descripcion" class="form-control"><?=htmlspecialchars($editando['descripcion']??'')?></textarea></div>
            <div class="form-row">
                <div class="form-group"><label>Precio Promocion *</label><input type="number" step="0.01" name="precio" class="form-control" required value="<?=htmlspecialchars($editando['precio']??'')?>"></div>
                <div class="form-group"><label>Estado</label><select name="status" class="form-control"><option value="activo" <?=($editando['status']??'activo')==='activo'?'selected':''?>>Activo</option><option value="inactivo" <?=($editando['status']??'')==='inactivo'?'selected':''?>>Inactivo</option></select></div>
            </div>
            <div class="form-row">
                <div class="form-group"><label>Visible en Web</label><select name="status_plataforma" class="form-control"><option value="inactivo" <?=($editando['status_plataforma']??'inactivo')==='inactivo'?'selected':''?>>No</option><option value="activo" <?=($editando['status_plataforma']??'')==='activo'?'selected':''?>>Si</option></select></div>
                <div class="form-group"><label>Imagen</label><input type="file" name="imagen" class="form-control" accept="image/*"></div>
            </div>

            <!-- Productos de la promocion -->
            <div class="form-group" style="margin-top:12px;">
                <label><strong>Productos incluidos en la promocion</strong></label>
                <div id="promoProductos">
                    <?php if($editando && !empty($editando['detalle_productos'])): foreach($editando['detalle_productos'] as $dp):?>
                    <div class="promo-prod-row" style="display:flex;gap:8px;margin-bottom:6px;align-items:center;">
                        <select name="prod_ids[]" class="form-control" style="flex:2;">
                            <option value="">-- Seleccionar --</option>
                            <?php foreach(($d['productos']??[]) as $prod):?>
                            <option value="<?=$prod['id']?>" <?=$prod['id']==$dp['id_producto']?'selected':''?>><?=htmlspecialchars($prod['nombre'])?> (Bs <?=number_format($prod['precio_venta'],2)?>)</option>
                            <?php endforeach;?>
                        </select>
                        <input type="number" step="0.01" name="prod_precios[]" class="form-control" style="flex:1;" placeholder="Precio promo" value="<?=number_format($dp['precio_unitario_promo'],2)?>">
                        <button type="button" class="btn btn-sm btn-danger" onclick="this.parentElement.remove()">X</button>
                    </div>
                    <?php endforeach; endif;?>
                </div>
                <button type="button" class="btn btn-sm btn-secondary" onclick="addPromoProduct()" style="margin-top:6px;">+ Agregar Producto</button>
            </div>

            <div class="modal-footer" style="padding:0;border:0;margin-top:16px;"><button type="button" class="btn btn-secondary" onclick="<?=$editando?"location='index.php?page=admin&module=promociones'":"closeModal('modalPromo')"?>">Cancelar</button><button type="submit" class="btn btn-primary">Guardar</button></div>
        </form>
    </div>
</div>

<script>
var productOptions = '';
<?php foreach(($d['productos']??[]) as $prod):?>
productOptions += '<option value="<?=$prod['id']?>"><?=addslashes(htmlspecialchars($prod['nombre']))?> (Bs <?=number_format($prod['precio_venta'],2)?>)</option>';
<?php endforeach;?>

function addPromoProduct() {
    var row = document.createElement('div');
    row.className = 'promo-prod-row';
    row.style.cssText = 'display:flex;gap:8px;margin-bottom:6px;align-items:center;';
    row.innerHTML = '<select name="prod_ids[]" class="form-control" style="flex:2;"><option value="">-- Seleccionar --</option>' + productOptions + '</select>'
        + '<input type="number" step="0.01" name="prod_precios[]" class="form-control" style="flex:1;" placeholder="Precio promo">'
        + '<button type="button" class="btn btn-sm btn-danger" onclick="this.parentElement.remove()">X</button>';
    document.getElementById('promoProductos').appendChild(row);
}
</script>
