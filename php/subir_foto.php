<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    die("No autorizado");
}

$usuario_id = $_SESSION['usuario_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['foto_perfil'])) {
    die("No se recibió el archivo");
}

$archivo = $_FILES['foto_perfil'];
if ($archivo['error'] !== UPLOAD_ERR_OK) {
    die("Error en la subida: código " . $archivo['error']);
}

// Validaciones
$maxSize = 2 * 1024 * 1024;
if ($archivo['size'] > $maxSize) die("El archivo es demasiado grande (máx 2MB)");

$tiposPermitidos = ['image/jpeg', 'image/png', 'image/webp'];
if (!in_array($archivo['type'], $tiposPermitidos)) die("Formato no permitido");

$directorio = 'uploads/perfil/';
if (!is_dir($directorio)) mkdir($directorio, 0777, true);

$extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
$nombreUnico = $usuario_id . '_' . uniqid() . '.' . $extension;
$rutaDestino = $directorio . $nombreUnico;

if (move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
    // Actualizar BD
    $stmt = $conexion->prepare("UPDATE usuarios SET foto_perfil = :ruta WHERE id = :id");
    $stmt->execute([':ruta' => $rutaDestino, ':id' => $usuario_id]);

    header("Location: perfil_usuario.php?foto_ok=1");
    exit();
} else {
    die("Error al mover el archivo");
}
?>