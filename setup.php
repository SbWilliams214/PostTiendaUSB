<?php
/**
 * Setup script — Run once to generate admin password hash
 * Access: http://localhost/ProyectUSB/setup.php
 * DELETE THIS FILE AFTER USE
 */
require_once __DIR__ . '/config/database.php';

echo "<h2>Setup — ProyectUSB POS</h2>";

// Generate real password hash for admin
$password = 'admin123';
$hash = password_hash($password, PASSWORD_DEFAULT);

try {
    // Check if admin user exists
    $stmt = $pdo->prepare("SELECT id FROM pass_usuarios WHERE id_usuario = 1");
    $stmt->execute();

    if ($stmt->fetch()) {
        $pdo->prepare("UPDATE pass_usuarios SET password_hash = ? WHERE id_usuario = 1")->execute([$hash]);
        echo "<p>✅ Contraseña del admin actualizada.</p>";
    } else {
        $pdo->prepare("INSERT INTO pass_usuarios (id_usuario, password_hash) VALUES (1, ?)")->execute([$hash]);
        echo "<p>✅ Hash del admin creado.</p>";
    }

    echo "<p><strong>Username:</strong> admin</p>";
    echo "<p><strong>Password:</strong> {$password}</p>";
    echo "<p><strong>Hash:</strong> {$hash}</p>";
    echo "<hr>";
    echo "<p>⚠️ <strong>ELIMINA este archivo (setup.php) después de usarlo.</strong></p>";
    echo '<p><a href="index.php?page=auth&action=login">Ir al Login →</a></p>';

} catch (PDOException $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
    echo "<p>Asegúrate de que la base de datos existe. Ejecuta primero sql/database.sql en phpMyAdmin.</p>";
}
