<?php $isAdmin = ($_GET['page'] ?? '') === 'admin'; $isCatalog = !$isAdmin;
$cssFile = $isAdmin ? 'assets/css/admin.css' : 'assets/css/catalogo.css'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $isAdmin ? 'Sistema POS' : 'Tienda Online' ?></title>
    <link rel="stylesheet" href="<?= $cssFile ?>">
</head>
<body>
<?php if ($isAdmin): ?>
<nav class="admin-topnav">
    <div style="display:flex;align-items:center;gap:12px;">
        <button class="hamburger" id="hamburger">&#9776;</button>
        <a href="index.php?page=admin&module=caja" class="brand">Sistema POS</a>
    </div>
    <div class="nav-right">
        <span class="user-info"><?= htmlspecialchars($_SESSION['user_name'] ?? 'Admin') ?> (<?= htmlspecialchars($_SESSION['user_role'] ?? '') ?>)</span>
        <a href="index.php?page=auth&action=logout" class="btn-logout">Cerrar Sesion</a>
    </div>
</nav>
<div class="sidebar-overlay" id="sidebarOverlay"></div>
<div class="admin-wrapper">
<?php else: ?>
<nav class="catalog-nav">
    <div class="catalog-nav-inner">
        <button class="mobile-menu-btn" id="mobileMenuBtn">&#9776;</button>
        <a href="index.php" class="catalog-brand">Tienda Online</a>
        <div class="catalog-nav-links" id="catalogNavLinks">
            <a href="index.php">Catalogo</a>
            <?php if (!empty($_SESSION['user_id']) && ($_SESSION['user_type'] ?? '') === 'cliente'): ?>
                <a href="index.php?page=catalogo&vista=mis_pedidos">Mis Pedidos</a>
                <span>Hola, <?= htmlspecialchars($_SESSION['user_name']) ?></span>
                <a href="index.php?page=auth&action=logout">Salir</a>
            <?php else: ?>
                <a href="index.php?page=catalogo&vista=login">Iniciar Sesion</a>
            <?php endif; ?>
            <a href="index.php?page=catalogo&vista=carrito" class="cart-link">Carrito <span class="cart-badge" id="cartBadge">0</span></a>
        </div>
    </div>
</nav>
<?php endif; ?>

<?php if (!empty($_SESSION['flash_success'])): ?>
    <div class="alert alert-success flash-auto" style="margin:20px;<?= $isAdmin ? 'margin-left:270px;' : '' ?>">
        <?= htmlspecialchars($_SESSION['flash_success']) ?>
    </div>
<?php unset($_SESSION['flash_success']); endif; ?>
<?php if (!empty($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger flash-auto" style="margin:20px;<?= $isAdmin ? 'margin-left:270px;' : '' ?>">
        <?= htmlspecialchars($_SESSION['flash_error']) ?>
    </div>
<?php unset($_SESSION['flash_error']); endif; ?>
<script>
document.addEventListener('DOMContentLoaded',function(){
    document.querySelectorAll('.flash-auto').forEach(function(el){
        setTimeout(function(){ el.style.transition='opacity 0.3s'; el.style.opacity='0'; setTimeout(function(){ el.remove(); },300); },1000);
    });
});
</script>
