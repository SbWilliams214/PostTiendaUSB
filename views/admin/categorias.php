<?php global $viewData; $d = $viewData ?? []; ?>
<div class="card">
    <div class="card-header-custom">
        <h2>Categorias</h2>
        <button class="btn btn-primary" onclick="openModal('modalCat')">+ Nueva Categoria</button>
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead><tr><th>ID</th><th>Nombre</th><th>Descripcion</th><th>Productos</th><th>Acciones</th></tr></thead>
            <tbody>
            <?php foreach(($d['categorias']??[]) as $c): ?>
            <tr>
                <td><?=$c['id']?></td>
                <td><strong><?=htmlspecialchars($c['nombre'])?></strong></td>
                <td><?=htmlspecialchars($c['descripcion']??'-')?></td>
                <td><?=$c['total_productos']?></td>
                <td class="actions">
                    <a href="index.php?page=admin&module=categorias&editar=<?=$c['id']?>" class="btn btn-sm btn-primary">Editar</a>
                    <form method="POST" style="display:inline" onsubmit="return confirm('Eliminar esta categoria?')"><?=csrf_field()?><input type="hidden" name="form_action" value="eliminar"><input type="hidden" name="id" value="<?=$c['id']?>"><button class="btn btn-sm btn-danger">Eliminar</button></form>
                </td>
            </tr>
            <?php endforeach;?>
            <?php if(empty($d['categorias'])):?><tr><td colspan="5" class="text-center text-muted" style="padding:30px;">No hay categorias</td></tr><?php endif;?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal-overlay" id="modalCat" <?php if(!empty($d['editando'])):?>style="display:flex"<?php endif;?>>
    <div class="modal">
        <div class="modal-header"><h3><?=!empty($d['editando'])?'Editar Categoria':'Nueva Categoria'?></h3><button class="modal-close" onclick="closeModal('modalCat');<?php if(!empty($d['editando'])):?>location.href='index.php?page=admin&module=categorias'<?php endif;?>">&times;</button></div>
        <form method="POST" class="modal-body">
            <?=csrf_field()?>
            <?php $e=$d['editando']??null;?>
            <input type="hidden" name="form_action" value="<?=$e?'editar':'crear'?>">
            <?php if($e):?><input type="hidden" name="id" value="<?=$e['id']?>"><?php endif;?>
            <div class="form-group"><label>Nombre *</label><input type="text" name="nombre" class="form-control" required value="<?=htmlspecialchars($e['nombre']??'')?>"></div>
            <div class="form-group"><label>Descripcion</label><textarea name="descripcion" class="form-control"><?=htmlspecialchars($e['descripcion']??'')?></textarea></div>
            <div class="modal-footer" style="padding:0;border:0;margin-top:16px;"><button type="button" class="btn btn-secondary" onclick="closeModal('modalCat')">Cancelar</button><button type="submit" class="btn btn-primary">Guardar</button></div>
        </form>
    </div>
</div>
