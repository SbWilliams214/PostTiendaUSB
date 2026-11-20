<?php
$cm = $_GET['module'] ?? 'inventario';
$rol = $_SESSION['user_role'] ?? '';
$isAdmin = ($rol === 'Admin');
?>
<aside class="admin-sidebar" id="adminSidebar">
    <ul class="sidebar-nav">
        <?php if ($isAdmin): ?>
        <li><a href="index.php?page=admin&module=inventario" class="<?=$cm==='inventario'?'active':''?>">Inventario</a></li>
        <li><a href="index.php?page=admin&module=categorias" class="<?=$cm==='categorias'?'active':''?>">Categorias</a></li>
        <li><a href="index.php?page=admin&module=proveedores" class="<?=$cm==='proveedores'?'active':''?>">Proveedores</a></li>
        <li><a href="index.php?page=admin&module=usuarios" class="<?=$cm==='usuarios'?'active':''?>">Usuarios</a></li>
        <?php endif; ?>
        <li><a href="index.php?page=admin&module=caja" class="<?=$cm==='caja'?'active':''?>">Caja</a></li>
        <li><a href="index.php?page=admin&module=pedidos" class="<?=$cm==='pedidos'?'active':''?>">Pedidos Web</a></li>
        <?php if ($isAdmin): ?>
        <li><a href="index.php?page=admin&module=promociones" class="<?=$cm==='promociones'?'active':''?>">Promociones</a></li>
        <li><a href="index.php?page=admin&module=configuraciones" class="<?=$cm==='configuraciones'?'active':''?>">Configuraciones</a></li>
        <li><a href="index.php?page=admin&module=reportes" class="<?=$cm==='reportes'?'active':''?>">Reportes</a></li>
        <?php endif; ?>
    </ul>
</aside>
