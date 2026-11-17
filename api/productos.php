<?php
header('Content-Type: application/json; charset=UTF-8');
if (!isset($pdo)) require_once __DIR__ . '/../config/database.php';

try {
    if (!empty($_GET['barcode'])) {
        $stmt = $pdo->prepare("
            SELECT i.id, i.nombre, i.precio_venta, i.cantidad, i.codigo_barras,
                   i.ruta_image, i.precio_proveedor, cm.nombre AS compania
            FROM inventario i
            LEFT JOIN compania_marca cm ON cm.id = i.id_compania
            WHERE i.codigo_barras = ? AND i.status = 'activo' LIMIT 1
        ");
        $stmt->execute([trim($_GET['barcode'])]);
        $p = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode($p ? ['success'=>true,'data'=>[$p]] : ['success'=>false,'data'=>[]]);
        exit;
    }
    if (!empty($_GET['q'])) {
        $q = '%' . trim($_GET['q']) . '%';
        $stmt = $pdo->prepare("
            SELECT i.id, i.nombre, i.precio_venta, i.cantidad, i.codigo_barras,
                   i.ruta_image, i.precio_proveedor, cm.nombre AS compania
            FROM inventario i
            LEFT JOIN compania_marca cm ON cm.id = i.id_compania
            WHERE (i.nombre LIKE ? OR cm.nombre LIKE ? OR i.codigo_barras LIKE ?) AND i.status='activo'
            ORDER BY i.nombre LIMIT 20
        ");
        $stmt->execute([$q, $q, $q]);
        echo json_encode(['success'=>true,'data'=>$stmt->fetchAll(PDO::FETCH_ASSOC)]);
        exit;
    }
    echo json_encode(['success'=>false,'message'=>'Parámetro q o barcode requerido']);
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['success'=>false,'message'=>'Error del servidor']);
}
