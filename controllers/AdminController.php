<?php
class AdminController {
    private $pdo;
    public function __construct($pdo) { $this->pdo = $pdo; }

    // ── INVENTARIO ───────────────────────────────────────────────
    public function inventario() {
        global $viewData;
        $buscar = trim($_GET['buscar'] ?? '');
        $stockBajo = !empty($_GET['stock_bajo']);
        $page = max(1, (int)($_GET['p'] ?? 1));
        $limit = 15; $offset = ($page - 1) * $limit;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $act = $_POST['form_action'] ?? '';
            if ($act === 'crear') {
                $img = $this->uploadImage('imagen');
                $this->pdo->prepare("INSERT INTO inventario (nombre,descripcion,cantidad,precio_proveedor,precio_venta,umbral_alerta,codigo_barras,ruta_image,id_categoria,id_proveedor,id_compania,status) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)")
                    ->execute([$_POST['nombre'],$_POST['descripcion'],(int)$_POST['cantidad'],$_POST['precio_proveedor'],$_POST['precio_venta'],(int)($_POST['umbral_alerta']??5),$_POST['codigo_barras'],$img,$_POST['id_categoria']?:null,$_POST['id_proveedor']?:null,$_POST['id_compania']?:null,'activo']);
                $_SESSION['flash_success'] = 'Producto creado';
                header('Location: index.php?page=admin&module=inventario'); exit;
            }
            if ($act === 'editar') {
                $id = (int)$_POST['id'];
                $img = $this->uploadImage('imagen');
                if ($img) {
                    $this->pdo->prepare("UPDATE inventario SET nombre=?,descripcion=?,cantidad=?,precio_proveedor=?,precio_venta=?,umbral_alerta=?,codigo_barras=?,ruta_image=?,id_categoria=?,id_proveedor=?,id_compania=? WHERE id=?")
                        ->execute([$_POST['nombre'],$_POST['descripcion'],(int)$_POST['cantidad'],$_POST['precio_proveedor'],$_POST['precio_venta'],(int)($_POST['umbral_alerta']??5),$_POST['codigo_barras'],$img,$_POST['id_categoria']?:null,$_POST['id_proveedor']?:null,$_POST['id_compania']?:null,$id]);
                } else {
                    $this->pdo->prepare("UPDATE inventario SET nombre=?,descripcion=?,cantidad=?,precio_proveedor=?,precio_venta=?,umbral_alerta=?,codigo_barras=?,id_categoria=?,id_proveedor=?,id_compania=? WHERE id=?")
                        ->execute([$_POST['nombre'],$_POST['descripcion'],(int)$_POST['cantidad'],$_POST['precio_proveedor'],$_POST['precio_venta'],(int)($_POST['umbral_alerta']??5),$_POST['codigo_barras'],$_POST['id_categoria']?:null,$_POST['id_proveedor']?:null,$_POST['id_compania']?:null,$id]);
                }
                $_SESSION['flash_success'] = 'Producto actualizado';
                header('Location: index.php?page=admin&module=inventario'); exit;
            }
            if ($act === 'toggle') {
                $this->pdo->prepare("UPDATE inventario SET status=IF(status='activo','inactivo','activo') WHERE id=?")->execute([(int)$_POST['id']]);
                header('Location: index.php?page=admin&module=inventario'); exit;
            }
            if ($act === 'eliminar') {
                $this->pdo->prepare("DELETE FROM inventario WHERE id=?")->execute([(int)$_POST['id']]);
                $_SESSION['flash_success'] = 'Producto eliminado';
                header('Location: index.php?page=admin&module=inventario'); exit;
            }
        }

        $where = ''; $params = [];
        if ($stockBajo) {
            $where = "WHERE i.cantidad <= i.umbral_alerta";
        }
        if ($buscar) {
            $where = $where ? "$where AND" : "WHERE";
            $where .= " (i.nombre LIKE ? OR cm.nombre LIKE ? OR i.codigo_barras LIKE ?)";
            $like = "%{$buscar}%"; $params = [$like,$like,$like];
        }

