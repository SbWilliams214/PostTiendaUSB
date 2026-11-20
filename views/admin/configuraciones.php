<?php /** Configuraciones */ global $viewData; $d = $viewData ?? []; ?>
<div class="card">
    <h2 style="margin-bottom:20px;">Configuraciones</h2>

    <div class="settings-section">
        <h3>Codigo QR para Pagos</h3>
        <div class="qr-preview">
            <?php if (!empty($d['qr_path'])): ?>
                <img src="<?= htmlspecialchars($d['qr_path']) ?>" alt="QR de pago">
            <?php else: ?>
                <span class="text-muted">Sin QR configurado</span>
            <?php endif; ?>
        </div>
        <form method="POST" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <div class="form-group">
                <label>Subir nueva imagen QR</label>
                <input type="file" name="qr_image" class="form-control" accept="image/*">
            </div>
            <button type="submit" class="btn btn-primary">Actualizar QR</button>
        </form>
    </div>
</div>
