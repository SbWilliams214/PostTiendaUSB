<?php
/**
 * CatalogoController — public storefront logic
 */
class CatalogoController {
    private $pdo;
    public function __construct($pdo) { $this->pdo = $pdo; }

    public function home() {
        global $viewData;
        $cat = (int)($_GET['cat'] ?? 0);
        $buscar = trim($_GET['buscar'] ?? '');
        $where = "WHERE i.status='activo'";
        $params = [];
        if ($cat > 0) { $where .= " AND i.id_categoria=?"; $params[] = $cat; }
        if ($buscar) { $where .= " AND (i.nombre LIKE ? OR cm.nombre LIKE ?)"; $like = "%{$buscar}%"; $params[] = $like; $params[] = $like; }

        $stmt = $this->pdo->prepare("SELECT i.*, cm.nombre AS compania FROM inventario i LEFT JOIN compania_marca cm ON cm.id=i.id_compania $where ORDER BY i.nombre");
        $stmt->execute($params);

        $viewData = [
            'productos' => $stmt->fetchAll(PDO::FETCH_ASSOC),
            'categorias' => $this->pdo->query("SELECT * FROM categoria ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC),
            'promociones' => $this->pdo->query("SELECT * FROM promociones WHERE status='activo' AND status_plataforma='activo'")->fetchAll(PDO::FETCH_ASSOC),
            'cat_actual' => $cat,
            'buscar' => $buscar,
        ];
    }

    public function producto() {
        global $viewData;
        $id = (int)($_GET['id'] ?? 0);
        $stmt = $this->pdo->prepare("SELECT i.*, cm.nombre AS compania, c.nombre AS categoria FROM inventario i LEFT JOIN compania_marca cm ON cm.id=i.id_compania LEFT JOIN categoria c ON c.id=i.id_categoria WHERE i.id=? AND i.status='activo'");
        $stmt->execute([$id]);
        $viewData = ['producto' => $stmt->fetch(PDO::FETCH_ASSOC)];
    }

    public function carrito() {
        // Cart data loaded via JS/AJAX
    }

    public function checkout() {
        global $viewData;
        // Must be logged in as client
        if (empty($_SESSION['user_id']) || ($_SESSION['user_type'] ?? '') !== 'cliente') {
            $_SESSION['redirect_after_login'] = 'index.php?page=catalogo&vista=checkout';
            header('Location: index.php?page=catalogo&vista=login'); exit;
        }
        $stmt = $this->pdo->prepare("SELECT valor FROM configuraciones WHERE clave='qr_general'");
        $stmt->execute();
        $viewData = ['qr_path' => $stmt->fetchColumn()];
    }

    public function login() {
        // Handled by view
    }

    public function registro() {
        header('Location: index.php?page=catalogo&vista=login'); exit;
    }

    public function mis_pedidos() {
        global $viewData;
        if (empty($_SESSION['user_id'])) { header('Location: index.php?page=catalogo&vista=login'); exit; }
        $stmt = $this->pdo->prepare("SELECT * FROM pedidos WHERE id_cliente_empleado=? AND id_tipo_pedido=1 ORDER BY fecha DESC, hora DESC");
        $stmt->execute([$_SESSION['user_id']]);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmtD = $this->pdo->prepare("SELECT cr.*, i.nombre AS producto_nombre FROM carrito cr LEFT JOIN inventario i ON i.id=cr.id_producto WHERE cr.id_pedido=?");
        foreach ($orders as &$o) { $stmtD->execute([$o['id']]); $o['items'] = $stmtD->fetchAll(PDO::FETCH_ASSOC); }
        $viewData = ['pedidos' => $orders];
    }
}
