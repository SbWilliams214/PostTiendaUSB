<?php $isAdmin = ($_GET['page'] ?? '') === 'admin'; ?>
<?php if ($isAdmin): ?>
    </div><!-- /admin-wrapper -->
    <footer class="admin-footer">&copy; <?= date('Y') ?> Sistema POS — ProyectUSB</footer>
    <script src="assets/js/admin.js"></script>
<?php else: ?>
    <footer class="catalog-footer">
        <div class="catalog-footer-inner">
            <p>&copy; <?= date('Y') ?> Tienda Online — ProyectUSB</p>
            <p>Direccion de la tienda | Telefono de contacto</p>
        </div>
    </footer>
    <script src="assets/js/catalogo.js"></script>
<?php endif; ?>
</body>
</html>
