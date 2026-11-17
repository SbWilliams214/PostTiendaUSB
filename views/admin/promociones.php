<?php /** Promociones */ global $viewData; $d = $viewData ?? []; ?>
<div class="card">
    <div class="card-header-custom">
        <h2>Promociones</h2>
        <button class="btn btn-primary" onclick="openModal('modalPromo')">+ Nueva Promocion</button>
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead><tr><th>ID</th><th>Imagen</th><th>Nombre</th><th>Precio</th><th>Estado</th><th>Plataforma Web</th><th>Acciones</th></tr></thead>
            <tbody>
            <?php foreach(($d['promociones']??[]) as $p): ?>
            <tr>
                <td><?=$p['id']?></td>
                <td><?php if($p['ruta_imagen']):?><img src="<?=htmlspecialchars($p['ruta_imagen'])?>" class="thumb"><?php else:?>--<?php endif;?></td>
                <td><strong><?=htmlspecialchars($p['nombre'])?></strong><br><small class="text-muted"><?=htmlspecialchars(substr($p['descripcion']??'',0,60))?></small></td>
                <td>Bs <?=number_format($p['precio'],2)?></td>
                <td><span class="badge <?=$p['status']==='activo'?'badge-success':'badge-secondary'?>"><?=$p['status']?></span></td>
                <td>
                    <form method="POST" style="display:inline"><?=csrf_field()?><input type="hidden" name="form_action" value="toggle_plat"><input type="hidden" name="id" value="<?=$p['id']?>">
                    <button class="badge <?=$p['status_plataforma']==='activo'?'badge-success':'badge-secondary'?>" style="cursor:pointer;border:0;" title="Click para cambiar"><?=$p['status_plataforma']?></button></form>
                </td>
                <td><form method="POST" style="display:inline" onsubmit="return confirm('Eliminar esta promocion?')"><?=csrf_field()?><input type="hidden" name="form_action" value="eliminar"><input type="hidden" name="id" value="<?=$p['id']?>"><button class="btn btn-sm btn-danger">Eliminar</button></form></td>
            </tr>
            <?php endforeach;?>
            <?php if(empty($d['promociones'])):?><tr><td colspan="7" class="text-center text-muted" style="padding:30px;">No hay promociones</td></tr><?php endif;?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal-overlay" id="modalPromo">
    <div class="modal">
        <div class="modal-header"><h3>Nueva Promocion</h3><button class="modal-close" onclick="closeModal('modalPromo')">X</button></div>
        <form method="POST" enctype="multipart/form-data" class="modal-body">
            <?=csrf_field()?><input type="hidden" name="form_action" value="crear">
            <div class="form-group"><label>Nombre *</label><input type="text" name="nombre" class="form-control" required></div>
            <div class="form-group"><label>Descripcion</label><textarea name="descripcion" class="form-control"></textarea></div>
            <div class="form-row">
                <div class="form-group"><label>Precio *</label><input type="number" step="0.01" name="precio" class="form-control" required></div>
                <div class="form-group"><label>Estado</label><select name="status" class="form-control"><option value="activo">Activo</option><option value="inactivo">Inactivo</option></select></div>
            </div>
            <div class="form-row">
                <div class="form-group"><label>Visible en Web</label><select name="status_plataforma" class="form-control"><option value="inactivo">No</option><option value="activo">Si</option></select></div>
                <div class="form-group"><label>Imagen</label><input type="file" name="imagen" class="form-control" accept="image/*"></div>
            </div>
            <div class="modal-footer" style="padding:0;border:0;margin-top:16px;"><button type="button" class="btn btn-secondary" onclick="closeModal('modalPromo')">Cancelar</button><button type="submit" class="btn btn-primary">Guardar</button></div>
        </form>
    </div>
</div>
