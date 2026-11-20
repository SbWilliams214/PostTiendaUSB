<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema POS</title>
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>
<div class="login-wrapper">
    <div class="login-box">
        <div class="login-logo">
            <h1>Sistema POS</h1>
            <p>Acceso para empleados</p>
        </div>
        <?php if (!empty($_SESSION['flash_error'])): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['flash_error']) ?></div>
            <?php unset($_SESSION['flash_error']); ?>
        <?php endif; ?>
        <form method="POST" action="index.php?page=auth&action=login">
            <?= csrf_field() ?>
            <input type="hidden" name="user_type" value="empleado">
            <div class="form-group">
                <label>Usuario</label>
                <input type="text" name="username" class="form-control" required autofocus placeholder="admin">
            </div>
            <div class="form-group">
                <label>Contrasena</label>
                <input type="password" name="password" class="form-control" required placeholder="********">
            </div>
            <button type="submit" class="btn btn-primary">Iniciar Sesion</button>
        </form>
    </div>
</div>
</body>
</html>
