<?php
header('Content-Type: application/json; charset=UTF-8');
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($pdo)) require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['error'=>'POST only']); exit; }
if (empty($_SESSION['user_type']) || $_SESSION['user_type'] !== 'empleado') { http_response_code(403); echo json_encode(['error'=>'No autorizado']); exit; }

$input = json_decode(file_get_contents('php://input'), true);
if (empty($input['items'])) { echo json_encode(['success'=>false,'error'=>'Sin productos']); exit; }

try {
    $pdo->beginTransaction();
    $codigo = 'POS-' . strtoupper(substr(md5(uniqid()),0,8));
    $metodo = (int)($input['metodo_pago'] ?? 1);
    $totalVenta = 0; $totalGanancia = 0;

    $stmt = $pdo->prepare("INSERT INTO pedidos (id_cliente_empleado,id_tipo_pedido,codigo_pedido,total_ganancia,fecha,hora,status) VALUES (?,2,?,0,CURDATE(),CURTIME(),'entregado')");
    $stmt->execute([$_SESSION['user_id'], $codigo]);
    $pedidoId = $pdo->lastInsertId();

    $stmtL = $pdo->prepare("INSERT INTO carrito (id_pedido,id_producto,cantidad,precio_unitario,subtotal) VALUES (?,?,?,?,?)");
    $stmtS = $pdo->prepare("UPDATE inventario SET cantidad=cantidad-? WHERE id=?");
    $stmtP = $pdo->prepare("SELECT precio_proveedor FROM inventario WHERE id=?");

    foreach ($input['items'] as $item) {
        $sub = $item['price'] * $item['qty'];
        $totalVenta += $sub;
        $stmtP->execute([$item['id']]);
        $pp = $stmtP->fetch(PDO::FETCH_ASSOC);
        $totalGanancia += ($item['price'] - ($pp['precio_proveedor'] ?? 0)) * $item['qty'];
        $stmtL->execute([$pedidoId, $item['id'], $item['qty'], $item['price'], round($sub,2)]);
        $stmtS->execute([$item['qty'], $item['id']]);
    }

    $pdo->prepare("UPDATE pedidos SET total_ganancia=? WHERE id=?")->execute([round($totalGanancia,2), $pedidoId]);
    $pdo->prepare("INSERT INTO detalle_pago (id_pedido,id_metodo_pago,monto_pagado,referencia) VALUES (?,?,?,?)")
        ->execute([$pedidoId, $metodo, round($totalVenta,2), $codigo]);

    $pdo->commit();
    echo json_encode(['success'=>true,'folio'=>$codigo,'total'=>round($totalVenta,2)]);
} catch(PDOException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['success'=>false,'error'=>'Error: '.$e->getMessage()]);
}
