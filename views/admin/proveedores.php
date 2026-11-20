<?php global $viewData; $d = $viewData ?? []; ?>
<div class="card">
    <div class="card-header-custom">
        <h2>Proveedores</h2>
        <button class="btn btn-primary" onclick="openModal('modalProv')">+ Nuevo Proveedor</button>
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead><tr><th>ID</th><th>Nombre</th><th>Compania/Marca</th><th>Pais</th><th>Telefono</th><th>Email</th><th>Direccion</th><th>Acciones</th></tr></thead>
            <tbody>
            <?php foreach(($d['proveedores']??[]) as $p): ?>
            <tr>
                <td><?=$p['id']?></td>
                <td><strong><?=htmlspecialchars($p['nombre'])?></strong></td>
                <td><?=htmlspecialchars($p['compania']??'-')?></td>
                <td><?=htmlspecialchars($p['pais_origen']??'-')?></td>
                <td><?=htmlspecialchars($p['telefono']??'-')?></td>
                <td><?=htmlspecialchars($p['email']??'-')?></td>
                <td><?=htmlspecialchars($p['direccion']??'-')?></td>
                <td class="actions">
                    <a href="index.php?page=admin&module=proveedores&editar=<?=$p['id']?>" class="btn btn-sm btn-primary">Editar</a>
                    <form method="POST" style="display:inline" onsubmit="return confirm('Eliminar este proveedor?')"><?=csrf_field()?><input type="hidden" name="form_action" value="eliminar"><input type="hidden" name="id" value="<?=$p['id']?>"><button class="btn btn-sm btn-danger">Eliminar</button></form>
                </td>
            </tr>
            <?php endforeach;?>
            <?php if(empty($d['proveedores'])):?><tr><td colspan="8" class="text-center text-muted" style="padding:30px;">No hay proveedores</td></tr><?php endif;?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal-overlay" id="modalProv" <?php if(!empty($d['editando'])):?>style="display:flex"<?php endif;?>>
    <div class="modal">
        <div class="modal-header"><h3><?=!empty($d['editando'])?'Editar Proveedor':'Nuevo Proveedor'?></h3><button class="modal-close" onclick="closeModal('modalProv');<?php if(!empty($d['editando'])):?>location.href='index.php?page=admin&module=proveedores'<?php endif;?>">&times;</button></div>
        <form method="POST" class="modal-body">
            <?=csrf_field()?>
            <?php $e=$d['editando']??null;?>
            <input type="hidden" name="form_action" value="<?=$e?'editar':'crear'?>">
            <?php if($e):?><input type="hidden" name="id" value="<?=$e['id']?>"><?php endif;?>
            <div class="form-group"><label>Nombre del Proveedor *</label><input type="text" name="nombre" class="form-control" required value="<?=htmlspecialchars($e['nombre']??'')?>"></div>
            <div class="form-group"><label>Compania/Marca existente</label>
                <select name="id_compania" class="form-control" id="selectCompania">
                    <option value="">- Seleccionar o crear nueva abajo -</option>
                    <?php foreach(($d['companias']??[]) as $c):?><option value="<?=$c['id']?>" <?=($e['id_compania']??'')==$c['id']?'selected':''?>><?=htmlspecialchars($c['nombre'])?></option><?php endforeach;?>
                </select>
            </div>
            <div class="form-row">
                <div class="form-group"><label>O crear nueva Compania</label><input type="text" name="nueva_compania" class="form-control" placeholder="Nombre de nueva compania"></div>
                <div class="form-group"><label>Pais de origen</label><input type="text" name="pais_origen" class="form-control" placeholder="Ej: Bolivia"></div>
            </div>
            <div class="form-row">
                <div class="form-group"><label>Telefono</label><input type="text" name="telefono" class="form-control" value="<?=htmlspecialchars($e['telefono']??'')?>"></div>
                <div class="form-group"><label>Email</label><input type="email" name="email" class="form-control" value="<?=htmlspecialchars($e['email']??'')?>"></div>
            </div>
            <div class="form-group"><label>Direccion</label><input type="text" name="direccion" class="form-control" value="<?=htmlspecialchars($e['direccion']??'')?>"></div>
            <div class="modal-footer" style="padding:0;border:0;margin-top:16px;"><button type="button" class="btn btn-secondary" onclick="closeModal('modalProv')">Cancelar</button><button type="submit" class="btn btn-primary">Guardar</button></div>
        </form>
    </div>
</div>