        $countStmt = $this->pdo->prepare("SELECT COUNT(*) FROM inventario i LEFT JOIN compania_marca cm ON cm.id=i.id_compania $where");
        $countStmt->execute($params);
        $total = $countStmt->fetchColumn();

        $stmt = $this->pdo->prepare("SELECT i.*, c.nombre AS categoria, cm.nombre AS compania, pv.nombre AS proveedor
            FROM inventario i LEFT JOIN categoria c ON c.id=i.id_categoria LEFT JOIN compania_marca cm ON cm.id=i.id_compania LEFT JOIN proveedores pv ON pv.id=i.id_proveedor
            $where ORDER BY i.id DESC LIMIT $limit OFFSET $offset");
        $stmt->execute($params);

        $lowCount = $this->pdo->query("SELECT COUNT(*) FROM inventario WHERE cantidad <= umbral_alerta")->fetchColumn();

        $viewData = [
            'productos' => $stmt->fetchAll(PDO::FETCH_ASSOC),
            'total' => $total, 'page' => $page, 'totalPages' => ceil($total / $limit),
            'buscar' => $buscar, 'stockBajo' => $stockBajo, 'lowCount' => $lowCount,
            'categorias' => $this->pdo->query("SELECT * FROM categoria ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC),
            'companias' => $this->pdo->query("SELECT * FROM compania_marca ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC),
            'proveedores' => $this->pdo->query("SELECT p.*, cm.nombre AS compania FROM proveedores p LEFT JOIN compania_marca cm ON cm.id=p.id_compania ORDER BY p.nombre")->fetchAll(PDO::FETCH_ASSOC),
        ];
        if (!empty($_GET['editar'])) {
            $stmt = $this->pdo->prepare("SELECT * FROM inventario WHERE id=?");
            $stmt->execute([(int)$_GET['editar']]);
            $viewData['editando'] = $stmt->fetch(PDO::FETCH_ASSOC);
        }
    }

    // ── CATEGORIAS ───────────────────────────────────────────────
    public function categorias() {
        global $viewData;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $act = $_POST['form_action'] ?? '';
            if ($act === 'crear') {
                $this->pdo->prepare("INSERT INTO categoria (nombre,descripcion) VALUES (?,?)")->execute([$_POST['nombre'],$_POST['descripcion']??'']);
                $_SESSION['flash_success'] = 'Categoria creada';
                header('Location: index.php?page=admin&module=categorias'); exit;
            }
            if ($act === 'editar') {
                $this->pdo->prepare("UPDATE categoria SET nombre=?,descripcion=? WHERE id=?")->execute([$_POST['nombre'],$_POST['descripcion']??'',(int)$_POST['id']]);
                $_SESSION['flash_success'] = 'Categoria actualizada';
                header('Location: index.php?page=admin&module=categorias'); exit;
            }
            if ($act === 'eliminar') {
                $this->pdo->prepare("DELETE FROM categoria WHERE id=?")->execute([(int)$_POST['id']]);
                $_SESSION['flash_success'] = 'Categoria eliminada';
                header('Location: index.php?page=admin&module=categorias'); exit;
            }
        }
        $viewData = [
            'categorias' => $this->pdo->query("SELECT c.*, (SELECT COUNT(*) FROM inventario WHERE id_categoria=c.id) AS total_productos FROM categoria c ORDER BY c.nombre")->fetchAll(PDO::FETCH_ASSOC),
        ];
        if (!empty($_GET['editar'])) {
            $stmt = $this->pdo->prepare("SELECT * FROM categoria WHERE id=?");
            $stmt->execute([(int)$_GET['editar']]);
            $viewData['editando'] = $stmt->fetch(PDO::FETCH_ASSOC);
        }
    }

    // ── PROVEEDORES ──────────────────────────────────────────────
    public function proveedores() {
        global $viewData;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $act = $_POST['form_action'] ?? '';
            if ($act === 'crear') {
                // Create compania if new name provided
                $id_compania = null;
                if (!empty($_POST['nueva_compania'])) {
                    $this->pdo->prepare("INSERT INTO compania_marca (nombre,pais_origen) VALUES (?,?)")->execute([trim($_POST['nueva_compania']),trim($_POST['pais_origen']??'')]);
                    $id_compania = $this->pdo->lastInsertId();
                } elseif (!empty($_POST['id_compania'])) {
                    $id_compania = (int)$_POST['id_compania'];
                }
                $this->pdo->prepare("INSERT INTO proveedores (id_compania,nombre,telefono,email,direccion) VALUES (?,?,?,?,?)")
                    ->execute([$id_compania,$_POST['nombre'],$_POST['telefono']??'',$_POST['email']??'',$_POST['direccion']??'']);
                $_SESSION['flash_success'] = 'Proveedor registrado';
                header('Location: index.php?page=admin&module=proveedores'); exit;
            }
            if ($act === 'editar') {
                $id_compania = null;
                if (!empty($_POST['nueva_compania'])) {
                    $this->pdo->prepare("INSERT INTO compania_marca (nombre,pais_origen) VALUES (?,?)")->execute([trim($_POST['nueva_compania']),trim($_POST['pais_origen']??'')]);
                    $id_compania = $this->pdo->lastInsertId();
                } elseif (!empty($_POST['id_compania'])) {
                    $id_compania = (int)$_POST['id_compania'];
                }
                $this->pdo->prepare("UPDATE proveedores SET id_compania=?,nombre=?,telefono=?,email=?,direccion=? WHERE id=?")
                    ->execute([$id_compania,$_POST['nombre'],$_POST['telefono']??'',$_POST['email']??'',$_POST['direccion']??'',(int)$_POST['id']]);
                $_SESSION['flash_success'] = 'Proveedor actualizado';
                header('Location: index.php?page=admin&module=proveedores'); exit;
            }
            if ($act === 'eliminar') {
                $this->pdo->prepare("DELETE FROM proveedores WHERE id=?")->execute([(int)$_POST['id']]);
                $_SESSION['flash_success'] = 'Proveedor eliminado';
                header('Location: index.php?page=admin&module=proveedores'); exit;
            }
        }
        $viewData = [
            'proveedores' => $this->pdo->query("SELECT p.*, cm.nombre AS compania, cm.pais_origen FROM proveedores p LEFT JOIN compania_marca cm ON cm.id=p.id_compania ORDER BY p.nombre")->fetchAll(PDO::FETCH_ASSOC),
            'companias' => $this->pdo->query("SELECT * FROM compania_marca ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC),
        ];
        if (!empty($_GET['editar'])) {
            $stmt = $this->pdo->prepare("SELECT * FROM proveedores WHERE id=?");
            $stmt->execute([(int)$_GET['editar']]);
            $viewData['editando'] = $stmt->fetch(PDO::FETCH_ASSOC);
        }
    }

    // ── USUARIOS ─────────────────────────────────────────────────
    public function usuarios() {
        global $viewData;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $act = $_POST['form_action'] ?? '';
            if ($act === 'crear') {
                $this->pdo->beginTransaction();
                $this->pdo->prepare("INSERT INTO usuario (nombre,username,email,telefono) VALUES (?,?,?,?)")
                    ->execute([$_POST['nombre'],$_POST['username'],$_POST['email']??'',$_POST['telefono']??'']);
                $uid = $this->pdo->lastInsertId();
                $hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $this->pdo->prepare("INSERT INTO pass_usuarios (id_usuario,password_hash) VALUES (?,?)")->execute([$uid,$hash]);
                $this->pdo->prepare("INSERT INTO empleados (id_usuario,id_rol,fecha_contratacion) VALUES (?,?,CURDATE())")->execute([$uid,(int)$_POST['id_rol']]);
                $this->pdo->commit();
                $_SESSION['flash_success'] = 'Empleado creado';
                header('Location: index.php?page=admin&module=usuarios'); exit;
            }
            if ($act === 'editar') {
                $eid = (int)$_POST['id'];
                $stmt = $this->pdo->prepare("SELECT id_usuario FROM empleados WHERE id=?");
                $stmt->execute([$eid]);
                $emp = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($emp) {
                    $this->pdo->prepare("UPDATE usuario SET nombre=?,username=?,email=?,telefono=? WHERE id=?")->execute([$_POST['nombre'],$_POST['username'],$_POST['email']??'',$_POST['telefono']??'',$emp['id_usuario']]);
                    $this->pdo->prepare("UPDATE empleados SET id_rol=? WHERE id=?")->execute([(int)$_POST['id_rol'],$eid]);
                    if (!empty($_POST['password'])) {
                        $hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
                        $this->pdo->prepare("UPDATE pass_usuarios SET password_hash=? WHERE id_usuario=?")->execute([$hash,$emp['id_usuario']]);
                    }
                }
                $_SESSION['flash_success'] = 'Empleado actualizado';
                header('Location: index.php?page=admin&module=usuarios'); exit;
            }
            if ($act === 'toggle') {
                $this->pdo->prepare("UPDATE empleados SET status=IF(status='activo','inactivo','activo') WHERE id=?")->execute([(int)$_POST['id']]);
                header('Location: index.php?page=admin&module=usuarios'); exit;
            }
        }
        $viewData = [
            'empleados' => $this->pdo->query("SELECT e.*, u.nombre, u.username, u.email, u.telefono, r.nombre AS rol FROM empleados e JOIN usuario u ON u.id=e.id_usuario JOIN roles r ON r.id=e.id_rol ORDER BY e.id DESC")->fetchAll(PDO::FETCH_ASSOC),
            'roles' => $this->pdo->query("SELECT * FROM roles")->fetchAll(PDO::FETCH_ASSOC),
        ];
        if (!empty($_GET['editar'])) {
            $stmt = $this->pdo->prepare("SELECT e.*, u.nombre, u.username, u.email, u.telefono FROM empleados e JOIN usuario u ON u.id=e.id_usuario WHERE e.id=?");
            $stmt->execute([(int)$_GET['editar']]);
            $viewData['editando'] = $stmt->fetch(PDO::FETCH_ASSOC);
        }
    }

