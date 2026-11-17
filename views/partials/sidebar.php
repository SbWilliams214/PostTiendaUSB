<?php $cm = $_GET['module'] ?? 'inventario'; ?>
<aside class="admin-sidebar" id="adminSidebar">
    <ul class="sidebar-nav">
        <li><a href="index.php?page=admin&module=inventario" class="<?=$cm==='inventario'?'active':''?>"><span class="nav-icon">Inv</span> Inventario</a></li>
        <li><a href="index.php?page=admin&module=categorias" class="<?=$cm==='categorias'?'active':''?>"><span class="nav-icon">Cat</span> Categorias</a></li>
        <li><a href="index.php?page=admin&module=proveedores" class="<?=$cm==='proveedores'?'active':''?>"><span class="nav-icon">Prov</span> Proveedores</a></li>
        <li><a href="index.php?page=admin&module=usuarios" class="<?=$cm==='usuarios'?'active':''?>"><span class="nav-icon">Usr</span> Usuarios</a></li>
        <li><a href="index.php?page=admin&module=caja" class="<?=$cm==='caja'?'active':''?>"><span class="nav-icon">Caja</span> Caja</a></li>
        <li><a href="index.php?page=admin&module=pedidos" class="<?=$cm==='pedidos'?'active':''?>"><span class="nav-icon">Ped</span> Pedidos Web</a></li>
        <li><a href="index.php?page=admin&module=promociones" class="<?=$cm==='promociones'?'active':''?>"><span class="nav-icon">Prm</span> Promociones</a></li>
        <li><a href="index.php?page=admin&module=configuraciones" class="<?=$cm==='configuraciones'?'active':''?>"><span class="nav-icon">Cfg</span> Configuraciones</a></li>
        <li><a href="index.php?page=admin&module=reportes" class="<?=$cm==='reportes'?'active':''?>"><span class="nav-icon">Rep</span> Reportes</a></li>
    </ul>
</aside>
