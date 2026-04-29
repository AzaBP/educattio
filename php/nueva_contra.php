<?php
session_start();
require_once 'conexion.php';   // misma carpeta

$token = isset($_GET['token']) ? $_GET['token'] : '';

if(empty($token)) {
    $_SESSION['error'] = "Enlace inválido.";
    header("Location: ../login.php");
    exit();
}

date_default_timezone_set('America/Mexico_City');
$now = date('Y-m-d H:i:s');

$stmt = $conexion->prepare("SELECT email FROM password_resets WHERE token = :token AND expires_at > :now");
$stmt->execute([':token' => $token, ':now' => $now]);
$user_data = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$user_data) {
    $_SESSION['error'] = "El enlace ha expirado o no es válido.";
    header("Location: ../login.php");
    exit();
}

$email = $user_data['email'];
// No cerrar conexión aún; se usará en el formulario (aunque el formulario no necesita BD, solo el token)
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nueva contraseña - Educattio</title>
</head>
<body>
    <h2>Crear nueva contraseña</h2>
    <?php if(isset($_SESSION['error'])): ?>
        <p style="color: red;"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></p>
    <?php endif; ?>
    <form action="guardar_nueva_contra.php" method="POST">
        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
        <label for="password">Nueva contraseña:</label>
        <input type="password" name="password" id="password" required>
        <label for="confirm_password">Confirmar contraseña:</label>
        <input type="password" name="confirm_password" id="confirm_password" required>
        <button type="submit" name="reset_password">Guardar contraseña</button>
    </form>
</body>
</html>