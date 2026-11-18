<?php
class AuthController {
    private $pdo;
    public function __construct($pdo) { $this->pdo = $pdo; }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $type = $_POST['user_type'] ?? 'empleado';
            $pass = $_POST['password'] ?? '';

            if ($type === 'cliente') {
                $email = trim($_POST['email'] ?? '');
                $stmt = $this->pdo->prepare("SELECT c.id, c.nombre, pc.password_hash FROM clientes c JOIN pass_clientes pc ON pc.id_cliente=c.id WHERE c.email=? AND c.status='activo'");
                $stmt->execute([$email]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($user && password_verify($pass, $user['password_hash'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['nombre'];
                    $_SESSION['user_type'] = 'cliente';
                    $redirect = $_SESSION['redirect_after_login'] ?? 'index.php?page=catalogo';
                    unset($_SESSION['redirect_after_login']);
                    header("Location: $redirect"); exit;
                }
                $_SESSION['flash_error'] = 'Credenciales incorrectas';
            } else {
                $username = trim($_POST['username'] ?? '');
                $stmt = $this->pdo->prepare("SELECT u.id, u.nombre, u.username, pu.password_hash, e.id AS emp_id, r.nombre AS rol
                    FROM usuario u
                    JOIN pass_usuarios pu ON pu.id_usuario=u.id
                    JOIN empleados e ON e.id_usuario=u.id
                    JOIN roles r ON r.id=e.id_rol
                    WHERE u.username=? AND e.status='activo'");
                $stmt->execute([$username]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($user && password_verify($pass, $user['password_hash'])) {
                    $_SESSION['user_id'] = $user['emp_id'];
                    $_SESSION['user_name'] = $user['nombre'];
                    $_SESSION['user_type'] = 'empleado';
                    $_SESSION['user_role'] = $user['rol'];
                    header('Location: index.php?page=admin&module=caja'); exit;
                }
                $_SESSION['flash_error'] = 'Credenciales incorrectas';
            }
        }
        include __DIR__ . '/../views/admin/login.php';
    }

    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nombre = trim($_POST['nombre'] ?? '');
            $email  = trim($_POST['email'] ?? '');
            $tel    = trim($_POST['telefono'] ?? '');
            $dir    = trim($_POST['direccion'] ?? '');
            $pass   = $_POST['password'] ?? '';

            if (!$nombre || !$email || !$pass) {
                $_SESSION['flash_error'] = 'Complete los campos obligatorios';
                header('Location: index.php?page=catalogo&vista=login'); exit;
            }
            try {
                $this->pdo->beginTransaction();
                $stmt = $this->pdo->prepare("INSERT INTO clientes (nombre,telefono,email,direccion) VALUES (?,?,?,?)");
                $stmt->execute([$nombre,$tel,$email,$dir]);
                $clienteId = $this->pdo->lastInsertId();
                $hash = password_hash($pass, PASSWORD_DEFAULT);
                $this->pdo->prepare("INSERT INTO pass_clientes (id_cliente,password_hash) VALUES (?,?)")->execute([$clienteId,$hash]);
                $this->pdo->commit();
                $_SESSION['user_id'] = $clienteId;
                $_SESSION['user_name'] = $nombre;
                $_SESSION['user_type'] = 'cliente';
                $redirect = $_SESSION['redirect_after_login'] ?? 'index.php?page=catalogo';
                unset($_SESSION['redirect_after_login']);
                header("Location: $redirect"); exit;
            } catch(PDOException $e) {
                $this->pdo->rollBack();
                $_SESSION['flash_error'] = 'Error al registrar. El email ya existe?';
                header('Location: index.php?page=catalogo&vista=login'); exit;
            }
        }
        header('Location: index.php?page=catalogo&vista=login'); exit;
    }

    public function logout() {
        session_destroy();
        header('Location: index.php?page=auth&action=login'); exit;
    }
}
