<?php
session_start();
require_once 'conexion.php';

if(isset($_POST['reset_password'])) {
    $token = $_POST['token'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if($password !== $confirm_password) {
        $_SESSION['error'] = "Las contraseñas no coinciden.";
        header("Location: nueva_contra.php?token=" . urlencode($token));
        exit();
    }

    if(strlen($password) < 8) {
        $_SESSION['error'] = "La contraseña debe tener al menos 8 caracteres.";
        header("Location: nueva_contra.php?token=" . urlencode($token));
        exit();
    }

    date_default_timezone_set('America/Mexico_City');
    $now = date('Y-m-d H:i:s');

    try {
        $conexion->beginTransaction();

        // Obtener email asociado al token válido
        $stmt = $conexion->prepare("SELECT email FROM password_resets WHERE token = :token AND expires_at > :now");
        $stmt->execute([':token' => $token, ':now' => $now]);
        $user_data = $stmt->fetch(PDO::FETCH_ASSOC);

        if(!$user_data) {
            throw new Exception("Enlace inválido o expirado.");
        }

        $email = $user_data['email'];

        // Actualizar contraseña (hasheada)
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt_update = $conexion->prepare("UPDATE usuarios SET password = :pass WHERE email = :email");
        $stmt_update->execute([':pass' => $hashed_password, ':email' => $email]);

        // Eliminar token usado
        $stmt_delete = $conexion->prepare("DELETE FROM password_resets WHERE token = :token");
        $stmt_delete->execute([':token' => $token]);

        $conexion->commit();
        $_SESSION['mensaje'] = "Contraseña actualizada. Ya puedes iniciar sesión.";
        header("Location: login.php");
        exit();

    } catch (Exception $e) {
        $conexion->rollBack();
        $_SESSION['error'] = $e->getMessage();
        header("Location: nueva_contra.php?token=" . urlencode($token));
        exit();
    }
} else {
    header("Location: login.php");
    exit();
}
?>