    // ── CAJA ─────────────────────────────────────────────────────
    public function caja() {
        global $viewData;
        $empId = $_SESSION['user_id'] ?? 0;

        // Handle POST actions
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $act = $_POST['form_action'] ?? '';
            if ($act === 'abrir_caja') {
                $monto = (float)($_POST['monto_inicial'] ?? 0);
                $this->pdo->prepare("INSERT INTO caja (id_empleado,monto_inicial,fecha_apertura,hora_apertura,status) VALUES (?,?,CURDATE(),CURTIME(),'abierta')")
                    ->execute([$empId, $monto]);
                $_SESSION['flash_success'] = 'Caja abierta';
                header('Location: index.php?page=admin&module=caja'); exit;
            }
            if ($act === 'cerrar_caja') {
                $cajaId = (int)$_POST['caja_id'];
                // Calculate totals by payment method
                $stmt = $this->pdo->prepare("
                    SELECT mp.metodo, IFNULL(SUM(dp.monto_pagado),0) AS total
                    FROM detalle_pago dp
                    JOIN metodo_pago mp ON mp.id=dp.id_metodo_pago
                    JOIN pedidos p ON p.id=dp.id_pedido
                    JOIN caja ca ON ca.id=?
                    WHERE p.status='entregado' AND p.fecha=ca.fecha_apertura AND p.hora>=ca.hora_apertura
                      AND p.id_cliente_empleado=?
                    GROUP BY mp.metodo
                ");
                $stmt->execute([$cajaId, $empId]);
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                // Ensure both Efectivo and QR always appear
                $desglose = ['Efectivo' => 0, 'QR' => 0];
                foreach ($rows as $r) {
                    $desglose[$r['metodo']] = (float)$r['total'];
                }
                $desgloseArr = [];
                foreach ($desglose as $metodo => $total) {
                    $desgloseArr[] = ['metodo' => $metodo, 'total' => $total];
                }
                $totalFinal = array_sum($desglose);

                $stmtM = $this->pdo->prepare("SELECT monto_inicial FROM caja WHERE id=?");
                $stmtM->execute([$cajaId]);
                $montoInicial = (float)$stmtM->fetchColumn();

                $this->pdo->prepare("UPDATE caja SET monto_final=?,fecha_cierre=CURDATE(),hora_cierre=CURTIME(),status='cerrada' WHERE id=?")
                    ->execute([round($totalFinal + $montoInicial, 2), $cajaId]);

                $_SESSION['caja_cierre'] = [
                    'monto_inicial' => $montoInicial,
                    'desglose' => $desgloseArr,
                    'total_ventas' => $totalFinal,
                    'monto_final' => round($totalFinal + $montoInicial, 2),
                ];
                $_SESSION['flash_success'] = 'Caja cerrada exitosamente';
                header('Location: index.php?page=admin&module=caja'); exit;
            }
        }

        // Check if there's an open caja for this employee
        $stmt = $this->pdo->prepare("SELECT * FROM caja WHERE id_empleado=? AND status='abierta' ORDER BY id DESC LIMIT 1");
        $stmt->execute([$empId]);
        $cajaAbierta = $stmt->fetch(PDO::FETCH_ASSOC);

        $viewData = [
            'productos' => $this->pdo->query("SELECT i.id, i.nombre, i.precio_venta, i.cantidad, i.codigo_barras, i.ruta_image, i.precio_proveedor, cm.nombre AS compania FROM inventario i LEFT JOIN compania_marca cm ON cm.id=i.id_compania WHERE i.status='activo' ORDER BY i.nombre")->fetchAll(PDO::FETCH_ASSOC),
            'promociones' => $this->pdo->query("SELECT p.*, GROUP_CONCAT(CONCAT(i.nombre,' x',dp.precio_unitario_promo) SEPARATOR ', ') AS productos_promo FROM promociones p LEFT JOIN detalle_promociones dp ON dp.id_promocion=p.id LEFT JOIN inventario i ON i.id=dp.id_producto WHERE p.status='activo' GROUP BY p.id ORDER BY p.nombre")->fetchAll(PDO::FETCH_ASSOC),
            'caja_abierta' => $cajaAbierta,
            'caja_cierre' => $_SESSION['caja_cierre'] ?? null,
        ];
        unset($_SESSION['caja_cierre']);
    }

