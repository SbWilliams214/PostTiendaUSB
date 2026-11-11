<?php
session_start();

$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'DBUSB';

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'No se pudo conectar a la base de datos MySQL de XAMPP.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$conn->set_charset('utf8mb4');

$tableCheck = $conn->query("SHOW TABLES LIKE 'usuarios'");
if ($tableCheck && $tableCheck->num_rows === 0) {
    $conn->query("CREATE TABLE IF NOT EXISTS usuarios (
        id_usuario INT AUTO_INCREMENT PRIMARY KEY,
        usuario VARCHAR(100) NOT NULL UNIQUE,
        nombre VARCHAR(100) NOT NULL,
        apellido VARCHAR(100) NOT NULL,
        telefono VARCHAR(30) NULL,
        email VARCHAR(120) NULL,
        contrasena VARCHAR(255) NOT NULL,
        fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB");
}

function ensureColumn($conn, $table, $column, $definition)
{
    $result = $conn->query("SHOW COLUMNS FROM `{$table}` LIKE '{$column}'");
    if ($result && $result->num_rows === 0) {
        $conn->query("ALTER TABLE `{$table}` ADD COLUMN `{$column}` {$definition}");
    }
}

function ensureUniqueIndex($conn, $table, $indexName, $columnList)
{
    $result = $conn->query("SHOW INDEX FROM `{$table}` WHERE Key_name = '{$indexName}'");
    if ($result && $result->num_rows === 0) {
        $conn->query("CREATE UNIQUE INDEX `{$indexName}` ON `{$table}` ({$columnList})");
    }
}

ensureColumn($conn, 'usuarios', 'usuario', 'VARCHAR(100) NOT NULL DEFAULT ""');
ensureColumn($conn, 'usuarios', 'email', 'VARCHAR(120) NULL DEFAULT NULL');
ensureUniqueIndex($conn, 'usuarios', 'idx_usuarios_usuario', 'usuario');

return $conn;
