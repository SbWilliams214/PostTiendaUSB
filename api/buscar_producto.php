<?php
header('Content-Type: application/json; charset=UTF-8');
if (!isset($pdo)) require_once __DIR__ . '/../config/database.php';

$q = trim($_GET['q'] ?? '');
if ($q === '') { echo json_encode([]); exit; }

$like = "%{$q}%";
$stmt = $pdo->prepare("
    SELECT i.id, i.nombre, i.precio_venta, i.cantidad, i.codigo_barras,
           i.ruta_image AS imagen, i.precio_proveedor, cm.nombre AS compania
    FROM inventario i
    LEFT JOIN compania_marca cm ON cm.id = i.id_compania
    WHERE (i.nombre LIKE ? OR cm.nombre LIKE ? OR i.codigo_barras LIKE ?) AND i.status='activo'
    ORDER BY i.nombre LIMIT 20
");
$stmt->execute([$like, $like, $like]);
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
