<?php /** Catalogo — Login/Register Client */ ?>
<div class="catalog-container">
    <div class="auth-card">
        <div class="auth-tabs">
            <button class="auth-tab active" onclick="showTab('login')">Iniciar Sesion</button>
            <button class="auth-tab" onclick="showTab('register')">Registrarse</button>
        </div>

        <!-- Login Form -->
        <div class="auth-panel" id="panelLogin">
            <form method="POST" action="index.php?page=auth&action=login">
                <?= csrf_field() ?>
                <input type="hidden" name="user_type" value="cliente">
                <div class="form-group"><label>Email</label><input type="email" name="email" class="form-control" required></div>
                <div class="form-group"><label>Contrasena</label><input type="password" name="password" class="form-control" required></div>
                <button type="submit" class="btn-primary-cat" style="width:100%;">Iniciar Sesion</button>
            </form>
        </div>

        <!-- Register Form -->
        <div class="auth-panel" id="panelRegister" style="display:none;">
            <form method="POST" action="index.php?page=auth&action=register">
                <?= csrf_field() ?>
                <div class="form-group"><label>Nombre *</label><input type="text" name="nombre" class="form-control" required></div>
                <div class="form-group"><label>Email *</label><input type="email" name="email" class="form-control" required></div>
                <div class="form-group"><label>Telefono</label><input type="text" name="telefono" class="form-control"></div>
                <div class="form-group"><label>Direccion</label><input type="text" name="direccion" class="form-control"></div>
                <div class="form-group"><label>Contrasena *</label><input type="password" name="password" class="form-control" required minlength="6"></div>
                <button type="submit" class="btn-primary-cat" style="width:100%;">Crear Cuenta</button>
            </form>
        </div>

        <p style="text-align:center;margin-top:16px;"><a href="index.php">Volver al catalogo</a></p>
    </div>
</div>

<script>
function showTab(tab) {
    document.getElementById('panelLogin').style.display = tab === 'login' ? 'block' : 'none';
    document.getElementById('panelRegister').style.display = tab === 'register' ? 'block' : 'none';
    document.querySelectorAll('.auth-tab').forEach((t, i) => t.classList.toggle('active', (tab === 'login' && i === 0) || (tab === 'register' && i === 1)));
}
</script>
