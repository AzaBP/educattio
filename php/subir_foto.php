<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Verificar login
if (!isset($_SESSION['usuario_id'])) {
    die("❌ No has iniciado sesión.");
}
$usuario_id = $_SESSION['usuario_id'];

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    die("❌ No es método POST.");
}
if (!isset($_FILES["foto_perfil"]) || $_FILES["foto_perfil"]["error"] != UPLOAD_ERR_OK) {
    die("❌ No se recibió el archivo o hubo error. Error: " . ($_FILES["foto_perfil"]["error"] ?? 'No file'));
}

$archivo = $_FILES["foto_perfil"];
// Validaciones
if ($archivo["size"] > 2 * 1024 * 1024) die("❌ Archivo demasiado grande (>2MB)");
$tipo = $archivo["type"];
if (!in_array($tipo, ['image/jpeg','image/png','image/webp'])) die("❌ Tipo no permitido: $tipo");
if (!getimagesize($archivo["tmp_name"])) die("❌ No es una imagen válida.");

// Directorio de destino
$directorio_destino = dirname(__DIR__) . "/uploads/perfil/";
if (!file_exists($directorio_destino)) {
    mkdir($directorio_destino, 0777, true);
}
if (!is_writable($directorio_destino)) {
    die("❌ El directorio no tiene permisos de escritura: " . $directorio_destino);
}

$extension = strtolower(pathinfo($archivo["name"], PATHINFO_EXTENSION));
$nombre_unico = $usuario_id . '_' . uniqid() . '.' . $extension;
$ruta_absoluta = $directorio_destino . $nombre_unico;
$ruta_relativa = "uploads/perfil/" . $nombre_unico;

if (move_uploaded_file($archivo["tmp_name"], $ruta_absoluta)) {
    // Actualizar BD
    require_once 'conexion.php';
    $stmt = $conexion->prepare("UPDATE usuarios SET foto_perfil = :ruta WHERE id = :id");
    $stmt->execute([':ruta' => $ruta_relativa, ':id' => $usuario_id]);
    echo "✅ Foto subida correctamente. Ruta guardada: $ruta_relativa<br>";
    echo "<a href='perfil_usuario.php'>Volver al perfil</a>";
} else {
    die("❌ Error al mover el archivo. Posible problema de permisos en la carpeta temporal o destino.");
}
?>