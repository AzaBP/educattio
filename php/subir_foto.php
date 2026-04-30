<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: perfil_usuario.php?foto_error=1");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['foto_perfil'])) {
    header("Location: perfil_usuario.php?foto_error=1");
    exit();
}

$archivo = $_FILES['foto_perfil'];
if ($archivo['error'] !== UPLOAD_ERR_OK) {
    header("Location: perfil_usuario.php?foto_error=1");
    exit();
}

// Validaciones
$maxSize = 2 * 1024 * 1024;
if ($archivo['size'] > $maxSize) {
    header("Location: perfil_usuario.php?foto_error=1");
    exit();
}

$tiposPermitidos = ['image/jpeg', 'image/png', 'image/webp'];
if (!in_array($archivo['type'], $tiposPermitidos)) {
    header("Location: perfil_usuario.php?foto_error=1");
    exit();
}

// Ruta corregida: apunta a la raíz del proyecto
$directorio = dirname(__DIR__) . '/uploads/perfil/';

if (!is_dir($directorio)) {
    mkdir($directorio, 0777, true);
}

if (!is_writable($directorio)) {
    header("Location: perfil_usuario.php?foto_error=1");
    exit();
}

$extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
$nombreUnico = $usuario_id . '_' . uniqid() . '.' . $extension;
$rutaDestino = $directorio . $nombreUnico;

// Guardar en BD la ruta relativa (desde la raíz del proyecto)
$rutaRelativa = 'uploads/perfil/' . $nombreUnico;

if (move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
    $stmt = $conexion->prepare("UPDATE usuarios SET foto_perfil = :ruta WHERE id = :id");
    $stmt->execute([':ruta' => $rutaRelativa, ':id' => $usuario_id]);

    header("Location: perfil_usuario.php?foto_ok=1");
    exit();
} else {
    header("Location: perfil_usuario.php?foto_error=1");
    exit();
}
} else {
    die("Error al mover el archivo a: " . $rutaDestino);
}
?>