<?php
require __DIR__ . '/db.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Método no permitido.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$usuario = trim($_POST['usuario'] ?? '');
$contrasena = $_POST['contrasena'] ?? '';

if ($usuario === '' || $contrasena === '') {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Usuario y contraseña son obligatorios.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$stmt = $conn->prepare(
    'SELECT id_usuario, usuario, nombre, apellido, contrasena FROM usuarios WHERE usuario = ? OR telefono = ? OR nombre = ? LIMIT 1'
);
$stmt->bind_param('sss', $usuario, $usuario, $usuario);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$passwordMatches = $user && (
    password_verify($contrasena, $user['contrasena']) ||
    $user['contrasena'] === $contrasena
);

if (!$user || !$passwordMatches) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Credenciales incorrectas.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$_SESSION['id_usuario'] = (int) $user['id_usuario'];
$_SESSION['usuario'] = $user['usuario'];
$_SESSION['nombre'] = trim($user['nombre'] . ' ' . $user['apellido']);

echo json_encode([
    'success' => true,
    'message' => 'Inicio de sesión correcto.',
    'redirect' => 'productos.html'
], JSON_UNESCAPED_UNICODE);
