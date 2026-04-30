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

// ✅ Ruta corregida: apunta a la raíz del proyecto
$directorio = dirname(__DIR__) . '/uploads/perfil/';

if (!is_dir($directorio)) {
    mkdir($directorio, 0777, true);
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
    die("Error al mover el archivo");
}
?>