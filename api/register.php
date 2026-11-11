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
$nombre = trim($_POST['nombre'] ?? '');
$apellido = trim($_POST['apellido'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');
$contrasena = $_POST['contrasena'] ?? '';

if ($usuario === '' || $nombre === '' || $apellido === '' || $contrasena === '') {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Completa usuario, nombre, apellido y contraseña.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if (strlen($contrasena) < 6) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'La contraseña debe tener al menos 6 caracteres.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$check = $conn->prepare('SELECT id_usuario FROM usuarios WHERE usuario = ? LIMIT 1');
$check->bind_param('s', $usuario);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    http_response_code(409);
    echo json_encode([
        'success' => false,
        'message' => 'Ese nombre de usuario ya existe.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$hash = password_hash($contrasena, PASSWORD_DEFAULT);
$stmt = $conn->prepare(
    'INSERT INTO usuarios (usuario, nombre, apellido, telefono, contrasena) VALUES (?, ?, ?, ?, ?)'
);
$stmt->bind_param('sssss', $usuario, $nombre, $apellido, $telefono, $hash);

if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error al registrar el usuario: ' . $stmt->error
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$_SESSION['id_usuario'] = $conn->insert_id;
$_SESSION['usuario'] = $usuario;
$_SESSION['nombre'] = $nombre . ' ' . $apellido;

echo json_encode([
    'success' => true,
    'message' => 'Registro exitoso.',
    'redirect' => 'productos.html'
], JSON_UNESCAPED_UNICODE);
