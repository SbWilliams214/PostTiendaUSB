<?php global $viewData; $d = $viewData ?? []; ?>
<div class="card">
    <div class="card-header-custom">
        <h2>Gestion de Empleados</h2>
        <button class="btn btn-primary" onclick="openModal('modalUsuario')">+ Nuevo Empleado</button>
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead><tr><th>ID</th><th>Nombre</th><th>Usuario</th><th>Email</th><th>Telefono</th><th>Rol</th><th>Estado</th><th>Acciones</th></tr></thead>
            <tbody>
            <?php foreach(($d['empleados']??[]) as $e): ?>
            <tr>
                <td><?=$e['id']?></td>
                <td><strong><?=htmlspecialchars($e['nombre'])?></strong></td>
                <td><?=htmlspecialchars($e['username']??'-')?></td>
                <td><?=htmlspecialchars($e['email']??'-')?></td>
                <td><?=htmlspecialchars($e['telefono']??'-')?></td>
                <td><span class="badge badge-info"><?=htmlspecialchars($e['rol'])?></span></td>
                <td><span class="badge <?=$e['status']==='activo'?'badge-success':'badge-secondary'?>"><?=$e['status']?></span></td>
                <td class="actions">
                    <a href="index.php?page=admin&module=usuarios&editar=<?=$e['id']?>" class="btn btn-sm btn-primary">Editar</a>
                    <form method="POST" style="display:inline"><?=csrf_field()?><input type="hidden" name="form_action" value="toggle"><input type="hidden" name="id" value="<?=$e['id']?>"><button class="btn btn-sm btn-warning">Visib.</button></form>
                </td>
            </tr>
            <?php endforeach;?>
            <?php if(empty($d['empleados'])):?><tr><td colspan="8" class="text-center text-muted" style="padding:30px;">No hay empleados</td></tr><?php endif;?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal-overlay" id="modalUsuario" <?php if(!empty($d['editando'])):?>style="display:flex"<?php endif;?>>
    <div class="modal">
        <div class="modal-header"><h3><?=!empty($d['editando'])?'Editar Empleado':'Nuevo Empleado'?></h3><button class="modal-close" onclick="closeModal('modalUsuario');<?php if(!empty($d['editando'])):?>location.href='index.php?page=admin&module=usuarios'<?php endif;?>">&times;</button></div>
        <form method="POST" class="modal-body">
            <?=csrf_field()?>
            <?php $e=$d['editando']??null;?>
            <input type="hidden" name="form_action" value="<?=$e?'editar':'crear'?>">
            <?php if($e):?><input type="hidden" name="id" value="<?=$e['id']?>"><?php endif;?>
            <div class="form-group"><label>Nombre Completo *</label><input type="text" name="nombre" class="form-control" required value="<?=htmlspecialchars($e['nombre']??'')?>"></div>
            <div class="form-row">
                <div class="form-group"><label>Username *</label><input type="text" name="username" class="form-control" required value="<?=htmlspecialchars($e['username']??'')?>"></div>
                <div class="form-group"><label>Email</label><input type="email" name="email" class="form-control" value="<?=htmlspecialchars($e['email']??'')?>"></div>
            </div>
            <div class="form-row">
                <div class="form-group"><label>Telefono</label><input type="text" name="telefono" class="form-control" value="<?=htmlspecialchars($e['telefono']??'')?>"></div>
                <div class="form-group"><label>Rol *</label><select name="id_rol" class="form-control" required><?php foreach(($d['roles']??[]) as $r):?><option value="<?=$r['id']?>" <?=($e['id_rol']??'')==$r['id']?'selected':''?>><?=htmlspecialchars($r['nombre'])?></option><?php endforeach;?></select></div>
            </div>
            <div class="form-group"><label>Contrasena <?=$e?'(dejar vacio para no cambiar)':'*'?></label><input type="password" name="password" class="form-control" <?=$e?'':'required'?>></div>
            <div class="modal-footer" style="padding:0;border:0;margin-top:16px;"><button type="button" class="btn btn-secondary" onclick="closeModal('modalUsuario')">Cancelar</button><button type="submit" class="btn btn-primary">Guardar</button></div>
        </form>
    </div>
</div>