    // ── PEDIDOS ──────────────────────────────────────────────────
    public function pedidos() {
        global $viewData;
        $filter = $_GET['filtro'] ?? 'pendiente';
        $where = $filter === 'todos' ? '' : "WHERE p.status='".addslashes($filter)."'";
        $stmt = $this->pdo->query("SELECT p.*, c.nombre AS cliente_nombre, tp.nombre AS tipo FROM pedidos p LEFT JOIN clientes c ON c.id=p.id_cliente_empleado LEFT JOIN tipo_pedido tp ON tp.id=p.id_tipo_pedido $where ORDER BY p.fecha DESC, p.hora DESC LIMIT 100");
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmtD = $this->pdo->prepare("SELECT cr.*, i.nombre AS producto_nombre, i.ruta_image AS producto_imagen FROM carrito cr LEFT JOIN inventario i ON i.id=cr.id_producto WHERE cr.id_pedido=?");
        foreach ($orders as &$o) { $stmtD->execute([$o['id']]); $o['items'] = $stmtD->fetchAll(PDO::FETCH_ASSOC); }
        $viewData = ['pedidos'=>$orders,'filtro'=>$filter];

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form_action'] ?? '') === 'entregar') {
            $this->pdo->prepare("UPDATE pedidos SET status='entregado' WHERE id=? AND status='pendiente'")->execute([(int)$_POST['pedido_id']]);
            $_SESSION['flash_success'] = 'Pedido entregado';
            header('Location: index.php?page=admin&module=pedidos'); exit;
        }
    }

    // ── PROMOCIONES ──────────────────────────────────────────────
    public function promociones() {
        global $viewData;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $act = $_POST['form_action'] ?? '';
            if ($act === 'crear') {
                $img = $this->uploadImage('imagen');
                $this->pdo->prepare("INSERT INTO promociones (id_empleado,nombre,descripcion,precio,ruta_imagen,status,status_plataforma) VALUES (?,?,?,?,?,?,?)")
                    ->execute([$_SESSION['user_id'],$_POST['nombre'],$_POST['descripcion'],$_POST['precio'],$img,$_POST['status']??'activo',$_POST['status_plataforma']??'inactivo']);
                $promoId = $this->pdo->lastInsertId();
                // Save detail products
                if (!empty($_POST['prod_ids'])) {
                    $stmtDP = $this->pdo->prepare("INSERT INTO detalle_promociones (id_promocion,id_producto,precio_unitario_promo) VALUES (?,?,?)");
                    foreach ($_POST['prod_ids'] as $k => $pid) {
                        $precioP = $_POST['prod_precios'][$k] ?? 0;
                        $stmtDP->execute([$promoId, (int)$pid, (float)$precioP]);
                    }
                }
                $_SESSION['flash_success'] = 'Promocion creada';
                header('Location: index.php?page=admin&module=promociones'); exit;
            }
            if ($act === 'editar') {
                $id = (int)$_POST['id'];
                $img = $this->uploadImage('imagen');
                if ($img) {
                    $this->pdo->prepare("UPDATE promociones SET nombre=?,descripcion=?,precio=?,ruta_imagen=?,status=?,status_plataforma=? WHERE id=?")
                        ->execute([$_POST['nombre'],$_POST['descripcion'],$_POST['precio'],$img,$_POST['status']??'activo',$_POST['status_plataforma']??'inactivo',$id]);
                } else {
                    $this->pdo->prepare("UPDATE promociones SET nombre=?,descripcion=?,precio=?,status=?,status_plataforma=? WHERE id=?")
                        ->execute([$_POST['nombre'],$_POST['descripcion'],$_POST['precio'],$_POST['status']??'activo',$_POST['status_plataforma']??'inactivo',$id]);
                }
                // Update detail products
                $this->pdo->prepare("DELETE FROM detalle_promociones WHERE id_promocion=?")->execute([$id]);
                if (!empty($_POST['prod_ids'])) {
                    $stmtDP = $this->pdo->prepare("INSERT INTO detalle_promociones (id_promocion,id_producto,precio_unitario_promo) VALUES (?,?,?)");
                    foreach ($_POST['prod_ids'] as $k => $pid) {
                        $precioP = $_POST['prod_precios'][$k] ?? 0;
                        $stmtDP->execute([$id, (int)$pid, (float)$precioP]);
                    }
                }
                $_SESSION['flash_success'] = 'Promocion actualizada';
                header('Location: index.php?page=admin&module=promociones'); exit;
            }
            if ($act === 'toggle_plat') {
                $this->pdo->prepare("UPDATE promociones SET status_plataforma=IF(status_plataforma='activo','inactivo','activo') WHERE id=?")->execute([(int)$_POST['id']]);
                header('Location: index.php?page=admin&module=promociones'); exit;
            }
            if ($act === 'toggle_status') {
                $this->pdo->prepare("UPDATE promociones SET status=IF(status='activo','inactivo','activo') WHERE id=?")->execute([(int)$_POST['id']]);
                header('Location: index.php?page=admin&module=promociones'); exit;
            }
            if ($act === 'eliminar') {
                $this->pdo->prepare("DELETE FROM promociones WHERE id=?")->execute([(int)$_POST['id']]);
                $_SESSION['flash_success'] = 'Promocion eliminada';
                header('Location: index.php?page=admin&module=promociones'); exit;
            }
        }
        $promos = $this->pdo->query("SELECT * FROM promociones ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
        // Load detail products for each promo
        $stmtDet = $this->pdo->prepare("SELECT dp.*, i.nombre AS producto_nombre FROM detalle_promociones dp LEFT JOIN inventario i ON i.id=dp.id_producto WHERE dp.id_promocion=?");
        foreach ($promos as &$pr) {
            $stmtDet->execute([$pr['id']]);
            $pr['detalle_productos'] = $stmtDet->fetchAll(PDO::FETCH_ASSOC);
        }
        $viewData = [
            'promociones' => $promos,
            'productos' => $this->pdo->query("SELECT id,nombre,precio_venta FROM inventario WHERE status='activo' ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC),
        ];
        if (!empty($_GET['editar'])) {
            $stmt = $this->pdo->prepare("SELECT * FROM promociones WHERE id=?");
            $stmt->execute([(int)$_GET['editar']]);
            $editando = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($editando) {
                $stmtDet->execute([$editando['id']]);
                $editando['detalle_productos'] = $stmtDet->fetchAll(PDO::FETCH_ASSOC);
            }
            $viewData['editando'] = $editando;
        }
    }

    // ── CONFIGURACIONES ──────────────────────────────────────────
    public function configuraciones() {
        global $viewData;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!empty($_FILES['qr_image']['name'])) {
                $img = $this->uploadImage('qr_image');
                if ($img) {
                    $this->pdo->prepare("UPDATE configuraciones SET valor=? WHERE clave='qr_general'")->execute([$img]);
                    $_SESSION['flash_success'] = 'QR actualizado';
                }
            }
            header('Location: index.php?page=admin&module=configuraciones'); exit;
        }
        $stmt = $this->pdo->prepare("SELECT valor FROM configuraciones WHERE clave='qr_general'");
        $stmt->execute();
        $viewData = ['qr_path' => $stmt->fetchColumn()];
    }

    // ── REPORTES ─────────────────────────────────────────────────
    public function reportes() {
        global $viewData;
        $viewData = ['reportData' => null, 'tipo' => ''];

        // Load employees list for filter
        $viewData['empleados'] = $this->pdo->query("SELECT e.id, u.nombre FROM empleados e JOIN usuario u ON u.id=e.id_usuario ORDER BY u.nombre")->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($_GET['generar'])) {
            $tipo = $_GET['generar'];
            $desde = $_GET['desde'] ?? date('Y-m-d', strtotime('-30 days'));
            $hasta = $_GET['hasta'] ?? date('Y-m-d');
            $empFiltro = (int)($_GET['empleado'] ?? 0);
            $viewData['tipo'] = $tipo;
            $viewData['desde'] = $desde;
            $viewData['hasta'] = $hasta;
            $viewData['empleado_filtro'] = $empFiltro;

            // Build employee filter clause
            $empWhere = '';
            $empParams = [];
            if ($empFiltro > 0) {
                $empWhere = ' AND p.id_cliente_empleado = ? AND p.id_tipo_pedido = 2';
                $empParams = [$empFiltro];
            }

            if ($tipo === 'ventas_diarias') {
                $stmt = $this->pdo->prepare("
                    SELECT p.id AS pedido_id, p.codigo_pedido, p.fecha, p.hora, p.status,
                           tp.nombre AS tipo_pedido,
                           cr.cantidad, cr.precio_unitario, cr.subtotal,
                           i.nombre AS producto_nombre,
                           cm.nombre AS marca,
                           IF(p.id_tipo_pedido=1, 'Web / En linea', IFNULL(u.nombre, 'Sin asignar')) AS empleado_nombre
                    FROM pedidos p
                    JOIN carrito cr ON cr.id_pedido = p.id
                    LEFT JOIN inventario i ON i.id = cr.id_producto
                    LEFT JOIN compania_marca cm ON cm.id = i.id_compania
                    LEFT JOIN tipo_pedido tp ON tp.id = p.id_tipo_pedido
                    LEFT JOIN empleados emp ON (emp.id = p.id_cliente_empleado AND p.id_tipo_pedido = 2)
                    LEFT JOIN usuario u ON u.id = emp.id_usuario
                    WHERE p.status='entregado' AND p.fecha BETWEEN ? AND ? $empWhere
                    ORDER BY p.fecha DESC, p.hora DESC, p.id DESC
                ");
                $stmt->execute(array_merge([$desde,$hasta], $empParams));
                $viewData['reportData'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } elseif ($tipo === 'inventario') {
                $viewData['reportData'] = $this->pdo->query("SELECT i.*, c.nombre AS categoria, cm.nombre AS compania FROM inventario i LEFT JOIN categoria c ON c.id=i.id_categoria LEFT JOIN compania_marca cm ON cm.id=i.id_compania ORDER BY i.cantidad ASC")->fetchAll(PDO::FETCH_ASSOC);
            } elseif ($tipo === 'ganancias') {
                $stmt = $this->pdo->prepare("
                    SELECT p.id AS pedido_id, p.codigo_pedido, p.fecha, p.hora, p.total_ganancia,
                           tp.nombre AS tipo_pedido,
                           cr.cantidad, cr.precio_unitario, cr.subtotal,
                           i.nombre AS producto_nombre, i.precio_proveedor,
                           (cr.precio_unitario - IFNULL(i.precio_proveedor,0)) * cr.cantidad AS ganancia_item,
                           IF(p.id_tipo_pedido=1, 'Web / En linea', IFNULL(u.nombre, 'Sin asignar')) AS empleado_nombre
                    FROM pedidos p
                    JOIN carrito cr ON cr.id_pedido = p.id
                    LEFT JOIN inventario i ON i.id = cr.id_producto
                    LEFT JOIN tipo_pedido tp ON tp.id = p.id_tipo_pedido
                    LEFT JOIN empleados emp ON (emp.id = p.id_cliente_empleado AND p.id_tipo_pedido = 2)
                    LEFT JOIN usuario u ON u.id = emp.id_usuario
                    WHERE p.status='entregado' AND p.fecha BETWEEN ? AND ? $empWhere
                    ORDER BY p.fecha DESC, p.hora DESC, p.id DESC
                ");
                $stmt->execute(array_merge([$desde,$hasta], $empParams));
                $viewData['reportData'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
                // Total ganancia
                $stmtT = $this->pdo->prepare("SELECT SUM(p.total_ganancia) AS total FROM pedidos p WHERE p.status='entregado' AND p.fecha BETWEEN ? AND ? $empWhere");
                $stmtT->execute(array_merge([$desde,$hasta], $empParams));
                $viewData['totalGanancia'] = $stmtT->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
            }
        }
    }

    private function uploadImage($field) {
        if (empty($_FILES[$field]['name']) || $_FILES[$field]['error'] !== UPLOAD_ERR_OK) return null;
        $ext = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg','jpeg','png','gif','webp'])) return null;
        $name = uniqid('img_') . '.' . $ext;
        $dest = __DIR__ . '/../uploads/' . $name;
        move_uploaded_file($_FILES[$field]['tmp_name'], $dest);
        return 'uploads/' . $name;
    }
}
