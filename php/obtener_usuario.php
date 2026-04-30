<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$sql = "SELECT nombre_usuario, foto_perfil FROM usuarios WHERE id = :id";
$stmt = $conexion->prepare($sql);
$stmt->execute([':id' => $usuario_id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

// Si no tiene foto personalizada, usamos una por defecto.
// Asegúrate de tener una imagen por defecto en tu proyecto.
if (empty($usuario['foto_perfil'])) {
    $usuario['foto_perfil'] = "/uploads/perfil/default-avatar.png";
} else {
    $usuario['foto_perfil'] = '/' . ltrim($usuario['foto_perfil'], '/');
}
?>

<!-- En el HTML de tu portal, muestra la imagen así: -->
<img src="<?php echo htmlspecialchars($usuario['foto_perfil']); ?>" alt="Foto de perfil" class="foto-perfil">
<span><?php echo htmlspecialchars($usuario['nombre_usuario']); ?></span>