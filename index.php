<?php
/**
 * ProyectUSB — Front Controller (Entry Point)
 * Routes all requests via ?page= parameter.
 */

// ─── Bootstrap ───────────────────────────────────────────────────────
session_start();
ob_start();

// UTF-8 everywhere
header('Content-Type: text/html; charset=UTF-8');

// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

// ─── Database connection ─────────────────────────────────────────────
require_once __DIR__ . '/config/database.php';

// ─── CSRF helpers ────────────────────────────────────────────────────
/**
 * Generate (or reuse) a CSRF token for the current session.
 */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Return a hidden <input> with the current CSRF token.
 */
function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token()) . '">';
}

/**
 * Validate the CSRF token sent in a POST request.
 * Halts execution with 403 on mismatch.
 */
function csrf_validate(): void
{
    $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!hash_equals(csrf_token(), $token)) {
        http_response_code(403);
        exit('Error 403 — Token CSRF inválido.');
    }
}

// ─── Auth helpers ────────────────────────────────────────────────────
/**
 * Check whether the current session belongs to an employee (admin).
 */
function is_employee(): bool
{
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'empleado';
}

/**
 * Check whether a user is logged in at all (employee or customer).
 */
function is_logged_in(): bool
{
    return !empty($_SESSION['user_id']);
}

/**
 * Redirect to a URL and stop.
 */
function redirect(string $url): void
{
    header("Location: $url");
    exit;
}

// ─── Routing ─────────────────────────────────────────────────────────
$page   = $_GET['page']   ?? 'catalogo';
$module = $_GET['module'] ?? null;   // admin sub-module
$vista  = $_GET['vista']  ?? 'home'; // catalog view
$action = $_GET['action'] ?? null;

// Autoload controllers on demand
function load_controller(string $name): void
{
    $file = __DIR__ . "/controllers/{$name}.php";
    if (file_exists($file)) {
        require_once $file;
    }
}

switch ($page) {
    // ── Public catalog ───────────────────────────────────────────────
    case 'catalogo':
        load_controller('CatalogoController');

        $allowed_views = ['home', 'producto', 'carrito', 'checkout', 'login', 'registro', 'mis_pedidos'];
        if (!in_array($vista, $allowed_views, true)) {
            $vista = 'home';
        }

        if (class_exists('CatalogoController')) {
            $ctrl = new CatalogoController($pdo);
            if (method_exists($ctrl, $vista)) {
                $ctrl->$vista();
            }
        }

        // The controller may set $viewData; include the view file.
        $viewFile = __DIR__ . "/views/catalogo/{$vista}.php";
        if (file_exists($viewFile)) {
            include __DIR__ . '/views/partials/header.php';
            include $viewFile;
            include __DIR__ . '/views/partials/footer.php';
        } else {
            http_response_code(404);
            include __DIR__ . '/views/partials/header.php';
            echo '<div class="container py-5 text-center"><h1>404</h1><p>Página no encontrada.</p></div>';
            include __DIR__ . '/views/partials/footer.php';
        }
        break;

    // ── Admin panel ──────────────────────────────────────────────────
    case 'admin':
        // Only employees may enter
        if (!is_employee()) {
            redirect('index.php?page=auth&action=login');
        }

        load_controller('AdminController');

        $allowed_modules = [
            'inventario', 'usuarios', 'caja', 'pedidos',
            'configuraciones', 'promociones', 'reportes',
            'proveedores', 'categorias',
        ];

        if ($module === null || !in_array($module, $allowed_modules, true)) {
            $module = 'inventario'; // default admin landing
        }

        if (class_exists('AdminController')) {
            $ctrl = new AdminController($pdo);
            if (method_exists($ctrl, $module)) {
                $ctrl->$module();
            }
        }

        $viewFile = __DIR__ . "/views/admin/{$module}.php";
        if (file_exists($viewFile)) {
            include __DIR__ . '/views/partials/header.php';
            include __DIR__ . '/views/partials/sidebar.php';
            echo '<main class="admin-main">';
            include $viewFile;
            echo '</main>';
            include __DIR__ . '/views/partials/footer.php';
        } else {
            http_response_code(404);
            echo 'Módulo no encontrado.';
        }
        break;

    // ── Authentication ───────────────────────────────────────────────
    case 'auth':
        load_controller('AuthController');

        $allowed_actions = ['login', 'logout', 'register'];
        if ($action === null || !in_array($action, $allowed_actions, true)) {
            $action = 'login';
        }

        if (class_exists('AuthController')) {
            $ctrl = new AuthController($pdo);
            if (method_exists($ctrl, $action)) {
                $ctrl->$action();  // handles its own view/redirect
                break;
            }
        }

        // Fallback: show admin login
        include __DIR__ . '/views/admin/login.php';
        break;

    // ── API (JSON endpoints) ─────────────────────────────────────────
    case 'api':
        $endpoint = $_GET['endpoint'] ?? '';
        $allowed  = ['productos', 'carrito', 'pedidos'];

        if (in_array($endpoint, $allowed, true)) {
            $file = __DIR__ . "/api/{$endpoint}.php";
            if (file_exists($file)) {
                require $file;
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Endpoint no encontrado']);
            }
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Endpoint no válido']);
        }
        break;

    // ── 404 ──────────────────────────────────────────────────────────
    default:
        http_response_code(404);
        include __DIR__ . '/views/partials/header.php';
        echo '<div class="container py-5 text-center">';
        echo '  <h1 style="font-size:4rem;color:#9E1B22;">404</h1>';
        echo '  <p class="lead">Página no encontrada.</p>';
        echo '  <a href="index.php" class="btn btn-primary">Volver al inicio</a>';
        echo '</div>';
        include __DIR__ . '/views/partials/footer.php';
        break;
}

ob_end_flush();
