<?php
/**
 * PDO Database Connection — ProyectUSB
 *
 * Returns a global $pdo variable used by controllers and API scripts.
 * Uses XAMPP defaults: localhost, root, no password.
 */

$db_host = 'localhost';
$db_name = 'proyect_usb';
$db_user = 'root';
$db_pass = '';

$dsn = "mysql:host={$db_host};dbname={$db_name};charset=utf8mb4";

$pdo_options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $db_user, $db_pass, $pdo_options);
} catch (PDOException $e) {
    // In production, log this instead of showing it
    http_response_code(500);
    exit('Error de conexión a la base de datos: ' . $e->getMessage());
}
