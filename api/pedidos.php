<?php
header('Content-Type: application/json; charset=UTF-8');
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($pdo)) require_once __DIR__ . '/../config/database.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_REQUEST['action'] ?? '';

try {
    switch ($action) {
        case 'crear':
            if ($method !== 'POST') { jsonErr(405,'Metodo no permitido'); }
            if (empty($_SESSION['user_id'])) { jsonErr(401,'Debe iniciar sesion'); }
            if (empty($_SESSION['cart']) && empty($_SESSION['cart_promos'])) { jsonErr(400,'Carrito vacio'); }

            $pdo->beginTransaction();
            $totalVenta = 0; $totalGanancia = 0; $lines = [];

            // Process regular products
            $cart = $_SESSION['cart'] ?? [];
            if (!empty($cart)) {
                $ids = array_keys($cart);
                $ph = implode(',', array_fill(0,count($ids),'?'));
                $stmt = $pdo->prepare("SELECT id,precio_venta,precio_proveedor,cantidad,nombre FROM inventario WHERE id IN ($ph) FOR UPDATE");
                $stmt->execute($ids);
                $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($products as $p) {
                    $qty = $cart[$p['id']];
                    if ($qty > $p['cantidad']) { $pdo->rollBack(); jsonErr(409,"Stock insuficiente: {$p['nombre']}"); }
                    $sub = $p['precio_venta'] * $qty;
                    $gan = ($p['precio_venta'] - ($p['precio_proveedor'] ?? 0)) * $qty;
                    $totalVenta += $sub; $totalGanancia += $gan;
                    $lines[] = ['pid'=>$p['id'],'qty'=>$qty,'precio'=>$p['precio_venta'],'sub'=>$sub];
                }
            }

            // Process promotions as recipes
            $cartPromos = $_SESSION['cart_promos'] ?? [];
            $promoLines = [];
            if (!empty($cartPromos)) {
                $stmtReceta = $pdo->prepare("SELECT dp.id_producto, dp.precio_unitario_promo FROM detalle_promociones dp WHERE dp.id_promocion=?");
                $stmtPromo = $pdo->prepare("SELECT precio FROM promociones WHERE id=?");

                foreach ($cartPromos as $promoId => $promoQty) {
                    $stmtPromo->execute([$promoId]);
                    $promoPrice = (float)$stmtPromo->fetchColumn();
                    $totalVenta += $promoPrice * $promoQty;

                    $stmtReceta->execute([$promoId]);
                    $receta = $stmtReceta->fetchAll(PDO::FETCH_ASSOC);

                    foreach ($receta as $ingrediente) {
                        $prodId = (int)$ingrediente['id_producto'];
                        $precioPromo = (float)$ingrediente['precio_unitario_promo'];

                        // Check stock
                        $stmtChk = $pdo->prepare("SELECT cantidad, precio_proveedor, nombre FROM inventario WHERE id=? FOR UPDATE");
                        $stmtChk->execute([$prodId]);
                        $inv = $stmtChk->fetch(PDO::FETCH_ASSOC);
                        if ($inv && $promoQty > $inv['cantidad']) {
                            $pdo->rollBack();
                            jsonErr(409, "Stock insuficiente para promocion: {$inv['nombre']}");
                        }

                        $totalGanancia += ($precioPromo - ($inv['precio_proveedor'] ?? 0)) * $promoQty;
                        $subItem = $precioPromo * $promoQty;
                        $promoLines[] = ['pid'=>$prodId,'qty'=>$promoQty,'precio'=>$precioPromo,'sub'=>round($subItem,2)];
                    }
                }
            }

            $codigo = 'PED-' . strtoupper(substr(md5(uniqid()),0,8));
            $metodo = (int)($_POST['metodo_pago'] ?? 1);

            $stmt = $pdo->prepare("INSERT INTO pedidos (id_cliente_empleado,id_tipo_pedido,codigo_pedido,total_ganancia,fecha,hora,status) VALUES (?,1,?,?,CURDATE(),CURTIME(),'pendiente')");
            $stmt->execute([$_SESSION['user_id'], $codigo, round($totalGanancia,2)]);
            $pedidoId = $pdo->lastInsertId();

            $stmtL = $pdo->prepare("INSERT INTO carrito (id_pedido,id_producto,cantidad,precio_unitario,subtotal) VALUES (?,?,?,?,?)");
            $stmtS = $pdo->prepare("UPDATE inventario SET cantidad=cantidad-? WHERE id=?");

            // Insert product lines
            foreach ($lines as $l) {
                $stmtL->execute([$pedidoId,$l['pid'],$l['qty'],$l['precio'],$l['sub']]);
                $stmtS->execute([$l['qty'],$l['pid']]);
            }
            // Insert promo recipe lines
            foreach ($promoLines as $l) {
                $stmtL->execute([$pedidoId,$l['pid'],$l['qty'],$l['precio'],$l['sub']]);
                $stmtS->execute([$l['qty'],$l['pid']]);
            }

            $pdo->prepare("INSERT INTO detalle_pago (id_pedido,id_metodo_pago,monto_pagado,referencia) VALUES (?,?,?,?)")
                ->execute([$pedidoId,$metodo,round($totalVenta,2),$codigo]);
            $pdo->commit();
            $_SESSION['cart'] = [];
            $_SESSION['cart_promos'] = [];
            echo json_encode(['success'=>true,'codigo'=>$codigo,'total'=>round($totalVenta,2)]);
            break;

        case 'pendientes':
            if (empty($_SESSION['user_type']) || $_SESSION['user_type'] !== 'empleado') { jsonErr(403,'No autorizado'); }
            $stmt = $pdo->query("SELECT p.*, c.nombre AS cliente_nombre, c.telefono AS cliente_tel FROM pedidos p LEFT JOIN clientes c ON c.id=p.id_cliente_empleado WHERE p.status='pendiente' AND p.id_tipo_pedido=1 ORDER BY p.fecha DESC, p.hora DESC");
            $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmtD = $pdo->prepare("SELECT cr.*, i.nombre AS producto_nombre FROM carrito cr LEFT JOIN inventario i ON i.id=cr.id_producto WHERE cr.id_pedido=?");
            foreach ($orders as &$o) { $stmtD->execute([$o['id']]); $o['items'] = $stmtD->fetchAll(PDO::FETCH_ASSOC); }
            echo json_encode(['success'=>true,'data'=>$orders]);
            break;

        case 'entregar':
            if ($method !== 'POST') { jsonErr(405,'No permitido'); }
            if (empty($_SESSION['user_type']) || $_SESSION['user_type'] !== 'empleado') { jsonErr(403,'No autorizado'); }
            $pid = (int)($_POST['pedido_id'] ?? 0);
            if ($pid <= 0) { jsonErr(400,'ID invalido'); }
            $stmt = $pdo->prepare("UPDATE pedidos SET status='entregado' WHERE id=? AND status='pendiente'");
            $stmt->execute([$pid]);
            if ($stmt->rowCount() === 0) { jsonErr(404,'Pedido no encontrado'); }
            echo json_encode(['success'=>true,'message'=>'Pedido entregado']);
            break;

        case 'historial':
            if (empty($_SESSION['user_id'])) { jsonErr(401,'No autenticado'); }
            $stmt = $pdo->prepare("SELECT * FROM pedidos WHERE id_cliente_empleado=? ORDER BY fecha DESC, hora DESC");
            $stmt->execute([$_SESSION['user_id']]);
            $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmtD = $pdo->prepare("SELECT cr.*, i.nombre AS producto_nombre FROM carrito cr LEFT JOIN inventario i ON i.id=cr.id_producto WHERE cr.id_pedido=?");
            foreach ($orders as &$o) { $stmtD->execute([$o['id']]); $o['items'] = $stmtD->fetchAll(PDO::FETCH_ASSOC); }
            echo json_encode(['success'=>true,'data'=>$orders]);
            break;

        default: jsonErr(400,'Accion no valida');
    }
} catch(PDOException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['success'=>false,'message'=>'Error del servidor']);
}

function jsonErr($c,$m) { http_response_code($c); echo json_encode(['success'=>false,'message'=>$m]); exit; }
