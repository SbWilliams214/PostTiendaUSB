<?php
header('Content-Type: application/json; charset=UTF-8');
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($pdo)) require_once __DIR__ . '/../config/database.php';
if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        echo json_encode(['success'=>true,'data'=>buildCart($pdo)]);
        exit;
    }
    if ($method === 'POST') {
        $action = $_POST['action'] ?? '';
        $id = (int)($_POST['id'] ?? 0);
        $qty = (int)($_POST['qty'] ?? 1);

        if ($action === 'add' && $id > 0) {
            $stmt = $pdo->prepare("SELECT cantidad FROM inventario WHERE id=? AND status='activo'");
            $stmt->execute([$id]);
            $p = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$p) { jsonErr(404,'Producto no encontrado'); }
            $current = $_SESSION['cart'][$id] ?? 0;
            $_SESSION['cart'][$id] = $current + max(1,$qty);
        } elseif ($action === 'remove') {
            unset($_SESSION['cart'][$id]);
        } elseif ($action === 'update' && $id > 0) {
            if ($qty <= 0) unset($_SESSION['cart'][$id]);
            else $_SESSION['cart'][$id] = $qty;
        } elseif ($action === 'clear') {
            $_SESSION['cart'] = [];
        }
        echo json_encode(['success'=>true,'data'=>buildCart($pdo)]);
        exit;
    }
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['success'=>false,'message'=>'Error del servidor']);
}

function buildCart($pdo) {
    if (empty($_SESSION['cart'])) return ['items'=>[],'total'=>0,'count'=>0];
    $ids = array_keys($_SESSION['cart']);
    $ph = implode(',', array_fill(0,count($ids),'?'));
    $stmt = $pdo->prepare("SELECT i.id, i.nombre, i.precio_venta, i.ruta_image, cm.nombre AS compania
        FROM inventario i LEFT JOIN compania_marca cm ON cm.id=i.id_compania WHERE i.id IN ($ph)");
    $stmt->execute($ids);
    $items = []; $total = 0;
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $p) {
        $q = $_SESSION['cart'][$p['id']];
        $sub = $p['precio_venta'] * $q;
        $total += $sub;
        $items[] = ['id'=>$p['id'],'nombre'=>$p['nombre'],'precio_venta'=>$p['precio_venta'],
                    'cantidad'=>$q,'subtotal'=>round($sub,2),'ruta_image'=>$p['ruta_image'],'compania'=>$p['compania']];
    }
    return ['items'=>$items,'total'=>round($total,2),'count'=>array_sum($_SESSION['cart'])];
}
function jsonErr($code,$msg) { http_response_code($code); echo json_encode(['success'=>false,'message'=>$msg]); exit; }
